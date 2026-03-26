@extends('layouts.admin')

@section('title', 'إضافة سجل تشغيل')

@php
    $breadcrumbTitle = 'إضافة سجل تشغيل';
    $breadcrumbParent = 'سجلات التشغيل';
    $breadcrumbParentUrl = route('admin.operation-logs.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="إضافة سجل تشغيل جديد" icon="bi-journal-plus" :backRoute="route('admin.operation-logs.index')" />

                    <div class="card-body p-4">
                        <form action="{{ route('admin.operation-logs.store') }}" method="POST" id="operationLogForm">
                            @csrf

                            {{-- Section 1: الموقع والوقت --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">الموقع والوقت</h6>
                            <div class="row g-3 mb-4">
                                <x-admin.operator-cascade
                                    :operators="$operators ?? collect()"
                                    :showGenerator="true"
                                    :showGenerationUnit="true"
                                    :generationUnits="$generationUnits ?? collect()"
                                    :generators="$generators ?? collect()"
                                    :colClass="!auth()->user()->isAffiliatedWithOperator() ? 'col-md-4' : 'col-md-6'"
                                    :routes="[
                                        'generationUnits' => url('/admin/operators') . '/__OPERATOR__/generation-units-for-logs',
                                        'generators' => url('/admin/generation-units') . '/__UNIT__/generators-for-logs',
                                    ]"
                                />

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">تاريخ التشغيل <span class="text-danger">*</span></label>
                                    <input type="date" name="operation_date"
                                           class="form-control @error('operation_date') is-invalid @enderror"
                                           value="{{ old('operation_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                                    @error('operation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">وقت البدء <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time"
                                           class="form-control @error('start_time') is-invalid @enderror"
                                           value="{{ old('start_time') }}">
                                    @error('start_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">وقت الإيقاف <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time"
                                           class="form-control @error('end_time') is-invalid @enderror"
                                           value="{{ old('end_time') }}">
                                    @error('end_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Section 2: القراءات --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">القراءات</h6>
                            <div class="row g-3 mb-4">
                                {{-- عداد الوقود --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">عداد الوقود — البدء</label>
                                    <input type="number" step="0.01" name="fuel_meter_start"
                                           class="form-control @error('fuel_meter_start') is-invalid @enderror"
                                           value="{{ old('fuel_meter_start') }}" min="0" placeholder="0.00">
                                    @error('fuel_meter_start')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">عداد الوقود — الانتهاء</label>
                                    <input type="number" step="0.01" name="fuel_meter_end"
                                           class="form-control @error('fuel_meter_end') is-invalid @enderror"
                                           value="{{ old('fuel_meter_end') }}" min="0" placeholder="0.00">
                                    @error('fuel_meter_end')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">الوقود المستهلك (لتر)</label>
                                    <input type="number" step="0.01" name="fuel_consumed"
                                           class="form-control bg-light @error('fuel_consumed') is-invalid @enderror"
                                           value="{{ old('fuel_consumed') }}" id="fuel_consumed"
                                           readonly tabindex="-1" placeholder="محسوب تلقائياً">
                                    @error('fuel_consumed')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                {{-- عداد الطاقة --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">عداد الطاقة — البدء</label>
                                    <input type="number" step="0.01" name="energy_meter_start"
                                           class="form-control @error('energy_meter_start') is-invalid @enderror"
                                           value="{{ old('energy_meter_start') }}" min="0" placeholder="0.00">
                                    @error('energy_meter_start')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">عداد الطاقة — الإيقاف</label>
                                    <input type="number" step="0.01" name="energy_meter_end"
                                           class="form-control @error('energy_meter_end') is-invalid @enderror"
                                           value="{{ old('energy_meter_end') }}" min="0" placeholder="0.00">
                                    @error('energy_meter_end')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">الطاقة المنتجة (kWh)</label>
                                    <input type="number" step="0.01" name="energy_produced"
                                           class="form-control bg-light @error('energy_produced') is-invalid @enderror"
                                           value="{{ old('energy_produced') }}" id="energy_produced"
                                           readonly tabindex="-1" placeholder="محسوب تلقائياً">
                                    @error('energy_produced')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                {{-- نسبة التحميل + التعرفة --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">نسبة التحميل (%)</label>
                                    <input type="number" step="0.01" name="load_percentage"
                                           class="form-control @error('load_percentage') is-invalid @enderror"
                                           value="{{ old('load_percentage') }}" min="0" max="100" placeholder="0.00">
                                    @error('load_percentage')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">سعر التعرفة (₪/kWh)</label>
                                    <input type="number" step="0.01" name="electricity_tariff_price"
                                           class="form-control bg-light @error('electricity_tariff_price') is-invalid @enderror"
                                           value="{{ old('electricity_tariff_price') }}" min="0" max="500"
                                           placeholder="يتم تعبئته تلقائياً" id="electricity_tariff_price">
                                    @error('electricity_tariff_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Section 3: ملاحظات --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">ملاحظات</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">ملاحظات تشغيلية</label>
                                    <textarea name="operational_notes"
                                              class="form-control @error('operational_notes') is-invalid @enderror"
                                              rows="3" placeholder="ملاحظات متعلقة بتشغيل المولد...">{{ old('operational_notes') }}</textarea>
                                    @error('operational_notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">أعطال مسجلة</label>
                                    <textarea name="malfunctions"
                                              class="form-control @error('malfunctions') is-invalid @enderror"
                                              rows="3" placeholder="تفاصيل أي أعطال تمت ملاحظتها...">{{ old('malfunctions') }}</textarea>
                                    @error('malfunctions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('admin.operation-logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitOperationLogBtn">
                                    <i class="bi bi-check-lg me-2"></i>حفظ البيانات
                                </button>
                            </div>
                        </form>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function($) {
    $(document).ready(function() {
        const $form = $('#operationLogForm');
        const $submitBtn = $('#submitOperationLogBtn');

        // Auto-calculate fuel consumed
        const fuelStart = document.querySelector('input[name="fuel_meter_start"]');
        const fuelEnd = document.querySelector('input[name="fuel_meter_end"]');
        const fuelConsumed = document.getElementById('fuel_consumed');

        function calculateFuel() {
            if (!fuelStart || !fuelEnd || !fuelConsumed) return;
            const start = parseFloat(fuelStart.value) || 0;
            const end = parseFloat(fuelEnd.value) || 0;
            if (start > 0 && end > 0 && end >= start) {
                fuelConsumed.value = (end - start).toFixed(2);
                fuelStart.classList.remove('is-invalid');
                fuelEnd.classList.remove('is-invalid');
            } else if (start > 0 && end > 0 && end < start) {
                fuelConsumed.value = '';
                fuelEnd.classList.add('is-invalid');
            } else {
                fuelConsumed.value = '';
                fuelStart.classList.remove('is-invalid');
                fuelEnd.classList.remove('is-invalid');
            }
        }
        if (fuelStart) fuelStart.addEventListener('input', calculateFuel);
        if (fuelEnd) fuelEnd.addEventListener('input', calculateFuel);

        // Auto-calculate energy produced
        const energyStart = document.querySelector('input[name="energy_meter_start"]');
        const energyEnd = document.querySelector('input[name="energy_meter_end"]');
        const energyProduced = document.getElementById('energy_produced');

        function calculateEnergy() {
            if (!energyStart || !energyEnd || !energyProduced) return;
            const start = parseFloat(energyStart.value) || 0;
            const end = parseFloat(energyEnd.value) || 0;
            if (start > 0 && end > 0 && end >= start) {
                energyProduced.value = (end - start).toFixed(2);
                energyStart.classList.remove('is-invalid');
                energyEnd.classList.remove('is-invalid');
            } else if (start > 0 && end > 0 && end < start) {
                energyProduced.value = '';
                energyEnd.classList.add('is-invalid');
            } else {
                energyProduced.value = '';
                energyStart.classList.remove('is-invalid');
                energyEnd.classList.remove('is-invalid');
            }
        }
        if (energyStart) energyStart.addEventListener('input', calculateEnergy);
        if (energyEnd) energyEnd.addEventListener('input', calculateEnergy);

        // Auto-load tariff price on date change
        const operationDateInput = document.querySelector('input[name="operation_date"]');
        if (operationDateInput) {
            operationDateInput.addEventListener('change', function() {
                const instance = CascadingSelects.getInstance();
                if (instance) CascadingSelects.loadTariffPrice(instance);
            });
        }

        // Form submission with validation
        $form.on('submit', function(e) {
            e.preventDefault();
            let hasErrors = false;
            let errorMessages = [];

            // Validate cascade selects
            const $operator = $form.find('[name="operator_id"]');
            const $generationUnit = $form.find('[name="generation_unit_id"]');
            const $generator = $form.find('[name="generator_id"]');

            [[$operator, 'المشغل'], [$generationUnit, 'وحدة التوليد'], [$generator, 'المولد']].forEach(function(item) {
                if (item[0].length && (!item[0].val() || item[0].val() == '0')) {
                    item[0].addClass('is-invalid');
                    errorMessages.push(item[1] + ' مطلوب');
                    hasErrors = true;
                } else {
                    item[0].removeClass('is-invalid');
                }
            });

            if (hasErrors) {
                if (typeof window.showToast === 'function') window.showToast(errorMessages.join('، '), 'error');
                $form.find('.is-invalid').first().each(function() {
                    $('html, body').animate({ scrollTop: $(this).offset().top - 100 }, 300);
                });
                return false;
            }

            if (!$form[0].checkValidity()) { $form[0].reportValidity(); return false; }

            // Validate meter readings
            const fs = parseFloat($('input[name="fuel_meter_start"]').val()) || 0;
            const fe = parseFloat($('input[name="fuel_meter_end"]').val()) || 0;
            if (fs > 0 && fe > 0 && fe < fs) {
                if (typeof window.showToast === 'function') window.showToast('قراءة عداد الوقود عند الانتهاء أقل من البدء', 'error');
                return false;
            }
            const es = parseFloat($('input[name="energy_meter_start"]').val()) || 0;
            const ee = parseFloat($('input[name="energy_meter_end"]').val()) || 0;
            if (es > 0 && ee > 0 && ee < es) {
                if (typeof window.showToast === 'function') window.showToast('قراءة عداد الطاقة عند الإيقاف أقل من البدء', 'error');
                return false;
            }

            // Disable button + submit via AJAX
            $submitBtn.prop('disabled', true);
            const originalText = $submitBtn.html();
            $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

            const formData = new FormData(this);
            formData.delete('fuel_consumed');
            formData.delete('energy_produced');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                success: function(response) {
                    if (response && response.success) {
                        if (typeof window.showToast === 'function') window.showToast(response.message || 'تم الحفظ بنجاح', 'success');
                        setTimeout(function() { window.location.href = '{{ route('admin.operation-logs.index') }}'; }, 500);
                    } else {
                        $submitBtn.prop('disabled', false).html(originalText);
                        if (typeof window.showToast === 'function') window.showToast(response.message || 'حدث خطأ', 'error');
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).removeAttr('disabled').removeClass('disabled').html(originalText);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        $form.find('.is-invalid').removeClass('is-invalid');
                        $form.find('.invalid-feedback:not([class*="d-block"])').remove();
                        $.each(errors, function(field, messages) {
                            const $field = $form.find('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                const $parent = $field.closest('[class*="col-"]');
                                $parent.find('.invalid-feedback').remove();
                                $parent.append('<div class="invalid-feedback d-block">' + (Array.isArray(messages) ? messages[0] : messages) + '</div>');
                            }
                        });
                        $form.find('.is-invalid').first().each(function() {
                            $('html, body').animate({ scrollTop: $(this).offset().top - 100 }, 300);
                        });
                        if (typeof window.showToast === 'function') window.showToast('يرجى التحقق من الحقول', 'error');
                    } else {
                        if (typeof window.showToast === 'function') window.showToast(xhr.responseJSON?.message || 'حدث خطأ', 'error');
                    }
                }
            });
        });
    });
})(jQuery);
</script>
@endpush
