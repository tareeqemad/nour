@extends('layouts.public')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'المقترحات والشكاوى - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/front/css/complaints.css') }}">
@endpush

@section('content')
<div class="complaints-public-page">
    <div class="container">
        <div class="complaints-header">
            <h1>المقترحات والشكاوى</h1>
            <p>شاركنا آراءك وشكاويك لتطوير الخدمات</p>
        </div>

        <div class="options-grid">
            <a href="{{ route('complaints-suggestions.create') }}?type=complaint" class="option-card">
                <div class="option-icon complaint-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <h3 class="option-title">إرسال شكوى</h3>
                <p class="option-description">
                    هل واجهت مشكلة مع أحد المشغلين؟ أخبرنا بها وسنعمل على حلها
                </p>
            </a>

            <a href="{{ route('complaints-suggestions.create') }}?type=suggestion" class="option-card">
                <div class="option-icon suggestion-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 9V5a3 3 0 0 0-6 0v4"></path>
                        <rect x="2" y="9" width="20" height="12" rx="2" ry="2"></rect>
                        <circle cx="12" cy="15" r="1"></circle>
                    </svg>
                </div>
                <h3 class="option-title">إرسال اقتراح</h3>
                <p class="option-description">
                    لديك فكرة لتحسين الخدمات؟ نحن نستمع لأفكارك ومقترحاتك
                </p>
            </a>

            <a href="{{ route('complaints-suggestions.track') }}" class="option-card">
                <div class="option-icon track-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <h3 class="option-title">تتبع الشكوى</h3>
                <p class="option-description">
                    تريد معرفة حالة شكواك؟ أدخل رمز التتبع للاطلاع على آخر التحديثات
                </p>
            </a>
        </div>
    </div>
</div>
@endsection

