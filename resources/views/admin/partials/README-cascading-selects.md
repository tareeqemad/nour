# Cascading Selects - المشغل → وحدة التوليد → المولد

## الملفات الجديدة

- `partials/cascading-selects.blade.php` - الـ HTML للـ Selects
- `partials/cascading-selects-scripts.blade.php` - الـ JavaScript
- `public/assets/admin/js/cascading-selects.js` - مكتبة JavaScript موحدة

## طريقة الاستخدام

### 1. في الـ Controller

```php
public function create()
{
    $user = auth()->user();
    
    // تحديد إذا كان المستخدم يستطيع اختيار المشغل
    $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
    
    if ($canSelectOperator) {
        $operators = Operator::orderBy('name')->get();
        $affiliatedOperator = null;
    } else {
        $operators = collect();
        // للمشغل: الحصول على المشغل الخاص به
        $affiliatedOperator = $user->isCompanyOwner() 
            ? $user->ownedOperators()->first()
            : $user->operators()->first();
    }
    
    return view('admin.your-module.create', compact(
        'operators',
        'affiliatedOperator',
        'canSelectOperator'
    ));
}
```

### 2. في الـ View (HTML)

```blade
<div class="row g-3">
    @include('admin.partials.cascading-selects', [
        'operators' => $operators,
        'affiliatedOperator' => $affiliatedOperator,
        'canSelectOperator' => $canSelectOperator,
        'showGenerator' => true,           // إظهار المولد
        'showGenerationUnit' => true,      // إظهار وحدة التوليد
        'operatorRequired' => true,        // المشغل مطلوب
        'generationUnitRequired' => true,  // وحدة التوليد مطلوبة
        'generatorRequired' => true,       // المولد مطلوب
        'colClass' => 'col-md-4',          // عرض العمود
    ])
</div>
```

### 3. في الـ View (JavaScript) - في نهاية الصفحة

```blade
@push('scripts')
    @include('admin.partials.cascading-selects-scripts', [
        'canSelectOperator' => $canSelectOperator,
        'affiliatedOperatorId' => $affiliatedOperator?->id,
        'initialOperatorId' => old('operator_id'),
        'initialGenerationUnitId' => old('generation_unit_id'),
        'initialGeneratorId' => old('generator_id'),
    ])
@endpush
```

## خيارات إضافية

### تخصيص الـ Labels

```blade
@include('admin.partials.cascading-selects', [
    // ...
    'operatorLabel' => 'الشركة',
    'generationUnitLabel' => 'المحطة',
    'generatorLabel' => 'الوحدة',
])
```

### عدة نسخ في نفس الصفحة

```blade
{{-- النسخة الأولى --}}
@include('admin.partials.cascading-selects', [
    'idPrefix' => 'source_',
    // ...
])

{{-- النسخة الثانية --}}
@include('admin.partials.cascading-selects', [
    'idPrefix' => 'target_',
    // ...
])
```

### Callbacks للتفاعل مع التغييرات

```blade
@include('admin.partials.cascading-selects-scripts', [
    // ...
    'onOperatorChange' => 'function(operatorId) { console.log("Operator changed:", operatorId); }',
    'onGenerationUnitChange' => 'function(unitId) { console.log("Unit changed:", unitId); }',
    'onGeneratorChange' => 'function(generatorId) { console.log("Generator changed:", generatorId); }',
])
```

## المنطق التلقائي

1. **SuperAdmin / Admin / EnergyAuthority**: يمكنهم اختيار أي مشغل من القائمة
2. **CompanyOwner**: المشغل محدد تلقائياً (المشغل الخاص به)
3. **Employee / Technician**: المشغل محدد تلقائياً (المشغل التابعين له)

## الـ Routes المطلوبة

تأكد من وجود هذه الـ routes:
- `GET /admin/operators/{operator}/generation-units` - لجلب وحدات التوليد
- `GET /admin/generation-units/{generationUnitId}/generators-list` - لجلب المولدات
