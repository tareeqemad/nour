@extends('layouts.admin')

@section('title', 'تعديل سجل صيانة')

@php
    $breadcrumbTitle = 'تعديل سجل صيانة';
    $breadcrumbParent = 'سجلات الصيانة';
    $breadcrumbParentUrl = route('admin.maintenance-records.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/maintenance-records.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-pencil-square me-2"></i>
                                تعديل سجل الصيانة
                            </h5>
                            <div class="general-subtitle">
                                @if($maintenanceRecord->generator)
                                    المولد: {{ $maintenanceRecord->generator->name }} | 
                                @endif
                                التاريخ: {{ $maintenanceRecord->maintenance_date->format('Y-m-d') }}
                            </div>
                        </div>
                        <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.maintenance-records.update', $maintenanceRecord) }}" method="POST" id="maintenanceRecordForm">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                    المعلومات الأساسية
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                                            المولد <span class="text-danger">*</span>
                                        </label>
                                        <select name="generator_id" id="generator_id" 
                                                class="form-select @error('generator_id') is-invalid @enderror">
                                            <option value="">اختر المولد</option>
                                            @foreach($generators as $generator)
                                                <option value="{{ $generator->id }}" 
                                                        {{ old('generator_id', $maintenanceRecord->generator_id) == $generator->id ? 'selected' : '' }}>
                                                    {{ $generator->generator_number }} - {{ $generator->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('generator_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-tag text-info me-1"></i>
                                            نوع الصيانة <span class="text-danger">*</span>
                                        </label>
                                        <select name="maintenance_type_id" id="maintenance_type_id" 
                                                class="form-select @error('maintenance_type_id') is-invalid @enderror">
                                            <option value="">اختر النوع</option>
                                            @foreach($constants['maintenance_type'] ?? [] as $type)
                                                <option value="{{ $type->id }}" {{ old('maintenance_type_id', $maintenanceRecord->maintenance_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('maintenance_type_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar3 text-primary me-1"></i>
                                            تاريخ الصيانة <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="maintenance_date" 
                                               class="form-control @error('maintenance_date') is-invalid @enderror" 
                                               value="{{ old('maintenance_date', $maintenanceRecord->maintenance_date->format('Y-m-d')) }}">
                                        @error('maintenance_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person-badge text-success me-1"></i>
                                            اسم الفني المسؤول
                                        </label>
                                        <input type="text" name="technician_name" 
                                               class="form-control @error('technician_name') is-invalid @enderror" 
                                               value="{{ old('technician_name', $maintenanceRecord->technician_name) }}" 
                                               placeholder="اسم الفني">
                                        @error('technician_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clock text-info me-1"></i>
                                            وقت البدء
                                        </label>
                                        <input type="time" name="start_time" id="start_time"
                                               class="form-control @error('start_time') is-invalid @enderror" 
                                               value="{{ old('start_time', $maintenanceRecord->start_time ? \Carbon\Carbon::parse($maintenanceRecord->start_time)->format('H:i') : '') }}">
                                        @error('start_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clock-history text-warning me-1"></i>
                                            وقت الانتهاء
                                        </label>
                                        <input type="time" name="end_time" id="end_time"
                                               class="form-control @error('end_time') is-invalid @enderror" 
                                               value="{{ old('end_time', $maintenanceRecord->end_time ? \Carbon\Carbon::parse($maintenanceRecord->end_time)->format('H:i') : '') }}">
                                        @error('end_time')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Work Details Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-clipboard-check text-warning me-2"></i>
                                    تفاصيل الأعمال
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-journal-text text-info me-1"></i>
                                            الأعمال المنفذة
                                        </label>
                                        <textarea name="work_performed" 
                                                  class="form-control @error('work_performed') is-invalid @enderror" 
                                                  rows="4" 
                                                  placeholder="أدخل تفاصيل الأعمال المنفذة...">{{ old('work_performed', $maintenanceRecord->work_performed) }}</textarea>
                                        @error('work_performed')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Time & Cost Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-clock-history text-danger me-2"></i>
                                    الوقت والتكلفة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-hourglass-split text-primary me-1"></i>
                                            زمن التوقف (ساعات)
                                        </label>
                                        <input type="number" step="0.01" name="downtime_hours" id="downtime_hours"
                                               class="form-control calculated-field @error('downtime_hours') is-invalid @enderror" 
                                               value="{{ old('downtime_hours', $maintenanceRecord->downtime_hours) }}" 
                                               min="0" 
                                               placeholder="0.00"
                                               readonly
                                               tabindex="-1">
                                        <small class="text-muted">يتم الحساب تلقائياً من وقت البدء والانتهاء</small>
                                        @error('downtime_hours')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Cost Details Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3 text-muted">
                                    <i class="bi bi-cash-stack text-success me-2"></i>
                                    تفاصيل التكلفة
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-box-seam text-info me-1"></i>
                                            تكلفة القطع (₪)
                                        </label>
                                        <input type="number" step="0.01" name="parts_cost" id="parts_cost"
                                               class="form-control @error('parts_cost') is-invalid @enderror" 
                                               value="{{ old('parts_cost', $maintenanceRecord->parts_cost) }}" 
                                               min="0" 
                                               placeholder="0.00">
                                        <small class="text-muted">تكلفة القطع المستخدمة</small>
                                        @error('parts_cost')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clock text-warning me-1"></i>
                                            ساعات العمل
                                        </label>
                                        <input type="number" step="0.01" name="labor_hours" id="labor_hours"
                                               class="form-control @error('labor_hours') is-invalid @enderror" 
                                               value="{{ old('labor_hours', $maintenanceRecord->labor_hours) }}" 
                                               min="0" 
                                               placeholder="0.00">
                                        <small class="text-muted">عدد ساعات العمل</small>
                                        @error('labor_hours')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-currency-exchange text-primary me-1"></i>
                                            أجر الساعة (₪)
                                        </label>
                                        <input type="number" step="0.01" name="labor_rate_per_hour" id="labor_rate_per_hour"
                                               class="form-control @error('labor_rate_per_hour') is-invalid @enderror" 
                                               value="{{ old('labor_rate_per_hour', $maintenanceRecord->labor_rate_per_hour ?? 100) }}" 
                                               min="0" 
                                               placeholder="100.00">
                                        <small class="text-muted">أجر الفني لكل ساعة</small>
                                        @error('labor_rate_per_hour')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calculator text-success me-1"></i>
                                            التكلفة الإجمالية (₪)
                                        </label>
                                        <input type="number" step="0.01" name="maintenance_cost" id="maintenance_cost"
                                               class="form-control calculated-field @error('maintenance_cost') is-invalid @enderror" 
                                               value="{{ old('maintenance_cost', $maintenanceRecord->maintenance_cost) }}" 
                                               min="0" 
                                               placeholder="0.00"
                                               readonly
                                               tabindex="-1">
                                        <small class="text-muted">يتم الحساب تلقائياً: تكلفة القطع + (ساعات العمل × أجر الساعة)</small>
                                        @error('maintenance_cost')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end align-items-center gap-2">
                                <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    حفظ التعديلات
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
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#maintenanceRecordForm');
            const $submitBtn = $form.find('button[type="submit"]');
            
            // Calculate downtime hours from start and end time
            function calculateDowntimeHours() {
                const startTime = $('#start_time').val();
                const endTime = $('#end_time').val();
                
                if (startTime && endTime) {
                    const start = new Date('2000-01-01T' + startTime + ':00');
                    const end = new Date('2000-01-01T' + endTime + ':00');
                    
                    if (end < start) {
                        // If end time is before start time, assume it's the next day
                        end.setDate(end.getDate() + 1);
                    }
                    
                    const diffMs = end - start;
                    const diffHours = diffMs / (1000 * 60 * 60);
                    
                    if (diffHours >= 0) {
                        $('#downtime_hours').val(diffHours.toFixed(2));
                    } else {
                        $('#downtime_hours').val('');
                    }
                } else {
                    $('#downtime_hours').val('');
                }
            }
            
            // Calculate maintenance cost
            function calculateMaintenanceCost() {
                const partsCost = parseFloat($('#parts_cost').val()) || 0;
                const laborHours = parseFloat($('#labor_hours').val()) || 0;
                const laborRate = parseFloat($('#labor_rate_per_hour').val()) || 0;
                const totalCost = partsCost + (laborHours * laborRate);
                $('#maintenance_cost').val(totalCost.toFixed(2));
            }
            
            $('#start_time, #end_time').on('change', calculateDowntimeHours);
            $('#parts_cost, #labor_hours, #labor_rate_per_hour').on('input', calculateMaintenanceCost);
            
            // Calculate initial values on page load
            calculateDowntimeHours();
            calculateMaintenanceCost();

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
                formData.delete('downtime_hours');
                formData.delete('maintenance_cost');

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
                                window.showToast(response.message || 'تم تحديث سجل الصيانة بنجاح', 'success');
                            } else {
                                alert(response.message || 'تم تحديث سجل الصيانة بنجاح');
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.maintenance-records.index') }}';
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
