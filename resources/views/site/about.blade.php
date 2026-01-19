@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', 'من نحن - ' . $siteName)
@section('description', 'تعرف على منصة ' . $siteName . ' ورسالتنا وأهدافنا')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/front/css/about.css') }}">
@endpush

@section('content')
<div class="about-page">
    <div class="container">
        <div class="about-header">
            <h1>من نحن</h1>
            <p>منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة</p>
        </div>

        <!-- About Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                من نحن
            </h2>
            <p>
                {{ $siteName ?? 'نور' }} هي منصة رقمية متطورة تهدف إلى تنظيم وإدارة سوق الطاقة في محافظات غزة بشكل احترافي وشامل. 
                نقدم خدمات متكاملة تربط بين المواطنين والمشغلين لتسهيل الوصول إلى أفضل الخدمات.
            </p>
            <p>
                نسعى جاهدين لتوفير بيئة رقمية متقدمة تسهل عملية إدارة وتنظيم سوق الطاقة، مع ضمان الشفافية 
                والموثوقية في جميع البيانات المقدمة.
            </p>
        </div>

        <!-- Mission Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                رسالتنا
            </h2>
            <p>
                رسالتنا هي توفير منصة رقمية شاملة وموثوقة تسهل التواصل بين المواطنين والمشغلين، 
                وتعزز الشفافية والكفاءة في إدارة سوق الطاقة.
            </p>
            <ul class="features-list">
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">تسهيل الوصول إلى المعلومات والخدمات</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">ضمان دقة وموثوقية البيانات</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">تعزيز التواصل والشفافية</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text">تطوير وتحسين الخدمات باستمرار</div>
                </li>
            </ul>
        </div>

        <!-- Goals Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                أهدافنا
            </h2>
            <div class="goals-grid">
                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3 class="goal-title">الشمولية</h3>
                    <p class="goal-text">تغطية جميع محافظات غزة والمشغلين النشطين</p>
                </div>

                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3 class="goal-title">الموثوقية</h3>
                    <p class="goal-text">ضمان دقة وموثوقية جميع البيانات المقدمة</p>
                </div>

                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3 class="goal-title">سهولة الاستخدام</h3>
                    <p class="goal-text">واجهة بسيطة وسهلة الاستخدام لجميع المستخدمين</p>
                </div>

                <div class="goal-card">
                    <div class="goal-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="goal-title">التواصل</h3>
                    <p class="goal-text">تسهيل التواصل بين المواطنين والمشغلين</p>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="about-section animate-on-scroll">
            <h2>
                <div class="about-section-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                خدماتنا
            </h2>
            <p>نوفر مجموعة واسعة من الخدمات الرقمية التي تسهل إدارة وتنظيم سوق الطاقة:</p>
            <ul class="features-list">
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>خريطة تفاعلية:</strong> استكشف مواقع المشغلين على خريطة تفاعلية سهلة الاستخدام</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>إحصائيات شاملة:</strong> احصل على إحصائيات مفصلة عن المشغلين والمولدات</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>الشكاوي والمقترحات:</strong> أرسل شكاويك ومقترحاتك بسهولة واطلع على متابعة شكاواك</div>
                </li>
                <li>
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="feature-text"><strong>معلومات الاتصال:</strong> احصل على معلومات الاتصال الكاملة لكل مشغل</div>
                </li>
            </ul>
        </div>

        <!-- CTA -->
        <div class="text-center" style="margin-top: 3rem;">
            <a href="{{ route('front.map') }}" class="btn btn-primary btn-lg" style="font-size: 1.1rem; padding: 1rem 2.5rem;">
                <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                ابدأ الاستكشاف
            </a>
        </div>
    </div>
</div>
@endsection
