@extends('layouts.admin')

@section('title', 'تفاصيل قضية تفتيش وتعدي')

@php
    $breadcrumbTitle = 'تفاصيل القضية';
    $breadcrumbParent = 'قضايا التفتيش والتعدي';
    $breadcrumbParentUrl = route('admin.inspection-violation-cases.index');
@endphp

@section('content')
    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                تفاصيل قضية تفتيش وتعدي
                            </h5>
                            <div class="general-subtitle">
                                {{ $inspection_violation_case->subscriber_name }}
                                @if($inspection_violation_case->case_date)
                                    <span class="mx-1">·</span>
                                    {{ $inspection_violation_case->case_date->format('Y-m-d') }}
                                @endif
                                <span class="mx-1">·</span>
                                @php $badgeClass = 'bg-' . \App\Models\InspectionViolationCase::statusCssClass($inspection_violation_case->status); @endphp
                                <span class="badge {{ $badgeClass }}">{{ \App\Models\InspectionViolationCase::statusLabels()[$inspection_violation_case->status] ?? $inspection_violation_case->status }}</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('update', $inspection_violation_case)
                                <a href="{{ route('admin.inspection-violation-cases.edit', $inspection_violation_case) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil me-1"></i>
                                    تعديل
                                </a>
                            @endcan
                            <a href="{{ route('admin.inspection-violation-cases.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-right me-1"></i>
                                رجوع
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            {{-- بيانات القضية --}}
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border shadow-none h-100">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-3">
                                            <i class="bi bi-person me-1"></i>بيانات المشترك
                                        </h6>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">المحافظة</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->governorateDetail?->label ?? '—' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">رقم الاشتراك</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->subscription_number ?: '—' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">اسم المشترك</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->subscriber_name }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block small">اسم المنتفع</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->beneficiary_name ?: '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- تفاصيل القضية --}}
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border shadow-none h-100">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-3">
                                            <i class="bi bi-clipboard-data me-1"></i>تفاصيل القضية
                                        </h6>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">تاريخ القضية</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->case_date?->format('Y-m-d') ?? '—' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">حالة القضية</span>
                                            <span class="badge {{ $badgeClass }}">{{ \App\Models\InspectionViolationCase::statusLabels()[$inspection_violation_case->status] ?? $inspection_violation_case->status }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">المشغل</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->operator?->name ?? '—' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block small">وحدة التوليد</span>
                                            <span class="fw-semibold">
                                                @if($inspection_violation_case->generationUnit)
                                                    {{ $inspection_violation_case->generationUnit->name }}
                                                    @if($inspection_violation_case->generationUnit->unit_code)
                                                        ({{ $inspection_violation_case->generationUnit->unit_code }})
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- معلومات النظام --}}
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border shadow-none h-100">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-3">
                                            <i class="bi bi-clock-history me-1"></i>معلومات النظام
                                        </h6>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">المُدخل</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->creator?->name ?? '—' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">تاريخ الإدخال</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-muted d-block small">آخر من عدّل</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->updater?->name ?? '—' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted d-block small">تاريخ التعديل</span>
                                            <span class="fw-semibold">{{ $inspection_violation_case->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- بيان القضية --}}
                            <div class="col-12">
                                <div class="card border shadow-none">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-2">
                                            <i class="bi bi-journal-text me-1"></i>بيان القضية / التعدي
                                        </h6>
                                        <div class="text-break">{{ $inspection_violation_case->statement ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
