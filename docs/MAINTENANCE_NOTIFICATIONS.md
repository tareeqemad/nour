# نظام إشعارات المولدات التي تحتاج صيانة

## 📋 كيف يعمل النظام حالياً

### 1. تحديد المولدات التي تحتاج صيانة

يتم تحديد المولدات التي تحتاج صيانة في `DashboardController::index()`:

```php
// app/Http/Controllers/Admin/DashboardController.php (السطر 126-137)

$generatorsNeedingMaintenance = Generator::with('generationUnit.operator')
    ->when($operatorIds, function($q) use ($operatorIds) {
        $generationUnitIds = GenerationUnit::whereIn('operator_id', $operatorIds)->pluck('id');
        $q->whereIn('generation_unit_id', $generationUnitIds);
    })
    ->where(function ($query) {
        $query->whereNull('last_major_maintenance_date')
            ->orWhere('last_major_maintenance_date', '<', Carbon::now()->subMonths(6));
    })
    ->limit(5)
    ->get();
```

### 2. المعايير المستخدمة

المولد يحتاج صيانة إذا:
- ✅ `last_major_maintenance_date` هو `null` (لم يتم صيانة أبداً)
- ✅ أو `last_major_maintenance_date` أقدم من **6 أشهر**

### 3. إنشاء الإشعارات

يتم إنشاء الإشعارات في `createNotifications()`:

```php
// app/Http/Controllers/Admin/DashboardController.php (السطر 836-860)

private function createNotifications($user, ?array $operatorIds, ?array $generatorIds, $generatorsNeedingMaintenance, $expiringCompliance): void
{
    if ($generatorsNeedingMaintenance->count() > 0) {
        $count = $generatorsNeedingMaintenance->count();
        $firstGeneratorId = $generatorsNeedingMaintenance->first()->id;
        $this->createOrUpdateNotification(
            $user->id,
            'maintenance_needed',
            'مولدات تحتاج صيانة',
            "يوجد {$count} مولد يحتاج إلى صيانة فورية",
            route('admin.maintenance-records.create', ['generator_id' => $firstGeneratorId])
        );
    }
}
```

---

## ⚠️ المشاكل الحالية

### 1. الإشعارات غير تلقائية
- الإشعارات يتم إنشاؤها **فقط عند زيارة Dashboard**
- لا يوجد نظام تلقائي للتحقق يومياً

### 2. الإشعارات للمستخدم الحالي فقط
- الإشعارات تُرسل فقط للمستخدم الذي يزور Dashboard
- لا يتم إرسالها تلقائياً للمشغلين المعنيين

### 3. لا يوجد تذكير مسبق
- النظام يكتشف المولدات التي تجاوزت 6 أشهر
- لا يوجد تذكير قبل انتهاء المدة (مثلاً قبل شهر)

---

## ✅ الحلول المقترحة

### الحل 1: Scheduled Job (الأفضل)

إنشاء Job يتم تشغيله يومياً للتحقق من المولدات:

```php
// app/Console/Commands/CheckMaintenanceNeeded.php

namespace App\Console\Commands;

use App\Models\Generator;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckMaintenanceNeeded extends Command
{
    protected $signature = 'maintenance:check';
    protected $description = 'Check generators that need maintenance and send notifications';

    public function handle()
    {
        // المولدات التي تحتاج صيانة
        $generatorsNeedingMaintenance = Generator::with('generationUnit.operator')
            ->where(function ($query) {
                $query->whereNull('last_major_maintenance_date')
                    ->orWhere('last_major_maintenance_date', '<', Carbon::now()->subMonths(6));
            })
            ->get();

        foreach ($generatorsNeedingMaintenance as $generator) {
            if ($generator->generationUnit && $generator->generationUnit->operator) {
                $operator = $generator->generationUnit->operator;
                
                // إشعار للمشغل (CompanyOwner)
                Notification::notifyOperatorUsers(
                    $operator,
                    'maintenance_needed',
                    'مولد يحتاج صيانة',
                    "المولد {$generator->name} يحتاج إلى صيانة فورية",
                    route('admin.maintenance-records.create', ['generator_id' => $generator->id])
                );
            }
        }

        // إشعار لسلطة الطاقة والسوبر ادمن والادمن
        $managers = User::whereIn('role', [
            \App\Enums\Role::SuperAdmin->value,
            \App\Enums\Role::Admin->value,
            \App\Enums\Role::EnergyAuthority->value,
        ])->get();

        $count = $generatorsNeedingMaintenance->count();
        if ($count > 0) {
            foreach ($managers as $manager) {
                Notification::createNotification(
                    $manager->id,
                    'maintenance_needed_summary',
                    'مولدات تحتاج صيانة',
                    "يوجد {$count} مولد يحتاج إلى صيانة فورية",
                    route('admin.maintenance-records.index')
                );
            }
        }

        $this->info("Checked {$count} generators needing maintenance");
    }
}
```

