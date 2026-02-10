@extends('layouts.admin')

@section('title', 'إضافة سجل صيانة')

@php
    $breadcrumbTitle = 'إضافة سجل صيانة';
    $breadcrumbParent = 'سجلات الصيانة';
    $breadcrumbParentUrl = route('admin.maintenance-records.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/maintenance-records.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <div class="maintenance-records-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h1 class="maintenance-record-create__title">
                                <i class="bi bi-tools me-2"></i>
                                إضافة سجل صيانة جديد
                            </h1>
                            <div class="maintenance-record-create__meta text-muted">
                                أدخل بيانات سجل الصيانة
                            </div>
                        </div>
                        <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('admin.maintenance-records.store') }}" method="POST" id="maintenanceRecordForm">
                            @csrf

                            <div class="row g-3 mb-4">
                                @include('admin.partials.cascading-selects', [
                                    'operators' => $operators ?? collect(),
                                    'showGenerator' => true,
                                    'showGenerationUnit' => true,
                                    'generationUnits' => $generationUnits ?? collect(),
                                    'generators' => $generators ?? collect(),
                                    'colClass' => auth()->user()->isAffiliatedWithOperator() ? 'col-md-6' : 'col-md-4',
                                    'operatorRequired' => false,
                                    'generationUnitRequired' => false,
                                    'generatorRequired' => true,
                                    'selectedGeneratorId' => old('generator_id', $selectedGeneratorId ?? null),
                                ])
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نوع الصيانة <span class="text-danger">*</span></label>
                                    <select name="maintenance_type_id" id="maintenance_type_id" class="form-select @error('maintenance_type_id') is-invalid @enderror">
                                        <option value="">اختر النوع</option>
                                        @foreach($constants['maintenance_type'] ?? [] as $type)
                                            <option value="{{ $type->id }}" {{ old('maintenance_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('maintenance_type_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ الصيانة <span class="text-danger">*</span></label>
                                    <input type="date" name="maintenance_date" class="form-control @error('maintenance_date') is-invalid @enderror"
                                           value="{{ old('maintenance_date', date('Y-m-d')) }}">
                                    @error('maintenance_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">اسم الفني المسؤول</label>
                                    <input type="text" name="technician_name" class="form-control @error('technician_name') is-invalid @enderror"
                                           value="{{ old('technician_name') }}" placeholder="اسم الفني">
                                    @error('technician_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">وقت البدء</label>
                                    <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror"
                                           value="{{ old('start_time') }}">
                                    @error('start_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">وقت الانتهاء</label>
                                    <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror"
                                           value="{{ old('end_time') }}">
                                    @error('end_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">الأعمال المنفذة</label>
                                <textarea name="work_performed" class="form-control @error('work_performed') is-invalid @enderror" rows="4"
                                          placeholder="تفاصيل الأعمال المنفذة">{{ old('work_performed') }}</textarea>
                                @error('work_performed')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">زمن التوقف (ساعات)</label>
                                    <input type="number" step="0.01" name="downtime_hours" id="downtime_hours"
                                           class="form-control calculated-field @error('downtime_hours') is-invalid @enderror"
                                           value="{{ old('downtime_hours') }}" min="0" readonly tabindex="-1">
                                    <small class="text-muted">يُحسب من وقت البدء والانتهاء</small>
                                    @error('downtime_hours')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">تكلفة القطع (₪)</label>
                                    <input type="number" step="0.01" name="parts_cost" id="parts_cost" class="form-control @error('parts_cost') is-invalid @enderror"
                                           value="{{ old('parts_cost') }}" min="0" placeholder="0">
                                    @error('parts_cost')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">ساعات العمل</label>
                                    <input type="number" step="0.01" name="labor_hours" id="labor_hours" class="form-control @error('labor_hours') is-invalid @enderror"
                                           value="{{ old('labor_hours') }}" min="0" placeholder="0">
                                    @error('labor_hours')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">أجر الساعة (₪)</label>
                                    <input type="number" step="0.01" name="labor_rate_per_hour" id="labor_rate_per_hour" class="form-control @error('labor_rate_per_hour') is-invalid @enderror"
                                           value="{{ old('labor_rate_per_hour', 100) }}" min="0" placeholder="100">
                                    @error('labor_rate_per_hour')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-semibold">التكلفة الإجمالية (₪)</label>
                                    <input type="number" step="0.01" name="maintenance_cost" id="maintenance_cost"
                                           class="form-control calculated-field @error('maintenance_cost') is-invalid @enderror"
                                           value="{{ old('maintenance_cost') }}" min="0" readonly tabindex="-1">
                                    <small class="text-muted">تُحسب تلقائياً</small>
                                    @error('maintenance_cost')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-secondary">
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>
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

@push('styles')
<style>
    .maintenance-record-create__title { font-size: 1.25rem; font-weight: 700; margin: 0; }
    .maintenance-record-create__meta { font-size: 0.9rem; margin-top: 0.25rem; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
@include('admin.partials.cascading-selects-scripts', [
    'cascadingRoutes' => [
        'generationUnits' => url('/admin/operators') . '/__OPERATOR__/generation-units-for-maintenance-records',
        'generators' => url('/admin/generation-units') . '/__UNIT__/generators-for-maintenance-records',
    ],
    'initialGeneratorId' => old('generator_id', $selectedGeneratorId ?? null),
])
<script>
(function($) {
    $(document).ready(function() {
        const $form = $('#maintenanceRecordForm');
        const $submitBtn = $form.find('button[type="submit"]');

        function calculateDowntimeHours() {
            const startTime = $('#start_time').val();
            const endTime = $('#end_time').val();
            if (startTime && endTime) {
                const start = new Date('2000-01-01T' + startTime + ':00');
                const end = new Date('2000-01-01T' + endTime + ':00');
                if (end <= start) end.setDate(end.getDate() + 1);
                const diffHours = (end - start) / (1000 * 60 * 60);
                $('#downtime_hours').val(diffHours >= 0 ? diffHours.toFixed(2) : '');
            } else {
                $('#downtime_hours').val('');
            }
        }

        function calculateMaintenanceCost() {
            const parts = parseFloat($('#parts_cost').val()) || 0;
            const hours = parseFloat($('#labor_hours').val()) || 0;
            const rate = parseFloat($('#labor_rate_per_hour').val()) || 0;
            $('#maintenance_cost').val((parts + hours * rate).toFixed(2));
        }

        $('#start_time, #end_time').on('change', calculateDowntimeHours);
        $('#parts_cost, #labor_hours, #labor_rate_per_hour').on('input', calculateMaintenanceCost);
        calculateDowntimeHours();
        calculateMaintenanceCost();

        $form.on('submit', function(e) {
            e.preventDefault();
            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return false;
            }
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');
            const formData = new FormData(this);
            formData.delete('downtime_hours');
            formData.delete('maintenance_cost');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                success: function(response) {
                    if (response.success) {
                        (typeof window.showToast === 'function')
                            ? window.showToast(response.message || 'تم إنشاء سجل الصيانة بنجاح', 'success')
                            : alert(response.message || 'تم إنشاء سجل الصيانة بنجاح');
                        var redirectUrl = response.redirect_url || '{{ route('admin.maintenance-records.index') }}';
                        setTimeout(function() { window.location.href = redirectUrl; }, 500);
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>حفظ البيانات');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        $form.find('.is-invalid').removeClass('is-invalid').next('.invalid-feedback').remove();
                        let firstError = '';
                        $.each(errors, function(field, messages) {
                            const $field = $form.find('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                const msg = Array.isArray(messages) ? messages[0] : messages;
                                if (!firstError) firstError = msg;
                                $field.after('<div class="invalid-feedback d-block">' + msg + '</div>');
                            }
                        });
                        (typeof window.showToast === 'function') ? window.showToast(firstError || 'يرجى تصحيح الحقول', 'error') : alert(firstError);
                    } else {
                        const msg = xhr.responseJSON?.message || 'حدث خطأ أثناء الحفظ';
                        (typeof window.showToast === 'function') ? window.showToast(msg, 'error') : alert(msg);
                    }
                }
            });
        });
    });
})(jQuery);
</script>
@endpush
