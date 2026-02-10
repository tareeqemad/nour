@extends('layouts.admin')

@section('title', 'تفاصيل سجل الصيانة')

@php
    $breadcrumbTitle = 'تفاصيل سجل الصيانة';
    $breadcrumbParent = 'سجلات الصيانة';
    $breadcrumbParentUrl = route('admin.maintenance-records.index');
    $typeDetail = $maintenanceRecord->maintenanceTypeDetail;
    $badgeColor = $typeDetail ? $typeDetail->getBadgeColor() : 'secondary';
    $typeLabel = $typeDetail?->label ?? '—';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/maintenance-records.css') }}">
@endpush

@section('content')
    <div class="maintenance-records-page maintenance-record-show">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    {{-- Header: title, badge, actions --}}
                    <div class="log-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div>
                                <h1 class="maintenance-record-show__title">
                                    <i class="bi bi-tools me-2"></i>
                                    تفاصيل سجل الصيانة
                                </h1>
                                <div class="maintenance-record-show__meta">
                                    @if($maintenanceRecord->generator)
                                        <a href="{{ route('admin.generators.show', $maintenanceRecord->generator) }}" class="maintenance-record-show__generator-link">
                                            {{ $maintenanceRecord->generator->generator_number }} — {{ $maintenanceRecord->generator->name }}
                                        </a>
                                        <span class="maintenance-record-show__meta-sep">·</span>
                                    @endif
                                    <span>{{ $maintenanceRecord->maintenance_date->format('Y-m-d') }}</span>
                                    <span class="text-muted ms-1">({{ $maintenanceRecord->maintenance_date->diffForHumans() }})</span>
                                    @php
                                        $technicianDisplay = $maintenanceRecord->creator
                                            ? $maintenanceRecord->creator->name . ($maintenanceRecord->technician_name && $maintenanceRecord->technician_name !== $maintenanceRecord->creator->name ? ' (' . $maintenanceRecord->technician_name . ')' : '')
                                            : ($maintenanceRecord->technician_name ?? null);
                                    @endphp
                                    @if($technicianDisplay)
                                        <span class="maintenance-record-show__meta-sep">·</span>
                                        <span>الفني: {{ $technicianDisplay }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge maintenance-record-show__type-badge bg-{{ $badgeColor }}">{{ $typeLabel }}</span>
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

                    <div class="card-body p-4">
                        {{-- Work performed --}}
                        @if($maintenanceRecord->work_performed)
                        <div class="maintenance-record-show__block">
                            <h6 class="maintenance-record-show__block-title">
                                <i class="bi bi-clipboard-check me-2"></i>
                                الأعمال المنفذة
                            </h6>
                            <div class="maintenance-record-show__work-text">{{ $maintenanceRecord->work_performed }}</div>
                        </div>
                        @endif

                        {{-- Time (only if any present) --}}
                        @if($maintenanceRecord->start_time || $maintenanceRecord->end_time || $maintenanceRecord->downtime_hours)
                        <div class="maintenance-record-show__block">
                            <h6 class="maintenance-record-show__block-title">
                                <i class="bi bi-clock-history me-2"></i>
                                الوقت
                            </h6>
                            <div class="maintenance-record-show__row">
                                @if($maintenanceRecord->start_time)
                                    <span>بدء: {{ \Carbon\Carbon::parse($maintenanceRecord->start_time)->format('H:i') }}</span>
                                @endif
                                @if($maintenanceRecord->end_time)
                                    <span>انتهاء: {{ \Carbon\Carbon::parse($maintenanceRecord->end_time)->format('H:i') }}</span>
                                @endif
                                @if($maintenanceRecord->downtime_hours)
                                    <span>زمن التوقف: <strong>{{ number_format($maintenanceRecord->downtime_hours, 2) }}</strong> ساعة</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Cost (only if any present) --}}
                        @if($maintenanceRecord->parts_cost || $maintenanceRecord->labor_hours || $maintenanceRecord->labor_rate_per_hour || $maintenanceRecord->maintenance_cost)
                        <div class="maintenance-record-show__block">
                            <h6 class="maintenance-record-show__block-title">
                                <i class="bi bi-cash-stack me-2"></i>
                                التكلفة
                            </h6>
                            <div class="maintenance-record-show__row">
                                @if($maintenanceRecord->parts_cost)
                                    <span>قطع: {{ number_format($maintenanceRecord->parts_cost, 2) }} ₪</span>
                                @endif
                                @if($maintenanceRecord->labor_hours)
                                    <span>ساعات عمل: {{ number_format($maintenanceRecord->labor_hours, 2) }}</span>
                                @endif
                                @if($maintenanceRecord->labor_rate_per_hour)
                                    <span>أجر الساعة: {{ number_format($maintenanceRecord->labor_rate_per_hour, 2) }} ₪</span>
                                @endif
                                @if($maintenanceRecord->maintenance_cost)
                                    <span class="maintenance-record-show__total-cost">{{ number_format($maintenanceRecord->maintenance_cost, 2) }} ₪ إجمالي</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Generator details only (name/link already in header) --}}
                        @if($maintenanceRecord->generator)
                        <div class="maintenance-record-show__block">
                            <h6 class="maintenance-record-show__block-title">
                                <i class="bi bi-lightning-charge me-2"></i>
                                تفاصيل المولد
                            </h6>
                            <div class="maintenance-record-show__row">
                                {{ number_format($maintenanceRecord->generator->capacity_kva, 0) }} KVA
                                <span class="badge bg-{{ $maintenanceRecord->generator->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $maintenanceRecord->generator->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                                @if($maintenanceRecord->generator->operator)
                                    <a href="{{ route('admin.operators.show', $maintenanceRecord->generator->operator) }}" class="text-decoration-none text-muted">{{ $maintenanceRecord->generator->operator->name }}</a>
                                @endif
                                <a href="{{ route('admin.generators.show', $maintenanceRecord->generator) }}" class="text-decoration-none ms-1">عرض المولد</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .maintenance-record-show__title {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: var(--log-text, #0f172a);
    }
    .maintenance-record-show__meta {
        font-size: 0.9rem;
        color: var(--log-muted, #6b7280);
        margin-top: 0.25rem;
    }
    .maintenance-record-show__meta-sep { margin: 0 0.35rem; }
    .maintenance-record-show__generator-link { color: inherit; text-decoration: none; }
    .maintenance-record-show__generator-link:hover { text-decoration: underline; }
    .maintenance-record-show__type-badge {
        font-size: 0.875rem;
        padding: 0.35em 0.65em;
        font-weight: 600;
    }
    .maintenance-record-show__block {
        padding: 1rem 0;
        border-bottom: 1px solid var(--log-border-2, #eef2f7);
    }
    .maintenance-record-show__block:last-of-type { border-bottom: none; }
    .maintenance-record-show__block-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--log-muted, #6b7280);
        margin: 0 0 0.5rem 0;
    }
    .maintenance-record-show__work-text {
        background: var(--log-subtle, #f8fafc);
        padding: 1rem;
        border-radius: 8px;
        white-space: pre-wrap;
        line-height: 1.5;
    }
    .maintenance-record-show__row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.5rem;
        align-items: center;
    }
    .maintenance-record-show__row code {
        background: rgba(0, 86, 179, 0.1);
        padding: 0.2em 0.4em;
        border-radius: 4px;
        font-size: 0.9em;
    }
    .maintenance-record-show__total-cost { font-weight: 700; color: var(--bs-success, #198754); }
</style>
@endpush
