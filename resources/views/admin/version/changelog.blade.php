@extends('layouts.admin')

@section('title', 'سجل التغييرات')

@php
    $breadcrumbTitle = 'سجل التغييرات';
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-journal-text me-2"></i>
                            سجل التغييرات
                        </h5>
                        <div class="general-subtitle">
                            تاريخ جميع التحديثات والتغييرات على المنصة
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if($versions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">لا توجد إصدارات مسجلة</h5>
                            <p class="text-muted">سيتم عرض سجل التغييرات هنا عند إضافة إصدارات جديدة</p>
                        </div>
                    @else
                        <div class="timeline-container">
                            @foreach($versions as $version)
                                <div class="timeline-item mb-4">
                                    <div class="card border-0 shadow-sm {{ $version->is_current ? 'border-start border-primary border-4' : '' }}">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="badge {{ $version->getTypeBadgeClass() }} fs-6">
                                                    v{{ $version->version }}
                                                </span>
                                                <h6 class="mb-0 fw-bold">{{ $version->title }}</h6>
                                                @if($version->is_current)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        الإصدار الحالي
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-2 text-muted small">
                                                <span class="badge bg-light text-dark">
                                                    {{ $version->getTypeLabel() }}
                                                </span>
                                                <span>
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    {{ $version->release_date->format('Y/m/d') }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body">
                                            @if($version->description)
                                                <p class="text-muted mb-3">{{ $version->description }}</p>
                                            @endif
                                            
                                            @php $changes = $version->getCategorizedChanges(); @endphp
                                            
                                            <div class="row g-3">
                                                {{-- الميزات الجديدة --}}
                                                @if(!empty($changes['features']))
                                                    <div class="col-md-6">
                                                        <div class="change-category">
                                                            <h6 class="fw-bold text-success mb-2">
                                                                <i class="bi bi-stars me-1"></i>
                                                                ميزات جديدة
                                                            </h6>
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach($changes['features'] as $feature)
                                                                    <li class="mb-1">
                                                                        <i class="bi bi-plus-circle text-success me-2"></i>
                                                                        {{ $feature }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- الإصلاحات --}}
                                                @if(!empty($changes['fixes']))
                                                    <div class="col-md-6">
                                                        <div class="change-category">
                                                            <h6 class="fw-bold text-danger mb-2">
                                                                <i class="bi bi-bug me-1"></i>
                                                                إصلاحات
                                                            </h6>
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach($changes['fixes'] as $fix)
                                                                    <li class="mb-1">
                                                                        <i class="bi bi-check-circle text-danger me-2"></i>
                                                                        {{ $fix }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- التحسينات --}}
                                                @if(!empty($changes['improvements']))
                                                    <div class="col-md-6">
                                                        <div class="change-category">
                                                            <h6 class="fw-bold text-info mb-2">
                                                                <i class="bi bi-arrow-up-circle me-1"></i>
                                                                تحسينات
                                                            </h6>
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach($changes['improvements'] as $improvement)
                                                                    <li class="mb-1">
                                                                        <i class="bi bi-arrow-up text-info me-2"></i>
                                                                        {{ $improvement }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                {{-- الأمان --}}
                                                @if(!empty($changes['security']))
                                                    <div class="col-md-6">
                                                        <div class="change-category">
                                                            <h6 class="fw-bold text-warning mb-2">
                                                                <i class="bi bi-shield-check me-1"></i>
                                                                أمان
                                                            </h6>
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach($changes['security'] as $security)
                                                                    <li class="mb-1">
                                                                        <i class="bi bi-shield text-warning me-2"></i>
                                                                        {{ $security }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $versions->links() }}
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
    .timeline-container {
        position: relative;
        padding-right: 20px;
    }
    
    .timeline-container::before {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--bs-primary) 0%, var(--bs-info) 100%);
        border-radius: 3px;
    }
    
    .timeline-item {
        position: relative;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        right: -26px;
        top: 20px;
        width: 12px;
        height: 12px;
        background: var(--bs-primary);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 3px var(--bs-primary);
    }
    
    .change-category {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
</style>
@endpush
