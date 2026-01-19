<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = $siteName ?? \App\Models\Setting::get('site_name', 'نور');
        $favicon = \App\Models\Setting::get('site_favicon', 'assets/admin/images/brand-logos/favicon.ico');
    @endphp
    <title>@yield('title', $siteName . ' - منصة رقمية لإدارة سوق الطاقة')</title>
    <meta name="description" content="@yield('description', 'منصة رقمية متكاملة لتنظيم وإدارة سوق الطاقة في محافظات غزة')">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset($favicon) }}" type="image/x-icon">
    
    <!-- Tajawal Font from Admin Panel -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tajawal-font.css') }}" />
    
    <!-- Bootstrap 5 RTL -->
    <link href="{{ asset('assets/front/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    
    <!-- Icons from Admin (includes Bootstrap Icons) -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/icons.css') }}">
    
    <!-- Admin General Cards CSS (for filter-card) -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/general-cards.css') }}">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/navigation.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="public-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="{{ route('front.home') }}" class="brand-link">
                    @if(isset($logoUrl) && $logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName ?? 'نور' }}" class="brand-logo">
                    @else
                        <div class="brand-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                            </svg>
                        </div>
                    @endif
                    <span class="brand-text">{{ $siteName ?? 'نور' }}</span>
                </a>
            </div>
            
            <button class="nav-toggle" id="navToggle" aria-label="قائمة التنقل">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="{{ route('front.home') }}" class="nav-link {{ request()->routeIs('front.home') ? 'active' : '' }}">الرئيسية</a></li>
                <li><a href="{{ route('front.map') }}" class="nav-link {{ request()->routeIs('front.map') ? 'active' : '' }}">الخريطة</a></li>
                <li><a href="{{ route('front.stats') }}" class="nav-link {{ request()->routeIs('front.stats') ? 'active' : '' }}">الإحصائيات</a></li>
                <li><a href="{{ route('front.about') }}" class="nav-link {{ request()->routeIs('front.about') ? 'active' : '' }}">من نحن</a></li>
                <li><a href="{{ route('front.join') }}" class="nav-link {{ request()->routeIs('front.join') ? 'active' : '' }}">طلب الانضمام</a></li>
                <li><a href="{{ route('complaints-suggestions.index') }}" class="nav-link {{ request()->routeIs('complaints-suggestions.*') ? 'active' : '' }}">الشكاوي والمقترحات</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="public-main">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="public-footer">
        <div class="footer-container">
            <p class="footer-copyright">جميع الحقوق محفوظة © 2026 {{ $siteName ?? 'نور' }}.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/front/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Scripts -->
    <script src="{{ asset('assets/front/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
