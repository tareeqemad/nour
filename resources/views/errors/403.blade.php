@extends('layouts.admin')

@section('title', 'غير مصرح لك بالدخول')

@php
    $breadcrumbTitle = 'غير مصرح لك بالدخول';
@endphp

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div class="error-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="error-icon">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                    </div>

                    <!-- Error Code -->
                    <h1 class="display-1 fw-bold text-danger mb-3">403</h1>
                    
                    <!-- Error Title -->
                    <h2 class="h3 fw-bold text-dark mb-3">غير مصرح لك بالدخول</h2>
                    
                    <!-- Error Message -->
                    <p class="text-muted mb-4 lead">
                        عذراً، لا تملك الصلاحيات اللازمة للوصول إلى هذه الصفحة.
                    </p>
                    
                    <p class="text-muted mb-4">
                        إذا كنت تعتقد أن هذا خطأ، يرجى التواصل مع مدير النظام.
                    </p>

                    <!-- Actions -->
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        @auth
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-house-door me-2"></i>
                                العودة للوحة التحكم
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                تسجيل الدخول
                            </a>
                        @endauth
                        
                        <button onclick="window.history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-right me-2"></i>
                            الرجوع للصفحة السابقة
                        </button>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-5 pt-4 border-top">
                        <div class="row text-start">
                            <div class="col-md-6 mb-3">
                                <h6 class="fw-semibold mb-2">
                                    <i class="bi bi-info-circle text-primary me-2"></i>
                                    ما الذي حدث؟
                                </h6>
                                <p class="text-muted small mb-0">
                                    تم رفض طلب الوصول لأنك لا تملك الصلاحيات المطلوبة لعرض هذه الصفحة أو تنفيذ هذا الإجراء.
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="fw-semibold mb-2">
                                    <i class="bi bi-question-circle text-info me-2"></i>
                                    كيف يمكن حل المشكلة؟
                                </h6>
                                <p class="text-muted small mb-0">
                                    إذا كنت تحتاج للوصول إلى هذه الصفحة، يرجى التواصل مع مدير النظام لطلب الصلاحيات اللازمة.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .error-icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 150px;
        height: 150px;
        margin: 0 auto;
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
        border-radius: 50%;
        animation: pulse 2s ease-in-out infinite;
    }

    .error-icon {
        color: #dc3545;
        width: 120px;
        height: 120px;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
    }

    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .display-1 {
        font-size: 6rem;
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .btn-lg {
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #004494 0%, #002d61 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 86, 179, 0.3);
    }

    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.2);
    }

    .border-top {
        border-color: #dee2e6 !important;
    }

    @media (max-width: 768px) {
        .display-1 {
            font-size: 4rem;
        }

        .error-icon-wrapper {
            width: 120px;
            height: 120px;
        }

        .error-icon {
            width: 90px;
            height: 90px;
        }

        .btn-lg {
            padding: 0.625rem 1.5rem;
            font-size: 0.95rem;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 2rem 1.5rem !important;
        }

        .display-1 {
            font-size: 3rem;
        }

        .h3 {
            font-size: 1.5rem;
        }

        .lead {
            font-size: 1rem;
        }
    }
</style>
@endpush

