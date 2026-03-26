@extends('layouts.admin')

@section('title', 'تعديل سجل كفاءة وقود')

@php
    $breadcrumbTitle = 'تعديل سجل كفاءة وقود';
    $breadcrumbParent = 'سجلات كفاءة الوقود';
    $breadcrumbParentUrl = route('admin.fuel-efficiencies.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تعديل سجل كفاءة الوقود" icon="bi-pencil" :backRoute="route('admin.fuel-efficiencies.index')" />

                    <div class="card-body p-4">
                        <form action="{{ route('admin.fuel-efficiencies.update', $fuelEfficiency) }}" method="POST" id="fuelEfficiencyForm">
                            @csrf
                            @method('PUT')

                            {{-- Section 1: المعلومات الأساسية --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">المعلومات الأساسية</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">المولد <span class="text-danger">*</span></label>
                                    <select name="generator_id" class="form-select @error('generator_id') is-invalid @enderror">
                                        <option value="">اختر المولد</option>
                                        @foreach($generators as $generator)
                                            <option value="{{ $generator->id }}" {{ old('generator_id', $fuelEfficiency->generator_id) == $generator->id ? 'selected' : '' }}>
                                                {{ $generator->generator_number }} - {{ $generator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('generator_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">تاريخ الاستهلاك <span class="text-danger">*</span></label>
                                    <input type="date" name="consumption_date"
                                           class="form-control @error('consumption_date') is-invalid @enderror"
                                           value="{{ old('consumption_date', $fuelEfficiency->consumption_date->format('Y-m-d')) }}">
                                    @error('consumption_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">ساعات التشغيل</label>
                                    <input type="number" step="0.01" name="operating_hours"
                                           class="form-control @error('operating_hours') is-invalid @enderror"
                                           value="{{ old('operating_hours', $fuelEfficiency->operating_hours) }}" min="0" placeholder="0.00">
                                    @error('operating_hours')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">سعر الوقود للتر</label>
                                    <input type="number" step="0.01" name="fuel_price_per_liter" id="fuel_price_per_liter"
                                           class="form-control @error('fuel_price_per_liter') is-invalid @enderror"
                                           value="{{ old('fuel_price_per_liter', $fuelEfficiency->fuel_price_per_liter) }}" min="0" placeholder="0.00">
                                    @error('fuel_price_per_liter')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Section 2: الاستهلاك والكفاءة --}}
                            <h6 class="text-muted fw-semibold mb-3 pb-2 border-bottom border-1">الاستهلاك والكفاءة</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">كمية الوقود المستهلكة (لتر)</label>
                                    <input type="number" step="0.01" name="fuel_consumed" id="fuel_consumed"
                                           class="form-control @error('fuel_consumed') is-invalid @enderror"
                                           value="{{ old('fuel_consumed', $fuelEfficiency->fuel_consumed) }}" min="0" placeholder="0.00">
                                    @error('fuel_consumed')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">كفاءة الوقود (%)</label>
                                    <input type="number" step="0.01" name="fuel_efficiency_percentage"
                                           class="form-control @error('fuel_efficiency_percentage') is-invalid @enderror"
                                           value="{{ old('fuel_efficiency_percentage', $fuelEfficiency->fuel_efficiency_percentage) }}" min="0" max="100" placeholder="0.00">
                                    @error('fuel_efficiency_percentage')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">مقارنة كفاءة الوقود</label>
                                    <select name="fuel_efficiency_comparison_id"
                                            class="form-select select2-simple @error('fuel_efficiency_comparison_id') is-invalid @enderror"
                                            data-placeholder="اختر المقارنة">
                                        <option value="">اختر المقارنة</option>
                                        @foreach($constants['fuel_efficiency_comparison'] ?? [] as $comparison)
                                            <option value="{{ $comparison->id }}" {{ old('fuel_efficiency_comparison_id', $fuelEfficiency->fuel_efficiency_comparison_id) == $comparison->id ? 'selected' : '' }}>
                                                {{ $comparison->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('fuel_efficiency_comparison_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">كفاءة توزيع الطاقة (%)</label>
                                    <input type="number" step="0.01" name="energy_distribution_efficiency" id="energy_distribution_efficiency"
                                           class="form-control @error('energy_distribution_efficiency') is-invalid @enderror"
                                           value="{{ old('energy_distribution_efficiency', $fuelEfficiency->energy_distribution_efficiency) }}" min="0" max="100" placeholder="0.00">
                                    @error('energy_distribution_efficiency')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">مقارنة كفاءة الطاقة</label>
                                    <select name="energy_efficiency_comparison_id"
                                            class="form-select select2-simple @error('energy_efficiency_comparison_id') is-invalid @enderror"
                                            data-placeholder="اختر المقارنة">
                                        <option value="">اختر المقارنة</option>
                                        @foreach($constants['energy_efficiency_comparison'] ?? [] as $comparison)
                                            <option value="{{ $comparison->id }}" {{ old('energy_efficiency_comparison_id', $fuelEfficiency->energy_efficiency_comparison_id) == $comparison->id ? 'selected' : '' }}>
                                                {{ $comparison->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('energy_efficiency_comparison_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">التكلفة الإجمالية</label>
                                    <input type="number" step="0.01" name="total_operating_cost" id="total_operating_cost"
                                           class="form-control bg-light @error('total_operating_cost') is-invalid @enderror"
                                           value="{{ old('total_operating_cost', $fuelEfficiency->total_operating_cost) }}" min="0"
                                           placeholder="محسوب تلقائياً" readonly tabindex="-1">
                                    <small class="form-text text-muted">محسوب تلقائياً: كمية الوقود × سعر اللتر</small>
                                    @error('total_operating_cost')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                                <a href="{{ route('admin.fuel-efficiencies.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>حفظ التعديلات
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
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script>
(function($) {
    $(document).ready(function() {
        // Init Select2 for comparison dropdowns
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-simple').select2({
                dir: 'rtl',
                language: 'ar',
                allowClear: true,
                width: '100%',
                placeholder: function() { return $(this).data('placeholder') || 'اختر...'; }
            });
        }

        // Auto-calculate total cost
        function calculateTotalCost() {
            const fuelConsumed = parseFloat($('#fuel_consumed').val()) || 0;
            const fuelPrice = parseFloat($('#fuel_price_per_liter').val()) || 0;
            $('#total_operating_cost').val((fuelConsumed * fuelPrice).toFixed(2));
        }
        $('#fuel_consumed, #fuel_price_per_liter').on('input', calculateTotalCost);
        calculateTotalCost();

        // AJAX form submit
        const $form = $('#fuelEfficiencyForm');
        $form.on('submit', function(e) {
            e.preventDefault();
            if (!$form[0].checkValidity()) { $form[0].reportValidity(); return false; }

            const $btn = $form.find('button[type="submit"]');
            $btn.prop('disabled', true);
            const originalText = $btn.html();
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');

            const formData = new FormData(this);
            formData.delete('total_operating_cost');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                success: function(response) {
                    if (response.success) {
                        if (typeof window.showToast === 'function') window.showToast(response.message || 'تم الحفظ بنجاح', 'success');
                        setTimeout(function() { window.location.href = '{{ route('admin.fuel-efficiencies.index') }}'; }, 500);
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        $form.find('.is-invalid').removeClass('is-invalid');
                        $.each(errors, function(field, messages) {
                            const $field = $form.find('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                const $parent = $field.closest('[class*="col-"]');
                                $parent.find('.invalid-feedback').remove();
                                $parent.append('<div class="invalid-feedback d-block">' + (Array.isArray(messages) ? messages[0] : messages) + '</div>');
                            }
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
