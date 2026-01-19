@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', $siteName . ' - منصة رقمية لإدارة سوق الطاقة')
@section('description', 'منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة')

@push('meta')
    @php
        $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
    @endphp
    <meta name="keywords" content="{{ $siteName }}, منصة الطاقة, مولدات كهرباء, غزة, محافظات غزة, مشغلين, إدارة الطاقة">
    <meta name="author" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $siteName }} - منصة رقمية لإدارة سوق الطاقة">
    <meta property="og:description" content="منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url('/') }}">
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/front/css/home.css') }}">
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">منصة {{ $siteName ?? 'نور' }}</h1>
                <p class="hero-subtitle">
                    منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة
                    <br>
                    نوفر لك المعلومات والأدوات اللازمة للوصول إلى أفضل المشغلين
                </p>
                <div class="hero-actions">
                    <a href="{{ route('front.map') }}" class="btn btn-hero btn-hero-primary">
                        <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        استكشف الخريطة
                    </a>
                    <a href="{{ route('front.stats') }}" class="btn btn-hero btn-hero-secondary">
                        <svg style="width: 20px; height: 20px; display: inline-block; margin-left: 8px; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                        الإحصائيات
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-image-wrapper">
                    <div class="hero-image-pattern"></div>
                    <svg class="hero-image-main" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="screenGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#ffffff;stop-opacity:0.98" />
                                <stop offset="100%" style="stop-color:#f8fafc;stop-opacity:0.95" />
                            </linearGradient>
                            <linearGradient id="cardGradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#2563eb;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="cardGradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="cardGradient3" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
                            </linearGradient>
                            <filter id="glow">
                                <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        
                        <!-- Main Dashboard Screen -->
                        <rect x="30" y="40" width="440" height="320" rx="20" fill="url(#screenGradient)" stroke="rgba(59, 130, 246, 0.2)" stroke-width="2"/>
                        <rect x="50" y="60" width="400" height="280" rx="12" fill="rgba(255,255,255,0.95)"/>
                        
                        <!-- Header Bar -->
                        <rect x="50" y="60" width="400" height="50" rx="12" fill="linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)"/>
                        <rect x="70" y="75" width="120" height="20" rx="4" fill="rgba(59, 130, 246, 0.1)"/>
                        <circle cx="200" cy="85" r="6" fill="#3b82f6"/>
                        <circle cx="220" cy="85" r="6" fill="#10b981"/>
                        <circle cx="240" cy="85" r="6" fill="#f59e0b"/>
                        
                        <!-- Map Section (Left Side) -->
                        <rect x="70" y="130" width="180" height="180" rx="10" fill="rgba(241, 245, 249, 0.8)" stroke="rgba(59, 130, 246, 0.15)" stroke-width="1.5"/>
                        
                        <!-- Gaza Strip Map Outline -->
                        <path d="M 90 200 Q 120 170 150 185 T 200 200 T 230 210" 
                              fill="rgba(59, 130, 246, 0.1)" 
                              stroke="rgba(59, 130, 246, 0.4)" 
                              stroke-width="2.5" 
                              stroke-linejoin="round"/>
                        
                        <!-- Map Markers -->
                        <circle cx="120" cy="190" r="6" fill="#3b82f6" filter="url(#glow)"/>
                        <circle cx="160" cy="200" r="6" fill="#10b981" filter="url(#glow)"/>
                        <circle cx="200" cy="210" r="6" fill="#f59e0b" filter="url(#glow)"/>
                        
                        <!-- Connection Lines -->
                        <path d="M 120 190 L 160 200 L 200 210" stroke="rgba(59, 130, 246, 0.3)" stroke-width="1.5" stroke-dasharray="4,2"/>
                        
                        <!-- Map Title -->
                        <rect x="90" y="140" width="140" height="12" rx="2" fill="rgba(59, 130, 246, 0.15)"/>
                        
                        <!-- Statistics Cards (Right Side) -->
                        <!-- Card 1: Operators -->
                        <rect x="270" y="130" width="160" height="50" rx="8" fill="url(#cardGradient1)" opacity="0.95"/>
                        <rect x="280" y="140" width="30" height="30" rx="6" fill="rgba(255,255,255,0.3)"/>
                        <rect x="320" y="145" width="100" height="8" rx="2" fill="rgba(255,255,255,0.9)"/>
                        <rect x="320" y="158" width="80" height="6" rx="2" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Card 2: Generators -->
                        <rect x="270" y="190" width="160" height="50" rx="8" fill="url(#cardGradient2)" opacity="0.95"/>
                        <rect x="280" y="200" width="30" height="30" rx="6" fill="rgba(255,255,255,0.3)"/>
                        <rect x="320" y="205" width="100" height="8" rx="2" fill="rgba(255,255,255,0.9)"/>
                        <rect x="320" y="218" width="70" height="6" rx="2" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Card 3: Capacity -->
                        <rect x="270" y="250" width="160" height="50" rx="8" fill="url(#cardGradient3)" opacity="0.95"/>
                        <rect x="280" y="260" width="30" height="30" rx="6" fill="rgba(255,255,255,0.3)"/>
                        <rect x="320" y="265" width="100" height="8" rx="2" fill="rgba(255,255,255,0.9)"/>
                        <rect x="320" y="278" width="90" height="6" rx="2" fill="rgba(255,255,255,0.6)"/>
                        
                        <!-- Chart/Graph Section (Bottom) -->
                        <rect x="70" y="320" width="360" height="15" rx="4" fill="rgba(59, 130, 246, 0.08)"/>
                        <rect x="80" y="325" width="8" height="5" rx="2" fill="#3b82f6"/>
                        <rect x="100" y="323" width="8" height="7" rx="2" fill="#10b981"/>
                        <rect x="120" y="324" width="8" height="6" rx="2" fill="#3b82f6"/>
                        <rect x="140" y="322" width="8" height="8" rx="2" fill="#f59e0b"/>
                        <rect x="160" y="325" width="8" height="5" rx="2" fill="#3b82f6"/>
                        <rect x="180" y="323" width="8" height="7" rx="2" fill="#10b981"/>
                        <rect x="200" y="321" width="8" height="9" rx="2" fill="#8b5cf6"/>
                        <rect x="220" y="324" width="8" height="6" rx="2" fill="#3b82f6"/>
                        <rect x="240" y="322" width="8" height="8" rx="2" fill="#10b981"/>
                        <rect x="260" y="325" width="8" height="5" rx="2" fill="#3b82f6"/>
                        
                        <!-- Floating Elements (Icons) -->
                        <circle cx="420" cy="200" r="20" fill="rgba(59, 130, 246, 0.1)" stroke="rgba(59, 130, 246, 0.3)" stroke-width="1.5"/>
                        <path d="M 415 195 L 425 200 L 415 205 Z" fill="#3b82f6" opacity="0.8"/>
                        
                        <circle cx="420" cy="250" r="20" fill="rgba(16, 185, 129, 0.1)" stroke="rgba(16, 185, 129, 0.3)" stroke-width="1.5"/>
                        <rect x="412" y="242" width="16" height="16" rx="2" fill="#10b981" opacity="0.8"/>
                        
                        <!-- Decorative Elements -->
                        <circle cx="460" cy="100" r="3" fill="#3b82f6" opacity="0.4"/>
                        <circle cx="475" cy="110" r="2" fill="#10b981" opacity="0.4"/>
                        <circle cx="470" cy="125" r="2.5" fill="#f59e0b" opacity="0.4"/>
                        
                        <!-- Notification Badge -->
                        <circle cx="470" cy="85" r="8" fill="#ef4444"/>
                        <text x="470" y="90" text-anchor="middle" fill="white" font-size="10" font-weight="bold">3</text>
                        
                        <!-- Generator Icons on Map -->
                        <!-- Generator 1 -->
                        <g transform="translate(110, 180)">
                            <rect x="-8" y="-8" width="16" height="16" rx="3" fill="rgba(59, 130, 246, 0.15)" stroke="#3b82f6" stroke-width="1.5"/>
                            <circle cx="0" cy="-2" r="3" fill="#3b82f6"/>
                            <rect x="-4" y="2" width="8" height="4" rx="1" fill="#3b82f6"/>
                            <line x1="-6" y1="6" x2="6" y2="6" stroke="#3b82f6" stroke-width="1"/>
                        </g>
                        
                        <!-- Generator 2 -->
                        <g transform="translate(150, 190)">
                            <rect x="-8" y="-8" width="16" height="16" rx="3" fill="rgba(16, 185, 129, 0.15)" stroke="#10b981" stroke-width="1.5"/>
                            <circle cx="0" cy="-2" r="3" fill="#10b981"/>
                            <rect x="-4" y="2" width="8" height="4" rx="1" fill="#10b981"/>
                            <line x1="-6" y1="6" x2="6" y2="6" stroke="#10b981" stroke-width="1"/>
                        </g>
                        
                        <!-- Generator 3 -->
                        <g transform="translate(190, 200)">
                            <rect x="-8" y="-8" width="16" height="16" rx="3" fill="rgba(245, 158, 11, 0.15)" stroke="#f59e0b" stroke-width="1.5"/>
                            <circle cx="0" cy="-2" r="3" fill="#f59e0b"/>
                            <rect x="-4" y="2" width="8" height="4" rx="1" fill="#f59e0b"/>
                            <line x1="-6" y1="6" x2="6" y2="6" stroke="#f59e0b" stroke-width="1"/>
                        </g>
                        
                        <!-- Power Grid Lines -->
                        <!-- Horizontal Power Lines -->
                        <line x1="70" y1="200" x2="250" y2="200" stroke="rgba(59, 130, 246, 0.4)" stroke-width="2" stroke-dasharray="3,2"/>
                        <line x1="70" y1="220" x2="250" y2="220" stroke="rgba(16, 185, 129, 0.4)" stroke-width="2" stroke-dasharray="3,2"/>
                        <line x1="70" y1="240" x2="250" y2="240" stroke="rgba(245, 158, 11, 0.4)" stroke-width="2" stroke-dasharray="3,2"/>
                        
                        <!-- Vertical Power Lines -->
                        <line x1="120" y1="130" x2="120" y2="310" stroke="rgba(59, 130, 246, 0.3)" stroke-width="1.5" stroke-dasharray="2,3"/>
                        <line x1="160" y1="130" x2="160" y2="310" stroke="rgba(16, 185, 129, 0.3)" stroke-width="1.5" stroke-dasharray="2,3"/>
                        <line x1="200" y1="130" x2="200" y2="310" stroke="rgba(245, 158, 11, 0.3)" stroke-width="1.5" stroke-dasharray="2,3"/>
                        
                        <!-- Power Transmission Towers (Small) -->
                        <g transform="translate(90, 280)">
                            <line x1="0" y1="0" x2="0" y2="-15" stroke="#64748b" stroke-width="2"/>
                            <line x1="-5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="-3" y1="-10" x2="3" y2="-10" stroke="#64748b" stroke-width="1"/>
                        </g>
                        
                        <g transform="translate(130, 285)">
                            <line x1="0" y1="0" x2="0" y2="-15" stroke="#64748b" stroke-width="2"/>
                            <line x1="-5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="-3" y1="-10" x2="3" y2="-10" stroke="#64748b" stroke-width="1"/>
                        </g>
                        
                        <g transform="translate(170, 290)">
                            <line x1="0" y1="0" x2="0" y2="-15" stroke="#64748b" stroke-width="2"/>
                            <line x1="-5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="5" y1="-5" x2="0" y2="-15" stroke="#64748b" stroke-width="1.5"/>
                            <line x1="-3" y1="-10" x2="3" y2="-10" stroke="#64748b" stroke-width="1"/>
                        </g>
                        
                        <!-- Power Lines Connecting Towers -->
                        <path d="M 90 280 Q 110 275 130 285 Q 150 290 170 290" 
                              stroke="rgba(59, 130, 246, 0.5)" 
                              stroke-width="2" 
                              fill="none" 
                              stroke-linecap="round"/>
                        
                        <!-- Lightning/Energy Symbols -->
                        <g transform="translate(140, 160)">
                            <path d="M 0 -8 L -6 0 L 0 0 L 6 8 L 0 0 L 6 0 Z" fill="#fbbf24" opacity="0.8"/>
                        </g>
                        
                        <g transform="translate(180, 170)">
                            <path d="M 0 -8 L -6 0 L 0 0 L 6 8 L 0 0 L 6 0 Z" fill="#10b981" opacity="0.8"/>
                        </g>
                        
                        <!-- Power Station Icon (Bottom Left) -->
                        <g transform="translate(100, 300)">
                            <rect x="-12" y="-12" width="24" height="20" rx="3" fill="rgba(59, 130, 246, 0.2)" stroke="#3b82f6" stroke-width="2"/>
                            <rect x="-8" y="-8" width="16" height="12" rx="2" fill="rgba(255,255,255,0.9)"/>
                            <circle cx="-4" cy="-2" r="2" fill="#3b82f6"/>
                            <circle cx="4" cy="-2" r="2" fill="#10b981"/>
                            <rect x="-6" y="4" width="12" height="3" rx="1" fill="#3b82f6"/>
                            <line x1="0" y1="-12" x2="0" y2="-18" stroke="#3b82f6" stroke-width="2"/>
                            <circle cx="0" cy="-20" r="3" fill="#3b82f6" opacity="0.6"/>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title">الإحصائيات</h2>
            <p class="section-subtitle">إحصائيات شاملة عن المشغلين والمولدات في محافظات غزة</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-value">{{ number_format($stats['total_operators']) }}</div>
                <div class="stat-label">مشغل نشط</div>
            </div>

            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="stat-value">{{ number_format($stats['total_generators']) }}</div>
                <div class="stat-label">مولد مسجل</div>
            </div>

            <div class="stat-card animate-on-scroll">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                    </svg>
                </div>
                <div class="stat-value">{{ number_format($stats['total_capacity'] / 1000, 1) }}K</div>
                <div class="stat-label">كيلو فولت أمبير (KVA)</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">مميزات المنصة</h2>
            <p class="section-subtitle">نوفر لك كل ما تحتاجه للوصول إلى أفضل الخدمات</p>
            
            <div class="features-grid">
                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3 class="feature-title">خريطة تفاعلية</h3>
                    <p class="feature-text">
                        استكشف المشغلين على خريطة تفاعلية سهلة الاستخدام مع معلومات كاملة عن كل مشغل
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">إحصائيات شاملة</h3>
                    <p class="feature-text">
                        احصل على إحصائيات مفصلة عن المشغلين والمولدات في جميع محافظات غزة
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            <line x1="9" y1="10" x2="15" y2="10"></line>
                            <line x1="12" y1="7" x2="12" y2="13"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">شكاوي ومقترحات</h3>
                    <p class="feature-text">
                        أرسل شكاويك ومقترحاتك بسهولة واطلع على حالة متابعة شكاواك
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">معلومات الاتصال</h3>
                    <p class="feature-text">
                        احصل على معلومات الاتصال الكاملة لكل مشغل بسهولة وسرعة
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3 class="feature-title">بيانات موثوقة</h3>
                    <p class="feature-text">
                        جميع البيانات محدثة وموثوقة ومتابعة بشكل مستمر لضمان دقتها
                    </p>
                </div>

                <div class="feature-card animate-on-scroll">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3 class="feature-title">سهل الاستخدام</h3>
                    <p class="feature-text">
                        واجهة بسيطة وسهلة الاستخدام تعمل على جميع الأجهزة والهواتف الذكية
                    </p>
                </div>
            </div>
        </div>
    </section>

@endsection
