<aside class="app-sidebar sticky" id="sidebar">
    <div class="main-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="header-logo">
            @php
                $logo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/rased_logo.png');
                $logoUrl = str_starts_with($logo, 'http') ? $logo : asset($logo);
            @endphp
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'نور') }}" class="desktop-logo">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'نور') }}" class="toggle-logo">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'نور') }}" class="desktop-dark">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'نور') }}" class="toggle-dark">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'نور') }}" class="desktop-white">
            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'نور') }}" class="toggle-white">
        </a>
    </div>

    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">

            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>

            @php
                $u = auth()->user();
                $isActive = fn($routes) => request()->routeIs($routes) ? 'active' : '';
                $isOpen   = fn($routes) => request()->routeIs($routes) ? 'open'   : '';
                $show     = fn($routes) => request()->routeIs($routes) ? 'display:block' : '';
            @endphp

            <ul class="main-menu">

                {{-- لوحة التحكم --}}
                <li class="slide {{ $isActive('admin.dashboard') }}">
                    <a href="{{ route('admin.dashboard') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M3 13h1v7c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-7h1a1 1 0 0 0 .707-1.707l-9-9a.999.999 0 0 0-1.414 0l-9 9A1 1 0 0 0 3 13zm7 7v-5h4v5h-4zm2-15.586 6 6V15l.001 5H16v-5c0-1.103-.897-2-2-2h-4c-1.103 0-2 .897-2 2v5H6v-9.586l6-6z"/></svg>
                        <span class="side-menu__label">لوحة التحكم</span>
                    </a>
                </li>

                {{-- الدليل الإرشادي --}}
                <li class="slide {{ $isActive('admin.guide.*') }}">
                    <a href="{{ route('admin.guide.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <span class="side-menu__label">الدليل الإرشادي</span>
                    </a>
                </li>

                {{-- استدعاء الـ partials حسب نوع المستخدم --}}
                @if($u->isSuperAdmin() || $u->isAdmin())
                    @include('admin.partials.sidebar-admin')
                @elseif($u->isEnergyAuthority())
                    @include('admin.partials.sidebar-energy-authority')
                @elseif($u->isCompanyOwner() || $u->isEmployee())
                    @include('admin.partials.sidebar-operator')
                @elseif($u->isTechnician())
                    @include('admin.partials.sidebar-technician')
                @elseif($u->isCivilDefense())
                    @include('admin.partials.sidebar-civil-defense')
                @endif

                {{-- التواصل والرسائل (لجميع المستخدمين) --}}
                @can('viewAny', App\Models\Message::class)
                    <li class="slide__category mt-3">
                        <span class="side-menu__label text-muted text-xs opacity-70">التواصل</span>
                    </li>

                    <li class="slide {{ $isActive('admin.messages.*') }}">
                        <a href="{{ route('admin.messages.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                            <span class="side-menu__label">الرسائل</span>
                        </a>
                    </li>
                @endcan

                {{-- الشكاوى والمقترحات (لجميع المستخدمين) --}}
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">خدمات</span>
                </li>

                <li class="slide {{ $isActive('admin.complaints-suggestions.*') }}">
                    <a href="{{ route('admin.complaints-suggestions.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                        <span class="side-menu__label">الشكاوى والمقترحات</span>
                    </a>
                </li>

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>

        </nav>
    </div>
</aside>
