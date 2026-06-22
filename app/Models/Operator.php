<?php

namespace App\Models;

use App\Governorate;
use App\Models\ConstantDetail;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class Operator extends Model
{
    use SoftDeletes, TracksUser;

    protected $table = 'operators';

    protected $fillable = [
        // البيانات الأساسية للمشغل
        'name', // اسم المشغل بالعربي
        'unit_number', // رقم المشغل للعرض (001, 002, إلخ)
        
        // العلاقة مع المستخدم (المالك)
        'owner_id', // ID المستخدم الذي يملك هذا المشغل
        
        // بيانات المالك والمشغل
        'owner_name', // اسم المالك الفعلي
        'owner_id_number', // رقم هوية المالك
        'operator_id_number', // رقم هوية المشغل
        
        // الحالة
        'status', // active/inactive
        'is_approved', // معتمد/غير معتمد
        'license_status', // حالة الترخيص (دورة حياة الطلب بعد الاعتماد)
        'profile_completed', // اكتمال الملف الشخصي
        
        // المنطقة الجغرافية
        'territory_radius_km', // نصف قطر المنطقة الجغرافية بالكيلومترات
    ];

    protected function casts(): array
    {
        return [
            'profile_completed' => 'boolean',
            'is_approved' => 'boolean',
            'territory_radius_km' => 'decimal:2',
        ];
    }

    // ===== حالات الترخيص (دورة حياة الطلب بعد الاعتماد) =====
    const LICENSE_PENDING     = 'pending';     // قيد المراجعة
    const LICENSE_PRELIMINARY = 'preliminary'; // موافقة مبدئية
    const LICENSE_NEEDS_EDIT  = 'needs_edit';  // مُرجَع للتعديل
    const LICENSE_REJECTED    = 'rejected';    // مرفوض
    const LICENSE_CANCELLED   = 'cancelled';   // ملغى
    const LICENSE_LICENSED    = 'licensed';    // حاصل على رخصة

    /**
     * كل حالات الترخيص مع تسمياتها العربية.
     *
     * @return array<string, string>
     */
    public static function licenseStatuses(): array
    {
        return [
            self::LICENSE_PENDING     => 'قيد المراجعة',
            self::LICENSE_PRELIMINARY => 'موافقة مبدئية',
            self::LICENSE_NEEDS_EDIT  => 'مُرجَع للتعديل',
            self::LICENSE_REJECTED    => 'مرفوض',
            self::LICENSE_CANCELLED   => 'ملغى',
            self::LICENSE_LICENSED    => 'حاصل على رخصة',
        ];
    }

    /** اسم الحالة بالعربي */
    public function getLicenseStatusLabelAttribute(): string
    {
        return self::licenseStatuses()[$this->license_status] ?? 'قيد المراجعة';
    }

    /** كلاس Bootstrap لشارة الحالة */
    public function getLicenseStatusBadgeAttribute(): string
    {
        return match ($this->license_status) {
            self::LICENSE_PRELIMINARY => 'info',
            self::LICENSE_NEEDS_EDIT  => 'warning text-dark',
            self::LICENSE_REJECTED    => 'danger',
            self::LICENSE_CANCELLED   => 'secondary',
            self::LICENSE_LICENSED    => 'success',
            default                   => 'light text-dark border',
        };
    }

    /**
     * إرسال رسالة SMS لمالك المشغّل عبر قالب قابل للتعديل (بمفتاح محدد).
     * يستخدمها زر التوجيه/الحالة والإرسال التلقائي. لا ترمي استثناء أبداً.
     */
    public function sendSms(string $templateKey, array $extra = []): bool
    {
        $owner = $this->owner;
        if (! $owner || ! $owner->phone) {
            return false;
        }

        $tpl = \App\Models\SmsTemplate::getByKey($templateKey);
        if (! $tpl) {
            return false; // لا قالب مفعّل
        }

        $message = $tpl->render(array_merge([
            'name'          => $owner->name,
            'operator_name' => $this->name,
            'site_name'     => \App\Models\Setting::get('site_name', 'نور'),
            'login_url'     => url('/login'),
        ], $extra));

        try {
            $result = (new \App\Services\HotSMSService())->sendSMS($owner->phone, $message, 2);
            return (bool) ($result['success'] ?? false);
        } catch (\Throwable $e) {
            \Log::error('Failed to send operator SMS', [
                'operator_id' => $this->id,
                'template'    => $templateKey,
                'error'       => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'operator_user')
            ->withTimestamps();
    }

    /**
     * Users with custom roles linked to this operator
     * Custom roles are defined dynamically by Energy Authority or Company Owner
     */
    public function staff(): BelongsToMany
    {
        return $this->users()->whereHas('roleModel', function($q) {
            $q->where('is_system', false)
              ->where('operator_id', $this->id);
        });
    }

    /**
     * وحدات التوليد التابعة لهذا المشغل
     */
    /**
     * إنشاء قواعد الحد الأدنى الافتراضية عند إنشاء مشغل جديد
     */
    protected static function booted(): void
    {
        static::created(function (self $operator) {
            MinimumChargeRule::seedForOperator($operator->id);
        });
    }

    public function generationUnits(): HasMany
    {
        return $this->hasMany(GenerationUnit::class);
    }

    /**
     * المولدات التابعة لهذا المشغل (عبر وحدات التوليد)
     */
    public function generators(): HasManyThrough
    {
        return $this->hasManyThrough(Generator::class, GenerationUnit::class);
    }

    /**
     * المناطق الجغرافية المملوكة لهذا المشغل
     */
    public function territories(): HasMany
    {
        return $this->hasMany(OperatorTerritory::class);
    }

    public function operationLogs(): HasMany
    {
        return $this->hasMany(OperationLog::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OperatorAttachment::class);
    }

    public function complianceSafeties(): HasMany
    {
        return $this->hasMany(ComplianceSafety::class);
    }

    /**
     * قواعد الحد الأدنى الخاصة بهذا المشغل
     */
    public function minimumChargeRules(): HasMany
    {
        return $this->hasMany(MinimumChargeRule::class);
    }

    // تم إزالة العلاقة لأن الأسعار الآن عامة لجميع المشغلين (لا يوجد operator_id)
    // public function electricityTariffPrices(): HasMany
    // {
    //     return $this->hasMany(ElectricityTariffPrice::class);
    // }

    /**
     * علاقة مع ثابت المدينة (city_id)
     * يستخدم: ConstantsHelper::get(20) - ثابت Master رقم 20
     */
    public function cityDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'city_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function isProfileComplete(): bool
    {
        // يجب إدخال الحقول الأربعة: name, owner_name, owner_id_number, operator_id_number
        return $this->profile_completed 
            && !empty($this->name) 
            && !empty($this->owner_name) 
            && !empty($this->owner_id_number) 
            && !empty($this->operator_id_number);
    }

    /**
     * التحقق من أن المشغل معتمد ومفعل
     */
    public function isApproved(): bool
    {
        return $this->status === 'active' && $this->is_approved === true;
    }

    public function getGovernorateDetails(): ?array
    {
        return $this->governorate?->details();
    }

    public function getGovernorateLabel(): ?string
    {
        return $this->governorate?->label();
    }

    public function getGovernorateCode(): ?string
    {
        return $this->governorate?->code();
    }

    /**
     * توليد رقم الوحدة التالي (001, 002, إلخ) حسب المحافظة والمدينة
     */
    public static function getNextUnitNumber(?Governorate $governorate, ?int $cityId = null): string
    {
        if (!$governorate || !$cityId) {
            return '001';
        }

        // البحث عن آخر رقم وحدة في نفس المحافظة والمدينة
        $lastUnit = static::where('governorate', $governorate)
            ->where('city_id', $cityId)
            ->whereNotNull('unit_number')
            ->orderByRaw('CAST(unit_number AS UNSIGNED) DESC')
            ->first();

        if ($lastUnit && $lastUnit->unit_number) {
            $lastNumber = (int) $lastUnit->unit_number;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * توليد كود الوحدة بالصيغة GU-PP-CC-NNN
     * حيث:
     * GU: ثابت (GENERATION UNIT)
     * PP: ترميز المحافظة
     * CC: ترميز المدينة
     * NNN: رقم الوحدة (001, 002, إلخ)
     */
    public static function generateUnitCode(?Governorate $governorate, ?int $cityId, ?string $unitNumber = null): ?string
    {
        if (!$governorate || !$cityId) {
            return null;
        }

        // الحصول على ترميز المحافظة
        $governorateCode = $governorate->code();
        
        // الحصول على ترميز المدينة من الثوابت
        $cityDetail = ConstantDetail::find($cityId);
        if (!$cityDetail || !$cityDetail->code) {
            return null;
        }
        
        $cityCode = $cityDetail->code;
        
        // استخدام رقم الوحدة الموجود أو توليد واحد جديد
        if (!$unitNumber) {
            $unitNumber = self::getNextUnitNumber($governorate, $cityId);
        }

        return "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";
    }

    public function getMissingFields(): array
    {
        $missing = [];

        if (empty($this->name)) $missing[] = 'اسم المشغل';
        if (empty($this->owner_name)) $missing[] = 'اسم المالك';
        if (empty($this->owner_id_number)) $missing[] = 'رقم هوية المالك';
        if (empty($this->operator_id_number)) $missing[] = 'رقم هوية المشغل';

        return $missing;
    }

    /**
     * الحصول على اسم المدينة من الثوابت
     */
    public function getCityName(): ?string
    {
        return $this->cityDetail?->label;
    }

    /**
     * Accessor للحصول على رقم الهاتف من المالك
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->owner?->phone;
    }

    /**
     * Boot the model and register event listeners
     */
    protected static function boot(): void
    {
        parent::boot();

        // When a new operator is created and is not approved, send notification to all approvers
        static::created(function (Operator $operator) {
            // إنشاء أدوار مخصصة تلقائياً للمشغل الجديد
            self::createDefaultRolesForOperator($operator);
            
            // إذا كان الملف مكتملاً عند الإنشاء، أرسل إشعار اكتمال الملف
            if ($operator->profile_completed && $operator->name) {
                \App\Models\Notification::notifyOperatorApprovers(
                    'operator_profile_completed',
                    'اكتمال ملف المشغل',
                    "تم اكتمال ملف المشغل ({$operator->name}). المالك: " . ($operator->owner?->name ?? 'غير محدد'),
                    route('admin.operators.show', $operator)
                );
            }
            
            if (!$operator->is_approved && $operator->owner) {
                // Send notification to all users who can approve operators (Super Admin, Admin, Energy Authority)
                \App\Models\Notification::notifyOperatorApprovers(
                    'operator_pending_approval',
                    'مشغل جديد يحتاج للاعتماد',
                    "تم إنشاء مشغل جديد ({$operator->name}) يحتاج للاعتماد والتفعيل. المالك: {$operator->owner->name}",
                    route('admin.operators.show', $operator)
                );

                // إرسال رسالة توجيه تلقائية للمشغّل الجديد (نفس قالب الزر) — لا ترمي استثناء
                $operator->sendSms('op_onboarding');
            }
        });

        // When profile_completed changes from false to true, send notification to approvers
        static::updating(function (Operator $operator) {
            if ($operator->isDirty('profile_completed') 
                && $operator->getOriginal('profile_completed') === false 
                && $operator->profile_completed === true) {
                
                // إرسال إشعار لسلطة الطاقة والسوبر أدمن والأدمن أن المشغل أكمل ملفه
                \App\Models\Notification::notifyOperatorApprovers(
                    'operator_profile_completed',
                    'اكتمال ملف المشغل',
                    "تم اكتمال ملف المشغل ({$operator->name}). المالك: " . ($operator->owner?->name ?? 'غير محدد'),
                    route('admin.operators.show', $operator)
                );
            }
        });
    }
    
    /**
     * إنشاء أدوار مخصصة افتراضية للمشغل الجديد
     * يتم إنشاء دورين: فني ومحاسب
     */
    private static function createDefaultRolesForOperator(Operator $operator): void
    {
        try {
            // جلب جميع الصلاحيات المتاحة
            $permissions = \App\Models\Permission::all();
            
            // 1. دور فني المشغل - صلاحيات الصيانة والتشغيل
            $technicianRole = Role::create([
                'name' => 'technician_' . $operator->id,
                'label' => 'فني مشغل',
                'description' => 'دور فني المشغل - تم إنشاؤه تلقائياً للمشغل: ' . $operator->name,
                'is_system' => false,
                'operator_id' => $operator->id,
                'created_by' => $operator->owner_id,
                'order' => 100,
            ]);
            
            // إضافة صلاحيات فني المشغل (صيانة + تشغيل + قراءات)
            $technicianPermissions = $permissions->whereIn('name', [
                'generation_units.view',
                'generators.view',
                'maintenance_records.view',
                'maintenance_records.create',
                'maintenance_records.update',
                'operation_logs.view',
                'operation_logs.create',
                'operation_logs.update',
                'fuel_efficiencies.view',
                'fuel_efficiencies.create',
                'fuel_efficiencies.update',
                'compliance_safeties.view',
                'compliance_safeties.create',
                'compliance_safeties.update',
                'meter_readings.view',
                'meter_readings.create',
                'meter_readings.update',
                'subscribers.view',
                'tasks.view',
                'tasks.create',
                'tasks.update',
            ])->pluck('id');
            $technicianRole->permissions()->attach($technicianPermissions);

            // 2. دور المحاسب - صلاحيات مالية + عرض بيانات
            $accountantRole = Role::create([
                'name' => 'accountant_' . $operator->id,
                'label' => 'محاسب',
                'description' => 'دور المحاسب - تم إنشاؤه تلقائياً للمشغل: ' . $operator->name,
                'is_system' => false,
                'operator_id' => $operator->id,
                'created_by' => $operator->owner_id,
                'order' => 101,
            ]);

            // إضافة صلاحيات المحاسب (مالية كاملة + عرض بيانات)
            $accountantPermissions = $permissions->whereIn('name', [
                'subscribers.view',
                'meter_readings.view',
                'invoices.view',
                'invoices.create',
                'invoices.update',
                'invoices.issue',
                'invoice_reports.view',
                'subscriber_accounts.view',
                'minimum_charge_rules.view', 'minimum_charge_rules.update',
                'electricity_tariff_prices.view',
                'generation_units.view',
                'generators.view',
                'operation_logs.view',
                'fuel_efficiencies.view',
                'maintenance_records.view',
                'compliance_safeties.view',
            ])->pluck('id');
            $accountantRole->permissions()->attach($accountantPermissions);
            
            \Log::info("تم إنشاء أدوار افتراضية مع صلاحيات للمشغل: {$operator->name} (ID: {$operator->id})");
        } catch (\Exception $e) {
            \Log::error("فشل إنشاء أدوار افتراضية للمشغل: {$operator->name} (ID: {$operator->id}). الخطأ: " . $e->getMessage());
        }
    }
}
