@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'المقترحات والشكاوى - ' . $siteName)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/front/css/complaints.css') }}">
@endpush

@section('content')
<div class="complaints-page">
    <div class="complaints-container">
        <div class="complaints-header">
            <h1>المقترحات والشكاوى</h1>
            <p>نقدر ملاحظاتك ونسعى لتحسين خدماتنا</p>
        </div>

        <div>
            <div class="options-grid">
                <a href="{{ route('complaints-suggestions.create') }}" class="option-card">
                    <div class="option-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            <line x1="9" y1="10" x2="15" y2="10"></line>
                            <line x1="12" y1="7" x2="12" y2="13"></line>
                        </svg>
                    </div>
                    <div class="option-title">تقديم شكوى أو مقترح</div>
                    <div class="option-description">قدم شكواك أو اقتراحك وسنقوم بمراجعته والرد عليك في أقرب وقت</div>
                </a>

                <a href="{{ route('complaints-suggestions.track') }}" class="option-card">
                    <div class="option-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="option-title">متابعة طلب سابق</div>
                    <div class="option-description">تابع حالة شكواك أو مقترحك السابق باستخدام رمز التتبع</div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