**إضافة إلى Kernel:**
```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->command('maintenance:check')->daily();
}
```

### الحل 2: Event Listener عند تحديث last_major_maintenance_date

```php
// app/Models/Generator.php

protected static function booted()
{
    static::updated(function ($generator) {
        if ($generator->isDirty('last_major_maintenance_date')) {
            // إزالة الإشعارات القديمة
            Notification::where('type', 'maintenance_needed')
                ->where('link', 'like', '%generator_id=' . $generator->id)
                ->delete();
        }
    });
}
```

### الحل 3: تذكير مسبق (قبل شهر من انتهاء المدة)

```php
// في CheckMaintenanceNeeded Command

// تذكير قبل شهر
$upcomingMaintenance = Generator::with('generationUnit.operator')
    ->whereNotNull('last_major_maintenance_date')
    ->where('last_major_maintenance_date', '<', Carbon::now()->subMonths(5))
    ->where('last_major_maintenance_date', '>=', Carbon::now()->subMonths(6))
    ->get();

foreach ($upcomingMaintenance as $generator) {
    // إرسال تذكير
}
```

---

## 📊 تحسينات إضافية

### 1. إضافة معايير أخرى

```php
// يمكن إضافة معايير أخرى مثل:
// - عدد ساعات التشغيل (operating_hours)
// - حالة المولد الفنية (technical_condition_id)
// - سجلات الصيانة السابقة

$generatorsNeedingMaintenance = Generator::where(function ($query) {
    // معيار 1: تاريخ الصيانة
    $query->where(function ($q) {
        $q->whereNull('last_major_maintenance_date')
          ->orWhere('last_major_maintenance_date', '<', Carbon::now()->subMonths(6));
    })
    // معيار 2: ساعات التشغيل (مثلاً أكثر من 2000 ساعة)
    ->orWhere(function ($q) {
        $q->whereNotNull('operating_hours')
          ->where('operating_hours', '>', 2000);
    })
    // معيار 3: الحالة الفنية
    ->orWhereHas('technicalConditionDetail', function ($q) {
        $q->where('code', 'NEEDS_MAINTENANCE');
    });
});
```

### 2. إشعارات متدرجة

```php
// إشعار تحذيري قبل شهر
if ($daysSinceMaintenance >= 150 && $daysSinceMaintenance < 180) {
    $type = 'maintenance_warning';
    $title = 'تذكير: مولد يحتاج صيانة قريباً';
}

// إشعار عاجل بعد 6 أشهر
if ($daysSinceMaintenance >= 180) {
    $type = 'maintenance_urgent';
    $title = 'عاجل: مولد يحتاج صيانة فورية';
}
```

### 3. إحصائيات في Dashboard

```php
// إضافة إحصائيات أكثر تفصيلاً
$maintenanceStats = [
    'overdue' => Generator::where('last_major_maintenance_date', '<', Carbon::now()->subMonths(6))->count(),
    'upcoming' => Generator::where('last_major_maintenance_date', '<', Carbon::now()->subMonths(5))
        ->where('last_major_maintenance_date', '>=', Carbon::now()->subMonths(6))
        ->count(),
    'never_maintained' => Generator::whereNull('last_major_maintenance_date')->count(),
];
```

---

## 🔧 التطبيق

### الخطوة 1: إنشاء Command
```bash
php artisan make:command CheckMaintenanceNeeded
```

### الخطوة 2: إضافة إلى Schedule
```php
// app/Console/Kernel.php
$schedule->command('maintenance:check')->daily();
```

### الخطوة 3: اختبار
```bash
php artisan maintenance:check
```

---

## 📝 ملاحظات

1. **تحديث last_major_maintenance_date:**
   - يتم تحديثه تلقائياً عند إضافة سجل صيانة من نوع "دوري" (PERIODIC)
   - راجع: `MaintenanceRecordController::store()` (السطر 217-226)

2. **عرض الإشعارات:**
   - يتم عرضها في Dashboard
   - راجع: `resources/views/admin/dashboard/partials/alerts.blade.php`

3. **الصلاحيات:**
   - المشغل (CompanyOwner) يرى فقط مولداته
   - Admin/SuperAdmin/EnergyAuthority يرون جميع المولدات

---

**آخر تحديث:** 2025-01-24
