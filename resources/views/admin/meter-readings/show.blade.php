@extends('layouts.admin')

@section('title', 'تفاصيل قراءة العداد')

@php
    $breadcrumbTitle = 'تفاصيل قراءة العداد';
    $breadcrumbParent = 'قراءات العدادات';
    $breadcrumbParentUrl = route('admin.meter-readings.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تفاصيل قراءة العداد" icon="bi-speedometer2" :backRoute="route('admin.meter-readings.index')">
                        @if($meterReading->isEditable())
                            @can('update', $meterReading)
                                <a href="{{ route('admin.meter-readings.edit', $meterReading) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>
                                    تعديل
                                </a>
                            @endcan
                        @endif
                    </x-admin.card-header-form>

                    <div class="card-body">
                        <div class="row g-3">
                            {{-- البيانات الأساسية --}}
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    البيانات الأساسية
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">رقم القراءة</label>
                                <div class="form-control-plaintext">
                                    <code class="text-primary">{{ $meterReading->reading_number }}</code>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">المشترك</label>
                                <div class="form-control-plaintext">
                                    <div>
                                        <span class="fw-medium">{{ $meterReading->subscriber->subscriber_name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $meterReading->subscriber->subscription_number }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">رقم العداد</label>
                                <div class="form-control-plaintext">{{ $meterReading->getDisplayMeterNumber() ?: '—' }}</div>
                            </div>

                            {{-- بيانات القراءة --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-speedometer2 me-2"></i>
                                    بيانات القراءة
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">القراءة السابقة</label>
                                <div class="form-control-plaintext">
                                    <span class="fw-medium">{{ number_format($meterReading->getDisplayPreviousReading(), 2) }}</span>
                                    @if($meterReading->isFirstForSubscriber() && (float) $meterReading->previous_reading === 0.0 && $meterReading->subscriber->opening_reading)
                                        <small class="text-muted d-block">(قراءة افتتاحية)</small>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">القراءة الحالية</label>
                                <div class="form-control-plaintext">
                                    <span class="fw-bold text-primary">{{ number_format($meterReading->current_reading, 2) }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">قيمة الاستهلاك (Kwh)</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="bi bi-lightning-charge me-1"></i>
                                        {{ number_format($meterReading->getDisplayConsumptionKwh(), 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">تاريخ القراءة</label>
                                <div class="form-control-plaintext">
                                    {{ $meterReading->reading_date->format('Y-m-d') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">فترة الاستهلاك (عدد الأيام)</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-secondary">{{ $meterReading->consumption_period_days }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">حالة القراءة</label>
                                <div class="form-control-plaintext">
                                    @php
                                        $statusClass = match($meterReading->reading_status) {
                                            1 => 'bg-success',
                                            2 => 'bg-warning text-dark',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $meterReading->reading_status_name }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">الإجراء</label>
                                <div class="form-control-plaintext">
                                    @php
                                        $actionClass = match($meterReading->action_status) {
                                            0 => 'bg-secondary',
                                            1 => 'bg-success',
                                            2 => 'bg-info',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $actionClass }}">{{ $meterReading->action_status_name }}</span>
                                </div>
                            </div>

                            {{-- معلومات التتبع --}}
                            <div class="col-12 mt-4">
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-clock-history me-2"></i>
                                    معلومات التتبع
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">المدخل</label>
                                <div class="form-control-plaintext">
                                    {{ $meterReading->creator->name ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">تاريخ الإدخال</label>
                                <div class="form-control-plaintext">
                                    {{ $meterReading->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            @if($meterReading->last_updated_by)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted">آخر من عدل</label>
                                    <div class="form-control-plaintext">
                                        {{ $meterReading->updater->name ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted">تاريخ التعديل</label>
                                    <div class="form-control-plaintext">
                                        {{ $meterReading->updated_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

