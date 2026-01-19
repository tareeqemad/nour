@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'الإحصائيات - ' . $siteName)
@section('description', 'إحصائيات شاملة عن المشغلين والمولدات في محافظات غزة')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/front/css/stats.css') }}">
@endpush

@section('content')
<div class="stats-page">
    <div class="container">
        <div class="stats-header">
            <h1>الإحصائيات الشاملة</h1>
            <p>نظرة شاملة على بيانات المشغلين والمولدات في جميع محافظات غزة</p>
        </div>

        <!-- Main Statistics -->
        <div class="main-stats-grid">
            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['total_operators']) }}</div>
                <div class="main-stat-label">مشغل نشط</div>
            </div>

            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['total_generators']) }}</div>
                <div class="main-stat-label">مولد مسجل</div>
            </div>

            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['total_capacity'] / 1000, 1) }}K</div>
                <div class="main-stat-label">كيلو فولت أمبير (KVA)</div>
            </div>

            <div class="main-stat-card animate-on-scroll">
                <div class="main-stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div class="main-stat-value">{{ number_format($stats['active_generators']) }}</div>
                <div class="main-stat-label">مولد نشط</div>
            </div>
        </div>

        <!-- Governorate Statistics -->
        @if($stats['operators_by_governorate']->count() > 0)
        <div class="governorate-stats-section animate-on-scroll">
            <h2 class="governorate-stats-title">توزيع المشغلين حسب المحافظة</h2>
            <div class="governorate-stats-grid">
                @foreach($stats['operators_by_governorate'] as $governorate => $count)
                <div class="governorate-stat-card">
                    <div class="governorate-stat-label">{{ $governorate }}</div>
                    <div class="governorate-stat-value">{{ number_format($count) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Information Section -->
        <div class="info-section animate-on-scroll">
            <h2>معلومات مهمة</h2>
            <ul class="info-list">
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="info-text">جميع البيانات محدثة ومتابعة بشكل مستمر</div>
                </li>
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <div class="info-text">البيانات موثوقة وتم التحقق منها</div>
                </li>
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="info-text">تحديث البيانات يتم بشكل دوري ومنتظم</div>
                </li>
                <li>
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div class="info-text">يمكنك التواصل مع المشغلين مباشرة من خلال المعلومات المتوفرة</div>
                </li>
            </ul>
        </div>

        <!-- CTA -->
        <div class="text-center" style="margin-top: 3rem;">
            <a href="{{ route('front.map') }}" class="btn btn-primary btn-lg">
                <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                استكشف الخريطة
            </a>
        </div>
    </div>
</div>
@endsection
