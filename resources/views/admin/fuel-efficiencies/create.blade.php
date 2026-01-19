@extends('layouts.admin')

@section('title', 'إضافة سجل كفاءة وقود')

@php
    $breadcrumbTitle = 'إضافة سجل كفاءة وقود';
    $breadcrumbParent = 'سجلات كفاءة الوقود';
    $breadcrumbParentUrl = route('admin.fuel-efficiencies.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/fuel-efficiencies.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-speedometer2 me-2"></i>
                                إضافة سجل كفاءة وقود جديد
                            </h5>
                            <div class="general-subtitle">
                                قم بإدخال بيانات كفاءة الوقود بشكل كامل
                            </div>
                        </div>
                        <a href="{{ route('admin.fuel-efficiencies.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.fuel-efficiencies.store') }}" method="POST" id="fuelEfficiencyForm">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    المعلومات الأساسية
                                </h6>
                                <div class="row g-3">
                                    @if(auth()->user()->isSuperAdmin())
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                المشغل <span class="text-danger">*</span>
                                            </label>
                                            <select name="operator_id" id="operator_id" 
                                                    class="form-select select2 @error('operator_id') is-invalid @enderror">
                                                <option value="0">-- اختر المشغل --</option>
                                                @foreach($operators as $operator)
                                                    <option value="{{ $operator->id }}" 
                                                            {{ old('operator_id') == $operator->id ? 'selected' : '' }}>
                                                        {{ $operator->name }}
                                                        @if($operator->unit_number)
                                                            - {{ $operator->unit_number }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('operator_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                وحدة التوليد <span class="text-danger">*</span>
                                            </label>
                                            <select name="generation_unit_id" id="generation_unit_id" 
                                                    class="form-select select2 @error('generation_unit_id') is-invalid @enderror" 
                                                    required>
                                                <option value="0">-- اختر وحدة التوليد --</option>
                                            </select>
                                            @error('generation_unit_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                المولد <span class="text-danger">*</span>
                                            </label>
                                            <select name="generator_id" id="generator_id" 
                                                    class="form-select select2 @error('generator_id') is-invalid @enderror">
                                                <option value="0">-- اختر المولد --</option>
                                            </select>
                                            @error('generator_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                المشغل <span class="text-danger">*</span>
                                            </label>
                                            <select name="operator_id" id="operator_id" 
                                                    class="form-select select2 @error('operator_id') is-invalid @enderror" 
                                                    disabled>
                                                <option value="{{ $operators->first()->id }}" selected>
                                                    {{ $operators->first()->name }}
                                                    @if($operators->first()->unit_number)
                                                        - {{ $operators->first()->unit_number }}
                                                    @endif
                                                </option>
                                            </select>
                                            <input type="hidden" name="operator_id" value="{{ $operators->first()->id }}">
                                            @error('operator_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                وحدة التوليد <span class="text-danger">*</span>
                                            </label>
                                            <select name="generation_unit_id" id="generation_unit_id" 
                                                    class="form-select select2 @error('generation_unit_id') is-invalid @enderror" 
                                                    required>
                                                <option value="0">-- اختر وحدة التوليد --</option>
                                                @foreach($generationUnits as $unit)
                                                    <option value="{{ $unit->id }}" 
                                                            {{ old('generation_unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }} ({{ $unit->unit_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('generation_unit_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                المولد <span class="text-danger">*</span>
                                            </label>
                                            <select name="generator_id" id="generator_id" 
                                                    class="form-select select2 @error('generator_id') is-invalid @enderror">
                                                <option value="0">-- اختر المولد --</option>
                                                @foreach($generators as $generator)
                                                    <option value="{{ $generator->id }}" 
                                                            data-generation-unit-id="{{ $generator->generation_unit_id }}"
                                                            {{ old('generator_id') == $generator->id ? 'selected' : '' }}>
                                                        {{ $generator->generator_number }} - {{ $generator->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('generator_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar3 text-primary me-1"></i>
                                            تاريخ الاستهلاك <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="consumption_date" 
                                               class="form-control @error('consumption_date') is-invalid @enderror" 
                                               value="{{ old('consumption_date', date('Y-m-d')) }}">
                                        @error('consumption_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Operating Hours Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-clock text-info me-2"></i>
                                    ساعات التشغيل
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clock-history text-primary me-1"></i>
                                            ساعات التشغيل
                                        </label>
                                        <input type="number" step="0.01" name="operating_hours" 
                                               class="form-control @error('operating_hours') is-invalid @enderror" 
                                               value="{{ old('operating_hours') }}" 
                                               min="0" 
                                               placeholder="0.00">
                                        <small class="text-muted">عدد ساعات التشغيل</small>
                                        @error('operating_hours')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-currency-exchange text-success me-1"></i>
                                            سعر الوقود للتر
                                        </label>
                                        <input type="number" step="0.01" name="fuel_price_per_liter" id="fuel_price_per_liter"
                                               class="form-control @error('fuel_price_per_liter') is-invalid @enderror" 
                                               value="{{ old('fuel_price_per_liter') }}" 
                                               min="0" 
                                               placeholder="0.00">
                                        <small class="text-muted">السعر لكل لتر من الوقود</small>
                                        @error('fuel_price_per_liter')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Fuel Consumption Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-fuel-pump text-danger me-2"></i>
                                    استهلاك الوقود
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-droplet text-primary me-1"></i>
                                            كمية الوقود المستهلكة (لتر)
                                        </label>
                                        <input type="number" step="0.01" name="fuel_consumed" id="fuel_consumed"
                                               class="form-control @error('fuel_consumed') is-invalid @enderror" 
                                               value="{{ old('fuel_consumed') }}" 
                                               min="0" 
                                               placeholder="0.00">
                                        <small class="text-muted">كمية الوقود المستهلكة باللتر</small>
                                        @error('fuel_consumed')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Fuel Efficiency Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-fuel-pump text-danger me-2"></i>
                                    كفاءة استهلاك الوقود
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-percent text-primary me-1"></i>
                                            كفاءة استهلاك الوقود (%)
                                        </label>
                                        <input type="number" step="0.01" name="fuel_efficiency_percentage" 
                                               class="form-control @error('fuel_efficiency_percentage') is-invalid @enderror" 
                                               value="{{ old('fuel_efficiency_percentage') }}" 
                                               min="0" max="100" 
                                               placeholder="0.00">
                                        <small class="text-muted">النسبة المئوية لكفاءة الوقود</small>
                                        @error('fuel_efficiency_percentage')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-bar-chart text-info me-1"></i>
                                            مقارنة كفاءة الوقود مع المعيار
                                        </label>
                                        <select name="fuel_efficiency_comparison_id" 
                                                class="form-select @error('fuel_efficiency_comparison_id') is-invalid @enderror">
                                            <option value="">اختر المقارنة</option>
                                            @foreach($constants['fuel_efficiency_comparison'] ?? [] as $comparison)
                                                <option value="{{ $comparison->id }}" {{ old('fuel_efficiency_comparison_id') == $comparison->id ? 'selected' : '' }}>
                                                    {{ $comparison->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('fuel_efficiency_comparison_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Energy Efficiency Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-lightning-charge text-warning me-2"></i>
                                    كفاءة توزيع الطاقة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-percent text-primary me-1"></i>
                                            كفاءة توزيع الطاقة (%)
                                        </label>
                                        <input type="number" step="0.01" name="energy_distribution_efficiency" id="energy_distribution_efficiency"
                                               class="form-control @error('energy_distribution_efficiency') is-invalid @enderror" 
                                               value="{{ old('energy_distribution_efficiency') }}" 
                                               min="0" max="100" 
                                               placeholder="0.00">
                                        <small class="text-muted">النسبة المئوية لكفاءة توزيع الطاقة</small>
                                        @error('energy_distribution_efficiency')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-bar-chart text-info me-1"></i>
                                            مقارنة كفاءة الطاقة مع المعيار
                                        </label>
                                        <select name="energy_efficiency_comparison_id" id="energy_efficiency_comparison_id"
                                                class="form-select @error('energy_efficiency_comparison_id') is-invalid @enderror">
                                            <option value="">اختر المقارنة</option>
                                            @foreach($constants['energy_efficiency_comparison'] ?? [] as $comparison)
                                                <option value="{{ $comparison->id }}" {{ old('energy_efficiency_comparison_id') == $comparison->id ? 'selected' : '' }}>
                                                    {{ $comparison->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('energy_efficiency_comparison_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Cost Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-cash-stack text-success me-2"></i>
                                    التكلفة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calculator text-info me-1"></i>
                                            التكلفة الإجمالية للتشغيل
                                        </label>
                                        <input type="number" step="0.01" name="total_operating_cost" id="total_operating_cost"
                                               class="form-control calculated-field @error('total_operating_cost') is-invalid @enderror" 
                                               value="{{ old('total_operating_cost') }}" 
                                               min="0" 
                                               placeholder="0.00"
                                               readonly
                                               tabindex="-1">
                                        <small class="text-muted">يتم الحساب تلقائياً: كمية الوقود × سعر اللتر</small>
                                        @error('total_operating_cost')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('admin.fuel-efficiencies.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ البيانات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            // Initialize Select2 for all selects
            $('.select2').select2({
                dir: 'rtl',
                language: 'ar',
                allowClear: true,
                width: '100%'
            });

            const $form = $('#fuelEfficiencyForm');
            const $submitBtn = $form.find('button[type="submit"]');
            
            const $operatorSelect = $('#operator_id');
            const $generationUnitSelect = $('#generation_unit_id');
            const $generatorSelect = $('#generator_id');
            
            @if(auth()->user()->isSuperAdmin())
                // للسوبر أدمن: المشغل → وحدة التوليد → المولد
                // عند اختيار المشغل
                $operatorSelect.on('change', async function() {
                    const operatorId = $(this).val();
                    
                    // إعادة تهيئة Select2 لوحدة التوليد
                    $generationUnitSelect.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                    
                    // إعادة تهيئة Select2 للمولد
                    $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                    
                    if (!operatorId || operatorId == '0') {
                        $generationUnitSelect.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').prop('disabled', true);
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/operators/${operatorId}/generation-units-for-efficiencies`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            $generationUnitSelect.empty().append('<option value="0">-- اختر وحدة التوليد --</option>');
                            
                            if (data.generation_units && data.generation_units.length > 0) {
                                data.generation_units.forEach(unit => {
                                    $generationUnitSelect.append(new Option(unit.label, unit.id, false, false));
                                });
                                $generationUnitSelect.prop('disabled', false).trigger('change');
                            } else {
                                $generationUnitSelect.append('<option value="">لا توجد وحدات توليد</option>');
                            }
                        }
                    } catch (error) {
                        console.error('Error loading generation units:', error);
                        $generationUnitSelect.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                });
                
                // عند اختيار وحدة التوليد
                $generationUnitSelect.on('change', async function() {
                    const generationUnitId = $(this).val();
                    
                    // إعادة تهيئة Select2 للمولد
                    $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                    
                    if (!generationUnitId || generationUnitId == '0') {
                        $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>').prop('disabled', true);
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/generation-units/${generationUnitId}/generators-for-efficiencies`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            $generatorSelect.empty().append('<option value="0">-- اختر المولد --</option>');
                            
                            if (data.generators && data.generators.length > 0) {
                                data.generators.forEach(generator => {
                                    $generatorSelect.append(new Option(generator.label, generator.id, false, false));
                                });
                                $generatorSelect.prop('disabled', false).trigger('change');
                            } else {
                                $generatorSelect.append('<option value="">لا توجد مولدات</option>');
                            }
                        }
                    } catch (error) {
                        console.error('Error loading generators:', error);
                        $generatorSelect.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                });
                
                @if(old('operator_id'))
                    $operatorSelect.trigger('change');
                @endif
            @else
                // للمشغل/الموظف: المشغل محدد → وحدات التوليد تظهر تلقائياً → يختار المولد
                $generationUnitSelect.on('change', function() {
                    const generationUnitId = $(this).val();
                    const currentValue = $generatorSelect.val();
                    
                    // تصفية المولدات حسب وحدة التوليد
                    $generatorSelect.find('option').each(function() {
                        const $option = $(this);
                        if (!$option.val() || $option.val() == '0') return; // تجاهل option الفارغ
                        
                        const optionGenerationUnitId = $option.data('generation-unit-id');
                        if (generationUnitId && generationUnitId != '0' && optionGenerationUnitId == generationUnitId) {
                            $option.prop('disabled', false).show();
                        } else if (!generationUnitId || generationUnitId == '0') {
                            $option.prop('disabled', false).show();
                        } else {
                            $option.prop('disabled', true).hide();
                        }
                    });
                    
                    // إعادة تهيئة Select2
                    $generatorSelect.select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    });
                    
                    // إذا كانت القيمة الحالية غير متاحة، امسح الاختيار
                    if (currentValue && currentValue != '0') {
                        const $selectedOption = $generatorSelect.find(`option[value="${currentValue}"]`);
                        if ($selectedOption.length && $selectedOption.prop('disabled')) {
                            $generatorSelect.val('0').trigger('change');
                        }
                    }
                });
                
                @if(old('generation_unit_id'))
                    $generationUnitSelect.trigger('change');
                @endif
            @endif
            
            // Calculate total operating cost
            function calculateTotalCost() {
                const fuelConsumed = parseFloat($('#fuel_consumed').val()) || 0;
                const fuelPrice = parseFloat($('#fuel_price_per_liter').val()) || 0;
                const totalCost = fuelConsumed * fuelPrice;
                $('#total_operating_cost').val(totalCost.toFixed(2));
            }
            
            $('#fuel_consumed, #fuel_price_per_liter').on('input', calculateTotalCost);
            
            // Calculate initial values on page load
            calculateTotalCost();

            $form.on('submit', function(e) {
                e.preventDefault();
                
                if (!$form[0].checkValidity()) {
                    $form[0].reportValidity();
                    return false;
                }

                $submitBtn.prop('disabled', true);
                const originalText = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

                const formData = new FormData(this);
                
                // Remove calculated fields from form data (server will calculate them)
                formData.delete('total_operating_cost');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof window.showToast === 'function') {
                                window.showToast(response.message || 'تم إنشاء سجل كفاءة الوقود بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم إنشاء سجل كفاءة الوقود بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.fuel-efficiencies.index') }}';
                            }, 500);
                        }
                    },
                    error: function(xhr) {
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html(originalText);

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            let firstError = '';
                            
                            $form.find('.is-invalid').removeClass('is-invalid');
                            $form.find('.invalid-feedback').remove();

                            $.each(errors, function(field, messages) {
                                const $field = $form.find('[name="' + field + '"]');
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                                    if (!firstError) firstError = errorMsg;
                                    $field.after('<div class="invalid-feedback d-block">' + errorMsg + '</div>');
                                }
                            });

                            if (typeof window.showToast === 'function') {
                                window.showToast(firstError || 'يرجى التحقق من الحقول المطلوبة', 'error');
                            } else {
                                alert(firstError || 'يرجى التحقق من الحقول المطلوبة');
                            }
                        } else {
                            const errorMsg = xhr.responseJSON?.message || 'حدث خطأ أثناء حفظ البيانات';
                            if (typeof window.showToast === 'function') {
                                window.showToast(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    }
                });
            });
        });
    })(jQuery);
</script>
@endpush
