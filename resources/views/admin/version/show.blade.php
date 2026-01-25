@extends('layouts.admin')

@section('title', 'تفاصيل الإصدار ' . $version->version)

@php
    $breadcrumbTitle = 'تفاصيل الإصدار';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-box-seam me-2"></i>
                            تفاصيل الإصدار v{{ $version->version }}
                        </h5>
                        <div class="general-subtitle">
                            {{ $version->title }}
                        </div>
                    </div>
                    <div class="general-card-actions">
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.versions.edit', $version) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i>
                                تعديل
                            </a>
                        @endif
                        <a href="{{ route('admin.changelog') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>
                            العودة للسجل
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- بطاقة معلومات الإصدار --}}
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-box text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="text-muted mb-1">رقم الإصدار</h6>
                                    <h4 class="fw-bold text-primary">v{{ $version->version }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-tag text-info mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="text-muted mb-1">نوع الإصدار</h6>
                                    <span class="badge {{ $version->getTypeBadgeClass() }} fs-6">
                                        {{ $version->getTypeLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-calendar3 text-success mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="text-muted mb-1">تاريخ الإصدار</h6>
                                    <h5 class="fw-bold">{{ $version->release_date->format('Y/m/d') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-check-circle text-{{ $version->is_current ? 'success' : 'secondary' }} mb-2" style="font-size: 2rem;"></i>
                                    <h6 class="text-muted mb-1">الحالة</h6>
                                    @if($version->is_current)
                                        <span class="badge bg-success fs-6">الإصدار الحالي</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">إصدار سابق</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الوصف --}}
                    @if($version->description)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-file-text text-primary me-2"></i>
                                    وصف الإصدار
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $version->description }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- سجل التغييرات --}}
                    @php $changes = $version->getCategorizedChanges(); @endphp
                    
                    @if(!empty($changes['features']) || !empty($changes['fixes']) || !empty($changes['improvements']) || !empty($changes['security']))
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-list-check text-primary me-2"></i>
                                    سجل التغييرات
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    {{-- الميزات الجديدة --}}
                                    @if(!empty($changes['features']))
                                        <div class="col-md-6">
                                            <div class="p-3 rounded" style="background: #d4edda;">
                                                <h6 class="fw-bold text-success mb-3">
                                                    <i class="bi bi-stars me-1"></i>
                                                    ميزات جديدة ({{ count($changes['features']) }})
                                                </h6>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($changes['features'] as $feature)
                                                        <li class="mb-2 d-flex">
                                                            <i class="bi bi-plus-circle-fill text-success me-2 mt-1"></i>
                                                            <span>{{ $feature }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- الإصلاحات --}}
                                    @if(!empty($changes['fixes']))
                                        <div class="col-md-6">
                                            <div class="p-3 rounded" style="background: #f8d7da;">
                                                <h6 class="fw-bold text-danger mb-3">
                                                    <i class="bi bi-bug me-1"></i>
                                                    إصلاحات ({{ count($changes['fixes']) }})
                                                </h6>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($changes['fixes'] as $fix)
                                                        <li class="mb-2 d-flex">
                                                            <i class="bi bi-check-circle-fill text-danger me-2 mt-1"></i>
                                                            <span>{{ $fix }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- التحسينات --}}
                                    @if(!empty($changes['improvements']))
                                        <div class="col-md-6">
                                            <div class="p-3 rounded" style="background: #cce5ff;">
                                                <h6 class="fw-bold text-info mb-3">
                                                    <i class="bi bi-arrow-up-circle me-1"></i>
                                                    تحسينات ({{ count($changes['improvements']) }})
                                                </h6>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($changes['improvements'] as $improvement)
                                                        <li class="mb-2 d-flex">
                                                            <i class="bi bi-arrow-up-circle-fill text-info me-2 mt-1"></i>
                                                            <span>{{ $improvement }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- الأمان --}}
                                    @if(!empty($changes['security']))
                                        <div class="col-md-6">
                                            <div class="p-3 rounded" style="background: #fff3cd;">
                                                <h6 class="fw-bold text-warning mb-3">
                                                    <i class="bi bi-shield-check me-1"></i>
                                                    تحديثات أمنية ({{ count($changes['security']) }})
                                                </h6>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($changes['security'] as $security)
                                                        <li class="mb-2 d-flex">
                                                            <i class="bi bi-shield-fill-check text-warning me-2 mt-1"></i>
                                                            <span>{{ $security }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                            <p class="mt-2">لا توجد تفاصيل تغييرات مسجلة لهذا الإصدار</p>
                        </div>
                    @endif

                    {{-- معلومات إضافية --}}
                    <div class="mt-4 text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        @if($version->releasedBy)
                            تم إصدار هذا الإصدار بواسطة: <strong>{{ $version->releasedBy->name }}</strong>
                        @endif
                        | تاريخ الإنشاء: {{ $version->created_at->format('Y/m/d H:i') }}
                        @if($version->updated_at != $version->created_at)
                            | آخر تحديث: {{ $version->updated_at->format('Y/m/d H:i') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
