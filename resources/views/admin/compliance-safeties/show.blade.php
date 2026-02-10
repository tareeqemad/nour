@extends('layouts.admin')

@section('title', 'تفاصيل سجل الامتثال والسلامة')

@php
    $breadcrumbTitle = 'تفاصيل سجل الامتثال والسلامة';
    $breadcrumbParent = 'الامتثال والسلامة';
    $breadcrumbParentUrl = route('admin.compliance-safeties.index');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/compliance-safeties.css') }}">
@endpush

@section('content')
    <div class="compliance-safeties-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h1 class="log-title mb-0">
                                <i class="bi bi-shield-check me-2"></i>
                                تفاصيل سجل الامتثال والسلامة
                            </h1>
                            <div class="log-subtitle text-muted mt-1">
                                @if($complianceSafety->operator)
                                    {{ $complianceSafety->operator->name }}
                                    @if($complianceSafety->operator->unit_number)
                                        <span class="badge bg-secondary text-white ms-1">{{ $complianceSafety->operator->unit_number }}</span>
                                    @endif
                                    <span class="mx-2">·</span>
                                @endif
                                @if($complianceSafety->last_inspection_date)
                                    {{ $complianceSafety->last_inspection_date->format('Y-m-d') }}
                                @else
                                    غير محدد
                                @endif
                                @if($complianceSafety->safetyCertificateStatusDetail)
                                    <span class="mx-2">·</span>
                                    <span class="badge text-white bg-{{ $complianceSafety->safetyCertificateStatusDetail->getBadgeColor() ?? 'secondary' }}">{{ $complianceSafety->safetyCertificateStatusDetail->label }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('update', $complianceSafety)
                                <a href="{{ route('admin.compliance-safeties.edit', $complianceSafety) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil me-1"></i>
                                    تعديل
                                </a>
                            @endcan
                            <a href="{{ route('admin.compliance-safeties.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-right me-1"></i>
                                رجوع
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border shadow-none h-100">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-2">
                                            <i class="bi bi-info-circle me-1"></i>معلومات إضافية
                                        </h6>
                                        <div>
                                            @if($complianceSafety->generationUnit)
                                                <div class="mb-2">
                                                    <span class="text-muted d-block">وحدة التوليد</span>
                                                    <a href="{{ route('admin.generation-units.show', $complianceSafety->generationUnit) }}" class="text-decoration-none">{{ $complianceSafety->generationUnit->name }} {{ $complianceSafety->generationUnit->unit_code ? '(' . $complianceSafety->generationUnit->unit_code . ')' : '' }}</a>
                                                </div>
                                            @endif
                                            @if($complianceSafety->generator)
                                                <div class="mb-2">
                                                    <span class="text-muted d-block">المولد</span>
                                                    <a href="{{ route('admin.generators.show', $complianceSafety->generator) }}" class="text-decoration-none">{{ $complianceSafety->generator->generator_number }} — {{ $complianceSafety->generator->name }}</a>
                                                </div>
                                            @endif
                                            <div class="mb-2">
                                                <span class="text-muted d-block">جهة التفتيش</span>
                                                <span>{{ $complianceSafety->inspection_authority ?: '—' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-muted d-block">المستخدم الذي أنشأ السجل</span>
                                                <span>{{ $complianceSafety->creator?->name ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border shadow-none h-100">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-2">
                                            <i class="bi bi-clipboard-check me-1"></i>نتيجة التفتيش
                                        </h6>
                                        <div class="text-break">{{ $complianceSafety->inspection_result ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border shadow-none h-100">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>المخالفات المسجلة
                                        </h6>
                                        @if($complianceSafety->violations)
                                            <div class="text-break">{{ $complianceSafety->violations }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card border shadow-none">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted fw-semibold mb-2">
                                            <i class="bi bi-clock-history me-1"></i>معلومات النظام
                                        </h6>
                                        <div class="text-muted d-flex flex-wrap gap-3">
                                            <span><i class="bi bi-calendar-plus me-1"></i>إنشاء: {{ $complianceSafety->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                            <span><i class="bi bi-pencil-square me-1"></i>تحديث: {{ $complianceSafety->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                        </div>
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
