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
                <div class="log-card">
                    <div class="log-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                            <div>
                                <div class="log-title">
                                    <i class="bi bi-shield-check me-2"></i>
                                    تفاصيل سجل الامتثال والسلامة
                                </div>
                                <div class="log-subtitle">
                                    @if($complianceSafety->operator)
                                        المشغل: {{ $complianceSafety->operator->name }} | 
                                    @endif
                                    التاريخ: {{ $complianceSafety->last_inspection_date ? $complianceSafety->last_inspection_date->format('Y-m-d') : 'غير محدد' }}
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
                    </div>

                    <div class="card-body p-4">
                        <!-- Basic Information Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                المعلومات الأساسية
                            </h6>
                            <div class="row g-3">
                                @if($complianceSafety->operator)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">المشغل</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-building text-info me-2"></i>
                                            <span>{{ $complianceSafety->operator->name }}</span>
                                            @if($complianceSafety->operator->unit_number)
                                                <span class="badge bg-secondary ms-2">{{ $complianceSafety->operator->unit_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($complianceSafety->last_inspection_date)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">تاريخ آخر تفتيش</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar3 text-success me-2"></i>
                                            <span>{{ $complianceSafety->last_inspection_date->format('Y-m-d') }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if($complianceSafety->inspection_authority)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">جهة التفتيش</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-building-check text-primary me-2"></i>
                                            <span>{{ $complianceSafety->inspection_authority }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if($complianceSafety->inspection_result)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">نتيجة التفتيش</label>
                                        <div>
                                            @php
                                                $resultBadge = match(strtolower($complianceSafety->inspection_result)) {
                                                    'passed', 'pass', 'ناجح', 'ممتثل' => 'success',
                                                    'failed', 'fail', 'فشل', 'غير متوافق' => 'danger',
                                                    'conditional', 'مشروط' => 'warning',
                                                    default => 'info'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $resultBadge }}">
                                                {{ $complianceSafety->inspection_result }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">حالة شهادة السلامة</label>
                                        <div>
                                            <span class="badge bg-{{ $complianceSafety->safetyCertificateStatusDetail?->getBadgeColor() ?? 'secondary' }}">
                                                {{ $complianceSafety->safetyCertificateStatusDetail?->label ?? '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($complianceSafety->creator)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-muted">
                                            <i class="bi bi-person-badge text-success me-1"></i>
                                            المستخدم الذي أنشأ السجل
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-semibold">{{ $complianceSafety->creator->name }}</span>
                                        </div>
                                    </div>
                                    @endif
                            </div>
                        </div>

                        @if($complianceSafety->violations)
                            <hr class="my-4">

                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-journal-text text-primary me-2"></i>
                                    التفاصيل الإضافية
                                </h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-muted">
                                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                                            المخالفات
                                        </label>
                                        <div class="alert alert-danger mb-0">
                                            {{ $complianceSafety->violations }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($complianceSafety->created_at || $complianceSafety->updated_at)
                            <hr class="my-4">

                            <div class="mb-0">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-clock-history text-muted me-2"></i>
                                    معلومات النظام
                                </h6>
                                <div class="row g-3">
                                    @if($complianceSafety->created_at)
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-muted">تاريخ الإنشاء</label>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar-plus text-primary me-2"></i>
                                                <span>{{ $complianceSafety->created_at->format('Y-m-d H:i') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if($complianceSafety->updated_at)
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-muted">آخر تحديث</label>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar-check text-success me-2"></i>
                                                <span>{{ $complianceSafety->updated_at->format('Y-m-d H:i') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

