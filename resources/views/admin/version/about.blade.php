@extends('layouts.admin')

@section('title', 'حول النظام')

@php
    $breadcrumbTitle = 'حول النظام';
    $siteName = \App\Models\Setting::get('site_name', 'نور');
@endphp

@section('content')
<div class="general-page">
    <div class="row g-3">
        {{-- معلومات النظام --}}
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-info-circle me-2"></i>
                            حول النظام
                        </h5>
                        <div class="general-subtitle">
                            معلومات عن منصة {{ $siteName }} والإصدار الحالي
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        {{-- بطاقة الإصدار الحالي --}}
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body text-white text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-box-seam" style="font-size: 4rem; opacity: 0.9;"></i>
                                    </div>
                                    <h2 class="fw-bold mb-2">{{ $siteName }}</h2>
                                    <div class="d-inline-block px-4 py-2 rounded-pill mb-3" style="background: rgba(255,255,255,0.2);">
                                        <span class="fs-4 fw-bold">v{{ $systemInfo['version'] }}</span>
                                    </div>
                                    @if($currentVersion)
                                        <p class="mb-0 opacity-75">
                                            {{ $currentVersion->title }}
                                        </p>
                                        <small class="opacity-50">
                                            تاريخ الإصدار: {{ $currentVersion->release_date->format('Y/m/d') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- معلومات تقنية --}}
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="bi bi-gear text-primary me-2"></i>
                                        المعلومات التقنية
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">
                                                <i class="bi bi-box me-2"></i>
                                                إصدار التطبيق
                                            </span>
                                            <span class="badge bg-primary">v{{ $systemInfo['version'] }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">
                                                <i class="bi bi-code-slash me-2"></i>
                                                Laravel
                                            </span>
                                            <span class="badge bg-danger">v{{ $systemInfo['laravel_version'] }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">
                                                <i class="bi bi-filetype-php me-2"></i>
                                                PHP
                                            </span>
                                            <span class="badge bg-info">v{{ $systemInfo['php_version'] }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">
                                                <i class="bi bi-globe me-2"></i>
                                                البيئة
                                            </span>
                                            <span class="badge {{ $systemInfo['environment'] === 'production' ? 'bg-success' : 'bg-warning' }}">
                                                {{ $systemInfo['environment'] }}
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">
                                                <i class="bi bi-clock me-2"></i>
                                                المنطقة الزمنية
                                            </span>
                                            <span class="badge bg-secondary">{{ $systemInfo['timezone'] }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted">
                                                <i class="bi bi-translate me-2"></i>
                                                اللغة
                                            </span>
                                            <span class="badge bg-secondary">{{ $systemInfo['locale'] }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- وصف النظام --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="bi bi-file-text text-primary me-2"></i>
                                        عن المنصة
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">
                                        <strong>{{ $siteName }}</strong> هي منصة رقمية شاملة لإدارة المولدات الكهربائية والمشغلين في فلسطين. 
                                        يوفر النظام إدارة كاملة لبيانات المشغلين، المولدات، سجلات التشغيل، الصيانة، الامتثال البيئي، والشكاوى والاقتراحات.
                                    </p>
                                    
                                    <h6 class="fw-bold mb-3">الميزات الرئيسية:</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <span>إدارة المستخدمين والصلاحيات المتقدمة</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <span>إدارة المشغلين ووحدات التوليد</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <span>إدارة المولدات والبيانات التقنية</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <span>سجلات التشغيل والصيانة</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <span>نظام الشكاوى والاقتراحات</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                <span>نظام المراسلات الداخلية</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- روابط سريعة --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <a href="{{ route('admin.changelog') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-journal-text me-2"></i>
                                    سجل التغييرات
                                </a>
                                <a href="{{ route('admin.guide.index') }}" class="btn btn-outline-info">
                                    <i class="bi bi-book me-2"></i>
                                    الدليل الإرشادي
                                </a>
                                @if(auth()->user()->isSuperAdmin())
                                    <a href="{{ route('admin.versions.index') }}" class="btn btn-outline-warning">
                                        <i class="bi bi-gear me-2"></i>
                                        إدارة الإصدارات
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
