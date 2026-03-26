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

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header-form title="تفاصيل سجل الصيانة" icon="bi-tools" :backRoute="route('admin.maintenance-records.index')">
                        <span class="badge bg-{{ $badgeColor }} me-2" style="font-size: 0.85rem;">{{ $typeLabel }}</span>
                        @can('update', $maintenanceRecord)
                            <a href="{{ route('admin.maintenance-records.edit', $maintenanceRecord) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil me-1"></i>تعديل
                            </a>
                        @endcan
                    </x-admin.card-header-form>

                    <div class="card-body p-4">
                        {{-- Meta info --}}
                        <div class="d-flex flex-wrap gap-3 mb-4 text-muted small">
                            @if($maintenanceRecord->generator)
                                <span><i class="bi bi-gear me-1"></i><a href="{{ route('admin.generators.show', $maintenanceRecord->generator) }}" class="text-decoration-none">{{ $maintenanceRecord->generator->generator_number }} — {{ $maintenanceRecord->generator->name }}</a></span>
                            @endif
                            <span><i class="bi bi-calendar me-1"></i>{{ $maintenanceRecord->maintenance_date->format('Y-m-d') }} ({{ $maintenanceRecord->maintenance_date->diffForHumans() }})</span>
                            @php
                                $technicianDisplay = $maintenanceRecord->creator
                                    ? $maintenanceRecord->creator->name . ($maintenanceRecord->technician_name && $maintenanceRecord->technician_name !== $maintenanceRecord->creator->name ? ' (' . $maintenanceRecord->technician_name . ')' : '')
                                    : ($maintenanceRecord->technician_name ?? null);
                            @endphp
                            @if($technicianDisplay)
                                <span><i class="bi bi-person me-1"></i>الفني: {{ $technicianDisplay }}</span>
                            @endif
                        </div>

                        {{-- الأعمال المنفذة --}}
                        @if($maintenanceRecord->work_performed)
                            <x-admin.section title="الأعمال المنفذة" icon="bi-clipboard-check" :boxed="true">
                                <div class="text-break" style="white-space: pre-wrap; line-height: 1.6;">{{ $maintenanceRecord->work_performed }}</div>
                            </x-admin.section>
                        @endif

                        {{-- الوقت --}}
                        @if($maintenanceRecord->start_time || $maintenanceRecord->end_time || $maintenanceRecord->downtime_hours)
                            <x-admin.section title="الوقت" icon="bi-clock-history" :boxed="true">
                                <div class="row g-3">
                                    @if($maintenanceRecord->start_time)
                                        <div class="col-md-4">
                                            <x-admin.field label="بدء" :value="\Carbon\Carbon::parse($maintenanceRecord->start_time)->format('H:i')" />
                                        </div>
                                    @endif
                                    @if($maintenanceRecord->end_time)
                                        <div class="col-md-4">
                                            <x-admin.field label="انتهاء" :value="\Carbon\Carbon::parse($maintenanceRecord->end_time)->format('H:i')" />
                                        </div>
                                    @endif
                                    @if($maintenanceRecord->downtime_hours)
                                        <div class="col-md-4">
                                            <x-admin.field label="زمن التوقف" :value="number_format($maintenanceRecord->downtime_hours, 2) . ' ساعة'" />
                                        </div>
                                    @endif
                                </div>
                            </x-admin.section>
                        @endif

                        {{-- التكلفة --}}
                        @if($maintenanceRecord->parts_cost || $maintenanceRecord->labor_hours || $maintenanceRecord->labor_rate_per_hour || $maintenanceRecord->maintenance_cost)
                            <x-admin.section title="التكلفة" icon="bi-cash-stack" :boxed="true">
                                <div class="row g-3">
                                    @if($maintenanceRecord->parts_cost)
                                        <div class="col-md-3">
                                            <x-admin.field label="قطع غيار" :value="number_format($maintenanceRecord->parts_cost, 2) . ' ₪'" />
                                        </div>
                                    @endif
                                    @if($maintenanceRecord->labor_hours)
                                        <div class="col-md-3">
                                            <x-admin.field label="ساعات عمل" :value="number_format($maintenanceRecord->labor_hours, 2)" />
                                        </div>
                                    @endif
                                    @if($maintenanceRecord->labor_rate_per_hour)
                                        <div class="col-md-3">
                                            <x-admin.field label="أجر الساعة" :value="number_format($maintenanceRecord->labor_rate_per_hour, 2) . ' ₪'" />
                                        </div>
                                    @endif
                                    @if($maintenanceRecord->maintenance_cost)
                                        <div class="col-md-3">
                                            <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">الإجمالي</div>
                                            <div style="font-size: 1.1rem; color: var(--bs-success, #198754); font-weight: 700;">{{ number_format($maintenanceRecord->maintenance_cost, 2) }} ₪</div>
                                        </div>
                                    @endif
                                </div>
                            </x-admin.section>
                        @endif

                        {{-- تفاصيل المولد --}}
                        @if($maintenanceRecord->generator)
                            <x-admin.section title="تفاصيل المولد" icon="bi-lightning-charge" :boxed="true">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <x-admin.field label="السعة" :value="number_format($maintenanceRecord->generator->capacity_kva, 0) . ' KVA'" />
                                    </div>
                                    <div class="col-md-3">
                                        <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">الحالة</div>
                                        <span class="badge bg-{{ $maintenanceRecord->generator->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ $maintenanceRecord->generator->status === 'active' ? 'نشط' : 'غير نشط' }}
                                        </span>
                                    </div>
                                    @if($maintenanceRecord->generator->operator)
                                        <div class="col-md-3">
                                            <x-admin.field label="المشغل" :value="$maintenanceRecord->generator->operator->name" />
                                        </div>
                                    @endif
                                    <div class="col-md-3">
                                        <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); font-weight: 600; margin-bottom: 0.15rem;">&nbsp;</div>
                                        <a href="{{ route('admin.generators.show', $maintenanceRecord->generator) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>عرض المولد
                                        </a>
                                    </div>
                                </div>
                            </x-admin.section>
                        @endif
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
