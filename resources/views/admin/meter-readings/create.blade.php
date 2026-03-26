@extends('layouts.admin')

@section('title', 'إضافة قراءة عداد جديدة')

@php
    $breadcrumbTitle = 'إضافة قراءة عداد جديدة';
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
                <x-admin.card>
                    <x-admin.card-header-form title="إضافة قراءة عداد جديدة" icon="bi-speedometer2" :backRoute="route('admin.meter-readings.index')" />

                    <div class="card-body">
                        <form action="{{ route('admin.meter-readings.store') }}" method="POST" id="meterReadingForm">
                            @csrf

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
                                                {{ old('subscriber_id', $selectedSubscriber?->id) == $sub->id ? 'selected' : '' }}
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
                           value="{{ old('meter_number', $selectedSubscriber->meter_number ?? '') }}" readonly style="background-color: #f8f9fa;">
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
                                           value="{{ old('previous_reading', $lastReading->current_reading ?? ($selectedSubscriber->opening_reading ?? 0)) }}" 
                                           required readonly style="background-color: #f8f9fa;">
                                    <small class="form-text text-muted" id="previous_reading_note">
                                        @if($lastReading)
                                            آخر قراءة: {{ number_format($lastReading->current_reading, 2) }} بتاريخ {{ $lastReading->reading_date->format('Y-m-d') }}
                                        @elseif(isset($selectedSubscriber) && $selectedSubscriber?->opening_reading !== null)
                                            قراءة افتتاحية: {{ number_format($selectedSubscriber->opening_reading, 2) }}
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
                                           value="{{ old('current_reading') }}" required>
                                    @error('current_reading')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">قيمة الاستهلاك (Kwh) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="consumption_kwh" id="consumption_kwh" 
                                           class="form-control @error('consumption_kwh') is-invalid @enderror" 
                                           value="{{ old('consumption_kwh') }}" required readonly style="background-color: #f8f9fa;">
                                    <small class="form-text text-muted">يتم حسابها تلقائياً</small>
                                    @error('consumption_kwh')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- تنبيه القراءة غير الطبيعية --}}
                                <div class="col-12 d-none" id="abnormalAlert">
                                    <div style="background: var(--color-warning-bg, #FFFBEB); border: 1px solid var(--color-warning-border, #FCD34D); border-radius: 8px; padding: 0.75rem 1rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="color: var(--color-warning, #F59E0B);"></i>
                                        <div style="font-size: 0.85rem; color: var(--color-warning-text, #B45309);">
                                            <strong>قراءة غير طبيعية</strong> — ستُصنّف تلقائياً وتحتاج مراجعة واعتماد منفرد.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">تاريخ القراءة <span class="text-danger">*</span></label>
                                    <input type="date" name="reading_date" id="reading_date" 
                                           class="form-control @error('reading_date') is-invalid @enderror" 
                                           value="{{ old('reading_date', date('Y-m-d')) }}" 
                                           max="{{ date('Y-m-d') }}" required>
                                    @error('reading_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">فترة الاستهلاك (عدد الأيام)</label>
                                    <input type="number" name="consumption_period_days" id="consumption_period_days" 
                                           class="form-control @error('consumption_period_days') is-invalid @enderror" 
                                           value="{{ old('consumption_period_days') }}" min="1" readonly style="background-color: #f8f9fa;">
                                    <small class="form-text text-muted">يتم حسابها تلقائياً</small>
                                    @error('consumption_period_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">حالة القراءة <span class="text-danger">*</span></label>
                                    <select name="reading_status" class="form-select @error('reading_status') is-invalid @enderror" required>
                                        <option value=""></option>
                                        @foreach($readingStatuses as $item)
                                            <option value="{{ $item->value }}" {{ old('reading_status', '1') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                    @error('reading_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end mt-3 pt-3" style="border-top: 1px solid var(--color-border-soft, #EDF1F5);">
                                        <a href="{{ route('admin.meter-readings.index') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i>
                                            إلغاء
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-lg me-1"></i>
                                            حفظ
                                        </button>
                                    </div>
                                </div>
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
        $(document).ready(function() {
            // Initialize Select2
            $('#subscriber_id').select2({
                placeholder: 'اختر المشترك',
                allowClear: true,
                dir: 'rtl'
            });

            const lastReadingUrl = @json(route('admin.meter-readings.last-reading'));
            
            // عند تغيير المشترك
            $('#subscriber_id').on('change', function() {
                const subscriberId = $(this).val();
                const selectedOption = $(this).find('option:selected');
                const meterNumber = selectedOption.data('meter-number') || '';
                
                // تحديث رقم العداد
                $('#meter_number').val(meterNumber);
                
                if (subscriberId) {
                    // جلب آخر قراءة
                    $.ajax({
                        url: lastReadingUrl,
                        method: 'GET',
                        data: { subscriber_id: subscriberId },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.last_reading.previous_reading !== undefined) {
                                    $('#previous_reading').val(response.last_reading.previous_reading);
                                    let note;
                                    if (response.last_reading.reading_date) {
                                        note = `آخر قراءة: ${parseFloat(response.last_reading.previous_reading).toFixed(2)} بتاريخ ${response.last_reading.reading_date}`;
                                    } else if (parseFloat(response.last_reading.previous_reading) > 0) {
                                        note = `قراءة افتتاحية: ${parseFloat(response.last_reading.previous_reading).toFixed(2)}`;
                                    } else {
                                        note = 'لا توجد قراءة سابقة';
                                    }
                                    $('#previous_reading_note').text(note);
                                    
                                    // تحديث تاريخ القراءة الأدنى
                                    if (response.last_reading.reading_date) {
                                        const minDate = new Date(response.last_reading.reading_date);
                                        minDate.setDate(minDate.getDate() + 1);
                                        $('#reading_date').attr('min', minDate.toISOString().split('T')[0]);
                                        
                                        // حساب فترة الاستهلاك تلقائياً
                                        calculatePeriod();
                                    }
                                } else {
                                    // لا توجد قراءة سابقة
                                    $('#previous_reading').val(0);
                                    $('#previous_reading_note').text('لا توجد قراءة سابقة');
                                    $('#reading_date').attr('min', '');
                                }
                                
                                if (response.subscriber.meter_number) {
                                    $('#meter_number').val(response.subscriber.meter_number);
                                }
                            } else {
                                alert(response.message || 'حدث خطأ أثناء جلب البيانات');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading last reading:', xhr);
                            alert('حدث خطأ أثناء جلب آخر قراءة');
                        }
                    });
                } else {
                    $('#previous_reading').val(0);
                    $('#previous_reading_note').text('لا توجد قراءة سابقة');
                    $('#reading_date').attr('min', '');
                }
                
                // إعادة حساب الاستهلاك
                calculateConsumption();
            });

            // حساب الاستهلاك تلقائياً
            const abnormalMaxKwh = {{ (float) \App\Models\Setting::get('abnormal_max_kwh', 9999) }};

            function calculateConsumption() {
                const previous = parseFloat($('#previous_reading').val()) || 0;
                const current = parseFloat($('#current_reading').val()) || 0;
                
                if (current >= previous) {
                    const consumption = current - previous;
                    $('#consumption_kwh').val(consumption.toFixed(2));
                    // تحقق من الحالة الشاذة
                    const isAbnormal = consumption <= 0 || (abnormalMaxKwh > 0 && consumption > abnormalMaxKwh);
                    $('#abnormalAlert').toggleClass('d-none', !isAbnormal);
                } else {
                    $('#consumption_kwh').val('');
                    $('#abnormalAlert').addClass('d-none');
                }
            }

            // حساب فترة الاستهلاك تلقائياً
            function calculatePeriod() {
                const readingDate = $('#reading_date').val();
                const subscriberId = $('#subscriber_id').val();
                
                if (readingDate && subscriberId) {
                    $.ajax({
                        url: lastReadingUrl,
                        method: 'GET',
                        data: { subscriber_id: subscriberId },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        success: function(response) {
                            if (response.success && response.last_reading.reading_date) {
                                const lastDate = new Date(response.last_reading.reading_date);
                                const currentDate = new Date(readingDate);
                                const diffTime = Math.abs(currentDate - lastDate);
                                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                                
                                if (diffDays > 0) {
                                    $('#consumption_period_days').val(diffDays);
                                }
                            }
                        }
                    });
                }
            }

            $('#current_reading').on('input', calculateConsumption);
            $('#previous_reading').on('input', calculateConsumption);
            $('#reading_date').on('change', calculatePeriod);
        });
    </script>
@endpush

