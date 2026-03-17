@extends('layouts.admin')

@section('title', 'تعديل قراءة العداد')

@php
    $breadcrumbTitle = 'تعديل قراءة العداد';
    $breadcrumbParent = 'قراءات العدادات';
    $breadcrumbParentUrl = route('admin.meter-readings.index');
    $readingStatuses = \App\Helpers\ConstantsHelper::get(28);
@endphp

@push('styles')
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
                                <i class="bi bi-pencil me-2"></i>
                                تعديل قراءة العداد
                            </h5>
                            <div class="general-subtitle">
                                تعديل قراءة العداد: {{ $meterReading->reading_number }}
                            </div>
                        </div>
                        <a href="{{ route('admin.meter-readings.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-right me-2"></i>
                            العودة للقائمة
                        </a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.meter-readings.update', $meterReading) }}" method="POST" id="meterReadingForm">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                {{-- البيانات الأساسية --}}
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        البيانات الأساسية
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">المشترك <span class="text-danger">*</span></label>
                                    <select name="subscriber_id" id="subscriber_id" class="form-select @error('subscriber_id') is-invalid @enderror" required>
                                        <option value="">اختر المشترك</option>
                                        @foreach($subscribers as $sub)
                                            <option value="{{ $sub->id }}" 
                                                {{ old('subscriber_id', $meterReading->subscriber_id) == $sub->id ? 'selected' : '' }}
                                                data-meter-number="{{ $sub->meter_number ?? '' }}">
                                                {{ $sub->subscription_number }} - {{ $sub->subscriber_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subscriber_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">رقم العداد <span class="text-danger">*</span></label>
                                    <input type="text" name="meter_number" id="meter_number" class="form-control @error('meter_number') is-invalid @enderror" 
                                           value="{{ old('meter_number', $meterReading->meter_number) }}" readonly style="background-color: #f8f9fa;">
                                    <small class="form-text text-muted">يتم تعبئته تلقائياً من بيانات المشترك</small>
                                    @error('meter_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- بيانات القراءة --}}
                                <div class="col-12 mt-4">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-speedometer2 me-2"></i>
                                        بيانات القراءة
                                    </h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">القراءة السابقة <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="previous_reading" id="previous_reading" 
                                           class="form-control @error('previous_reading') is-invalid @enderror" 
                                           value="{{ old('previous_reading', $meterReading->previous_reading) }}" required>
                                    <small class="form-text text-muted" id="previous_reading_note">
                                        @if($lastReading)
                                            آخر قراءة قبل هذه: {{ number_format($lastReading->current_reading, 2) }} بتاريخ {{ $lastReading->reading_date->format('Y-m-d') }}
                                        @else
                                            لا توجد قراءة سابقة
                                        @endif
                                    </small>
                                    @error('previous_reading')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">القراءة الحالية <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="current_reading" id="current_reading" 
                                           class="form-control @error('current_reading') is-invalid @enderror" 
                                           value="{{ old('current_reading', $meterReading->current_reading) }}" required>
                                    @error('current_reading')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">قيمة الاستهلاك (Kwh) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="consumption_kwh" id="consumption_kwh" 
                                           class="form-control @error('consumption_kwh') is-invalid @enderror" 
                                           value="{{ old('consumption_kwh', $meterReading->consumption_kwh) }}" required readonly style="background-color: #f8f9fa;">
                                    <small class="form-text text-muted">يتم حسابها تلقائياً</small>
                                    @error('consumption_kwh')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- تنبيه القراءة غير الطبيعية --}}
                                @php
                                    $currentConsumption = old('consumption_kwh', $meterReading->consumption_kwh);
                                    $maxKwh = (float) \App\Models\Setting::get('abnormal_max_kwh', 9999);
                                    $showAbnormal = $currentConsumption <= 0 || ($maxKwh > 0 && $currentConsumption > $maxKwh);
                                @endphp
                                <div class="col-12 {{ $showAbnormal ? '' : 'd-none' }}" id="abnormalAlert">
                                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-0">
                                        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
                                        <div>
                                            <strong>تنبيه: قراءة غير طبيعية</strong><br>
                                            هذه القراءة صُنِّفت تلقائياً كغير طبيعية بسبب استهلاك صفري أو سالب أو يتجاوز الحد الأقصى المسموح به.
                                            ستظل موقوفة عن الاعتماد الجماعي وتحتاج إلى مراجعة واعتماد منفرد مع ذكر السبب.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ القراءة <span class="text-danger">*</span></label>
                                    <input type="date" name="reading_date" id="reading_date" 
                                           class="form-control @error('reading_date') is-invalid @enderror" 
                                           value="{{ old('reading_date', $meterReading->reading_date->format('Y-m-d')) }}" 
                                           max="{{ date('Y-m-d') }}" required>
                                    @error('reading_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">فترة الاستهلاك (عدد الأيام)</label>
                                    <input type="number" name="consumption_period_days" id="consumption_period_days" 
                                           class="form-control @error('consumption_period_days') is-invalid @enderror" 
                                           value="{{ old('consumption_period_days', $meterReading->consumption_period_days) }}" min="1" readonly style="background-color: #f8f9fa;">
                                    <small class="form-text text-muted">يتم حسابها تلقائياً</small>
                                    @error('consumption_period_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">حالة القراءة <span class="text-danger">*</span></label>
                                    <select name="reading_status" class="form-select @error('reading_status') is-invalid @enderror" required>
                                        <option value="">اختر الحالة</option>
                                        @foreach($readingStatuses as $item)
                                            <option value="{{ $item->value }}" {{ old('reading_status', $meterReading->reading_status) == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                    @error('reading_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('admin.meter-readings.index') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i>
                                            إلغاء
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i>
                                            حفظ التعديلات
                                        </button>
                                    </div>
                                </div>
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
    <script>
        $(document).ready(function() {
            $('#subscriber_id').select2({
                placeholder: 'اختر المشترك',
                allowClear: true,
                dir: 'rtl'
            });

            // تعبئة رقم العداد تلقائياً عند اختيار المشترك
            $('#subscriber_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const meterNumber = selectedOption.data('meter-number');
                $('#meter_number').val(meterNumber || '');
            });

            // حساب الاستهلاك تلقائياً
            const abnormalMaxKwh = {{ (float) \App\Models\Setting::get('abnormal_max_kwh', 9999) }};

            function calculateConsumption() {
                const previous = parseFloat($('#previous_reading').val()) || 0;
                const current = parseFloat($('#current_reading').val()) || 0;
                
                if (current >= previous) {
                    const consumption = current - previous;
                    $('#consumption_kwh').val(consumption.toFixed(2));
                    const isAbnormal = consumption <= 0 || (abnormalMaxKwh > 0 && consumption > abnormalMaxKwh);
                    $('#abnormalAlert').toggleClass('d-none', !isAbnormal);
                } else {
                    $('#consumption_kwh').val('');
                    $('#abnormalAlert').addClass('d-none');
                }
            }

            // حساب فترة الاستهلاك عند تغيير التاريخ
            $('#reading_date').on('change', function() {
                const readingDate = $(this).val();
                const subscriberId = $('#subscriber_id').val();
                const currentReadingId = {{ $meterReading->id }};
                if (readingDate && subscriberId) {
                    $.ajax({
                        url: '{{ route("admin.meter-readings.last-reading") }}',
                        method: 'GET',
                        data: { subscriber_id: subscriberId },
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        success: function(response) {
                            if (response.success && response.last_reading.reading_date) {
                                const lastDate = new Date(response.last_reading.reading_date);
                                const curDate = new Date(readingDate);
                                const diffDays = Math.round((curDate - lastDate) / (1000 * 60 * 60 * 24));
                                $('#consumption_period_days').val(diffDays > 0 ? diffDays : 1);
                            } else {
                                $('#consumption_period_days').val(7);
                            }
                        }
                    });
                }
            });

            $('#current_reading').on('input', calculateConsumption);
            $('#previous_reading').on('input', calculateConsumption);
        });
    </script>
@endpush

