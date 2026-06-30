{{--
    سايدبار منصة مضمون.

    يستخدم نفس أصناف (classes) سايدبار نور ⟵ نفس التصميم تماماً.
    مبرمج مضمون: أضف عناصر قائمتك داخل nav أدناه، واحرس كل عنصر بصلاحياته
    (مثال: @if($u->hasPermission('madmoun.policies.view')) ... @endif).
--}}
@php
    $u = auth()->user();
    $isActive = fn($routes) => request()->routeIs($routes) ? 'active' : '';
    $siteName = \App\Models\Setting::get('site_name', 'نور');
    $logo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
    $logoUrl = str_starts_with($logo, 'http') ? $logo : asset($logo);
@endphp

<aside class="app-sidebar sticky" id="sidebar">
    <div class="main-sidebar-header">
        <a href="{{ route('madmoun.dashboard') }}" class="header-logo">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="desktop-logo">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="toggle-logo">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="desktop-dark">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="toggle-dark">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="desktop-white">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="toggle-white">
        </a>
    </div>

    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path></svg>
            </div>

            <ul class="main-menu">
                {{-- ترويسة المنصة --}}
                <li class="slide__category"><span class="category-name">منصة مضمون</span></li>

                {{-- لوحة مضمون --}}
                <li class="slide {{ $isActive('madmoun.dashboard') }}">
                    <a href="{{ route('madmoun.dashboard') }}" class="side-menu__item">
                        <i class="side-menu__icon bi bi-shield-check"></i>
                        <span class="side-menu__label">الرئيسية</span>
                    </a>
                </li>

                {{-- ↓↓↓ عناصر قائمة مضمون تُضاف هنا (محروسة بصلاحيات madmoun.*) ↓↓↓ --}}

                {{-- مثال: بوالص الضمان --}}
                <li class="slide {{ $isActive('madmoun.policies.*') }}">
                    <a href="{{ route('madmoun.policies.index') }}" class="side-menu__item">
                        <i class="side-menu__icon bi bi-file-earmark-text"></i>
                        <span class="side-menu__label">بوالص الضمان</span>
                    </a>
                </li>


                {{-- العودة إلى منصة نور --}}
                <li class="slide__category"><span class="category-name">التنقّل</span></li>
                <li class="slide">
                    <a href="{{ route('admin.dashboard') }}" class="side-menu__item">
                        <i class="side-menu__icon bi bi-arrow-right-circle"></i>
                        <span class="side-menu__label">العودة إلى نور</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
