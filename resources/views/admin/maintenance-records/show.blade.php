@extends('layouts.admin')

@section('title', 'تفاصيل سجل الصيانة')

@php
    $breadcrumbTitle = 'تفاصيل سجل الصيانة';
    $breadcrumbParent = 'سجلات الصيانة';
    $breadcrumbParentUrl = route('admin.maintenance-records.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/maintenance-records.css') }}">
@endpush

@section('content')
    <div class="maintenance-records-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-tools me-2"></i>
                                    تفاصيل سجل الصيانة
                                </div>
                                <div class="log-subtitle">
                                    @if($maintenanceRecord->generator)
                                        المولد: {{ $maintenanceRecord->generator->name }} | 
                                    @endif
                                    التاريخ: {{ $maintenanceRecord->maintenance_date->format('Y-m-d') }}
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                @can('update', $maintenanceRecord)
                                    <a href="{{ route('admin.maintenance-records.edit', $maintenanceRecord) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil me-1"></i>
                                        تعديل
                                    </a>
                                @endcan
                                <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    رجوع
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
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
                                        المولد
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->generator)
                                            <a href="{{ route('admin.generators.show', $maintenanceRecord->generator) }}" class="text-decoration-none">
                                                {{ $maintenanceRecord->generator->generator_number }} - {{ $maintenanceRecord->generator->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag text-info me-1"></i>
                                        نوع الصيانة
                                    </label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-{{ $maintenanceRecord->maintenanceTypeDetail?->getBadgeColor() ?? 'secondary' }}">
                                            {{ $maintenanceRecord->maintenanceTypeDetail?->label ?? '-' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calendar3 text-primary me-1"></i>
                                        تاريخ الصيانة
                                    </label>
                                    <div class="form-control-plaintext">
                                        {{ $maintenanceRecord->maintenance_date->format('Y-m-d') }}
                                        <small class="text-muted ms-2">({{ $maintenanceRecord->maintenance_date->diffForHumans() }})</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge text-success me-1"></i>
                                        المستخدم الذي أنشأ السجل
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->creator)
                                            <span class="fw-semibold">{{ $maintenanceRecord->creator->name }}</span>
                                            @if($maintenanceRecord->technician_name && $maintenanceRecord->technician_name !== $maintenanceRecord->creator->name)
                                                <br>
                                                <small class="text-muted">اسم الفني: {{ $maintenanceRecord->technician_name }}</small>
                                            @endif
                                        @elseif($maintenanceRecord->technician_name)
                                            {{ $maintenanceRecord->technician_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>

                                @if($maintenanceRecord->technician_name && (!$maintenanceRecord->creator || $maintenanceRecord->technician_name !== $maintenanceRecord->creator->name))
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge text-success me-1"></i>
                                        اسم الفني المسؤول
                                    </label>
                                    <div class="form-control-plaintext">
                                        {{ $maintenanceRecord->technician_name ?? '-' }}
                                    </div>
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
                                    <div class="form-control-plaintext bg-light p-3 rounded">
                                        {{ $maintenanceRecord->work_performed ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Time Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-clock-history text-danger me-2"></i>
                                معلومات الوقت
                            </h6>
                            <div class="row g-3">
                                @if($maintenanceRecord->start_time)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-clock text-info me-1"></i>
                                        وقت البدء
                                    </label>
                                    <div class="form-control-plaintext">
                                        {{ \Carbon\Carbon::parse($maintenanceRecord->start_time)->format('H:i') }}
                                    </div>
                                </div>
                                @endif

                                @if($maintenanceRecord->end_time)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-clock-history text-warning me-1"></i>
                                        وقت الانتهاء
                                    </label>
                                    <div class="form-control-plaintext">
                                        {{ \Carbon\Carbon::parse($maintenanceRecord->end_time)->format('H:i') }}
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-hourglass-split text-primary me-1"></i>
                                        زمن التوقف (ساعات)
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->downtime_hours)
                                            <strong class="text-primary">{{ number_format($maintenanceRecord->downtime_hours, 2) }} ساعة</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
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
                                @if($maintenanceRecord->parts_cost)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-box-seam text-info me-1"></i>
                                        تكلفة القطع (₪)
                                    </label>
                                    <div class="form-control-plaintext">
                                        {{ number_format($maintenanceRecord->parts_cost, 2) }} ₪
                                    </div>
                                </div>
                                @endif

                                @if($maintenanceRecord->labor_hours)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-clock text-warning me-1"></i>
                                        ساعات العمل
                                    </label>
                                    <div class="form-control-plaintext">
                                        {{ number_format($maintenanceRecord->labor_hours, 2) }} ساعة
                                    </div>
                                </div>
                                @endif

                                @if($maintenanceRecord->labor_rate_per_hour)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-currency-exchange text-primary me-1"></i>
                                        أجر الساعة (₪)
                                    </label>
                                    <div class="form-control-plaintext">
                                        {{ number_format($maintenanceRecord->labor_rate_per_hour, 2) }} ₪
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calculator text-success me-1"></i>
                                        التكلفة الإجمالية (₪)
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->maintenance_cost)
                                            <strong class="text-success fs-5">{{ number_format($maintenanceRecord->maintenance_cost, 2) }} ₪</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Generator Information Section -->
                        @if($maintenanceRecord->generator)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-muted">
                                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                                معلومات المولد
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-lightning-charge text-warning me-1"></i>
                                        اسم المولد
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->generator)
                                            <a href="{{ route('admin.generators.show', $maintenanceRecord->generator) }}" class="text-decoration-none">
                                                {{ $maintenanceRecord->generator->name }}
                                                @if($maintenanceRecord->generator->trashed())
                                                    <span class="badge bg-secondary ms-1" title="مولد محذوف">محذوف</span>
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-muted">مولد محذوف</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-hash text-primary me-1"></i>
                                        رقم المولد
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->generator)
                                            <code class="text-primary">{{ $maintenanceRecord->generator->generator_number }}</code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>

                                @if($maintenanceRecord->generator && $maintenanceRecord->generator->operator)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-building text-info me-1"></i>
                                        المشغل
                                    </label>
                                    <div class="form-control-plaintext">
                                        <a href="{{ route('admin.operators.show', $maintenanceRecord->generator->operator) }}" class="text-decoration-none">
                                            {{ $maintenanceRecord->generator->operator->name }}
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-speedometer text-success me-1"></i>
                                        القدرة
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->generator)
                                            {{ number_format($maintenanceRecord->generator->capacity_kva, 2) }} KVA
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        الحالة
                                    </label>
                                    <div class="form-control-plaintext">
                                        @if($maintenanceRecord->generator)
                                            <span class="badge bg-{{ $maintenanceRecord->generator->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ $maintenanceRecord->generator->status === 'active' ? 'نشط' : 'غير نشط' }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.maintenance-records.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>
                                رجوع
                            </a>
                            @can('update', $maintenanceRecord)
                                <a href="{{ route('admin.maintenance-records.edit', $maintenanceRecord) }}" class="btn btn-primary px-4">
                                    <i class="bi bi-pencil me-2"></i>
                                    تعديل
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .form-control-plaintext {
        min-height: 38px;
        padding: 0.5rem 0;
        line-height: 1.5;
        display: flex;
        align-items: center;
    }
    
    .form-control-plaintext.bg-light {
        min-height: auto;
        padding: 0.75rem;
    }
    
    code {
        background-color: rgba(0, 86, 179, 0.1);
        padding: 0.2em 0.4em;
        border-radius: 3px;
        font-size: 0.9em;
    }
    
    a.text-decoration-none:hover {
        text-decoration: underline !important;
    }
</style>
@endpush
