@extends('layouts.site')

@php
    $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
@endphp
@section('title', $siteName . ' - منصة رقمية لإدارة سوق الطاقة')
@section('description', 'منصة رقمية متكاملة لتنظيم ومراقبة سوق الطاقة — تربط المشغلين والمشتركين والجهات الرقابية')

@push('meta')
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

    {{-- ===== Hero Section ===== --}}
    <section class="hero">
        <div class="hero-grid"></div>
        <div class="hero-inner">
            <div class="hero-text">
                <h1 class="hero-title">منصة <span>{{ $siteName }}</span></h1>
                <p class="hero-desc">
                    منصة رقمية متكاملة لتنظيم ومراقبة سوق الطاقة في محافظات غزة —
                    تربط بين المشغلين والمشتركين والجهات الرقابية في مكان واحد.
                </p>
                <div class="hero-btns">
                    <a href="{{ route('front.map') }}" class="hero-btn hero-btn-primary">
                        <i class="bi bi-geo-alt-fill"></i>
                        استكشف الخريطة
                    </a>
                    <a href="{{ route('front.stats') }}" class="hero-btn hero-btn-secondary">
                        <i class="bi bi-bar-chart-fill"></i>
                        الإحصائيات
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-cards">
                    <div class="hero-card">
                        <div class="hero-card-icon blue"><i class="bi bi-building"></i></div>
                        <div class="hero-card-info">
                            <div class="hero-card-val">{{ number_format($stats['total_operators']) }}</div>
                            <div class="hero-card-label">مشغل نشط</div>
                        </div>
                    </div>
                    <div class="hero-card">
                        <div class="hero-card-icon green"><i class="bi bi-lightning-charge"></i></div>
                        <div class="hero-card-info">
                            <div class="hero-card-val">{{ number_format($stats['total_generators']) }}</div>
                            <div class="hero-card-label">مولد مسجل</div>
                        </div>
                    </div>
                    <div class="hero-card">
                        <div class="hero-card-icon amber"><i class="bi bi-activity"></i></div>
                        <div class="hero-card-info">
                            <div class="hero-card-val">{{ \App\Helpers\GeneralHelper::formatCompactNumber($stats['total_capacity'] ?? 0) }}</div>
                            <div class="hero-card-label">كيلو فولت أمبير (KVA)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Stats Section ===== --}}
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title">أرقام وإحصائيات</h2>
            <p class="section-subtitle">لمحة شاملة عن قطاع الطاقة في محافظات غزة</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon"><i class="bi bi-building"></i></div>
                <div class="stat-value">{{ number_format($stats['total_operators']) }}</div>
                <div class="stat-label">مشغل نشط</div>
            </div>
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon"><i class="bi bi-lightning-charge"></i></div>
                <div class="stat-value">{{ number_format($stats['total_generators']) }}</div>
                <div class="stat-label">مولد مسجل</div>
            </div>
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon"><i class="bi bi-activity"></i></div>
                <div class="stat-value">{{ \App\Helpers\GeneralHelper::formatCompactNumber($stats['total_capacity'] ?? 0) }}</div>
                <div class="stat-label">كيلو فولت أمبير</div>
            </div>
            <div class="stat-card animate-on-scroll">
                <div class="stat-icon"><i class="bi bi-diagram-3"></i></div>
                <div class="stat-value">{{ number_format($stats['total_generation_units'] ?? 0) }}</div>
                <div class="stat-label">وحدة توليد</div>
            </div>
        </div>
    </section>

    {{-- ===== Announcements Section ===== --}}
    @if(($announcements ?? collect())->isNotEmpty())
    <section class="announcements-section">
        <div class="container">
            <h2 class="section-title">الإعلانات</h2>
            <p class="section-subtitle">آخر التحديثات والإعلانات الرسمية من إدارة المنصة</p>
        </div>

        <div class="announcements-carousel" data-carousel data-count="{{ $announcements->count() }}">
            <button type="button" class="carousel-arrow carousel-arrow-prev" aria-label="السابق" data-carousel-prev>
                <i class="bi bi-chevron-right"></i>
            </button>

            <div class="announcements-track" data-carousel-track>
                @foreach($announcements as $ann)
                    <article class="announcement-card {{ $ann->is_featured ? 'is-featured' : '' }}" data-carousel-slide>
                        <div class="announcement-header">
                            <h3 class="announcement-title">{{ $ann->title }}</h3>
                            @if($ann->is_featured)
                                <span class="announcement-badge"><i class="bi bi-star-fill"></i> مميز</span>
                            @endif
                        </div>
                        <div class="announcement-body">
                            <div class="announcement-meta">
                                <i class="bi bi-calendar3"></i>
                                <span>{{ $ann->announcement_date?->translatedFormat('d M Y') }}</span>
                            </div>
                            <p class="announcement-text">{{ $ann->description }}</p>
                            <div class="announcement-footer">
                                <i class="bi bi-clock-history me-1"></i>
                                ساري من {{ $ann->start_date?->translatedFormat('d M Y') }}
                                حتى {{ $ann->end_date?->translatedFormat('d M Y') }}
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <button type="button" class="carousel-arrow carousel-arrow-next" aria-label="التالي" data-carousel-next>
                <i class="bi bi-chevron-left"></i>
            </button>

            @if($announcements->count() > 1)
                <div class="carousel-dots" data-carousel-dots>
                    @foreach($announcements as $i => $ann)
                        <button type="button" class="carousel-dot {{ $i === 0 ? 'is-active' : '' }}"
                                data-carousel-dot="{{ $i }}" aria-label="إعلان رقم {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- ===== Features Section ===== --}}
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">مميزات المنصة</h2>
            <p class="section-subtitle">كل ما تحتاجه للوصول إلى أفضل الخدمات</p>
        </div>

        <div class="features-grid">
            <div class="feature-card animate-on-scroll">
                <div class="feature-icon"><i class="bi bi-geo-alt"></i></div>
                <h3 class="feature-title">خريطة تفاعلية</h3>
                <p class="feature-text">استكشف المشغلين على خريطة تفاعلية مع معلومات كاملة عن كل مشغل ومواقع وحدات التوليد</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon"><i class="bi bi-bar-chart"></i></div>
                <h3 class="feature-title">إحصائيات شاملة</h3>
                <p class="feature-text">إحصائيات مفصلة عن المشغلين والمولدات والقدرات الإنتاجية في جميع المحافظات</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon"><i class="bi bi-chat-dots"></i></div>
                <h3 class="feature-title">شكاوي ومقترحات</h3>
                <p class="feature-text">أرسل شكاويك ومقترحاتك بسهولة وتابع حالتها أولاً بأول</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon"><i class="bi bi-telephone"></i></div>
                <h3 class="feature-title">معلومات الاتصال</h3>
                <p class="feature-text">معلومات الاتصال الكاملة لكل مشغل ووحدة توليد بسهولة وسرعة</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                <h3 class="feature-title">بيانات موثوقة</h3>
                <p class="feature-text">جميع البيانات محدثة وموثوقة ومتابعة بشكل مستمر لضمان دقتها</p>
            </div>

            <div class="feature-card animate-on-scroll">
                <div class="feature-icon"><i class="bi bi-phone"></i></div>
                <h3 class="feature-title">سهل الاستخدام</h3>
                <p class="feature-text">واجهة بسيطة وسهلة تعمل على جميع الأجهزة والهواتف الذكية</p>
            </div>
        </div>
    </section>

    {{-- ===== CTA Section ===== --}}
    <section class="cta-section">
        <h2 class="cta-title">مشغل طاقة أو مشترك؟</h2>
        <p class="cta-desc">انضم إلى منصة {{ $siteName }} — تابع خدماتك وأدِر عملياتك بكفاءة</p>
        <a href="{{ route('front.join') }}" class="cta-btn">
            <i class="bi bi-person-plus-fill"></i>
            طلب الانضمام
        </a>
    </section>

@endsection
