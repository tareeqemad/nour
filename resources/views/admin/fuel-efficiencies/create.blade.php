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
    <link rel="stylesheet" href="{{ asset('assets/admin/css/cascading-selects.css') }}">
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
                                    {{-- Cascading Selects: المشغل → وحدة التوليد → المولد --}}
                                    @php
                                        $canSelect = !auth()->user()->isAffiliatedWithOperator();
                                    @endphp
                                    @include('admin.partials.cascading-selects', [
                                        'operators' => $operators ?? collect(),
                                        'showGenerator' => true,
                                        'showGenerationUnit' => true,
                                        'generationUnits' => $generationUnits ?? collect(),
                                        'generators' => $generators ?? collect(),
                                        'colClass' => $canSelect ? 'col-md-4' : 'col-md-6',
                                    ])

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
<script src="{{ asset('assets/admin/js/cascading-selects.js') }}"></script>
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#fuelEfficiencyForm');
            const $submitBtn = $form.find('button[type="submit"]');

            // تهيئة Cascading Selects باستخدام المكتبة الموحدة
            CascadingSelects.init({
                canSelectOperator: {{ !auth()->user()->isAffiliatedWithOperator() ? 'true' : 'false' }},
                operatorUrl: '/admin/operators/{id}/generation-units-for-efficiencies',
                generationUnitUrl: '/admin/generation-units/{id}/generators-for-efficiencies',
                useSelect2: true,
                select2Options: {
                    dir: 'rtl',
                    language: 'ar',
                    allowClear: true,
                    width: '100%'
                }
            });

            @if(old('operator_id') && !auth()->user()->isAffiliatedWithOperator())
                $('#operator_id').trigger('change');
            @endif

            @if(old('generation_unit_id') && auth()->user()->isAffiliatedWithOperator())
                $('#generation_unit_id').trigger('change');
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
