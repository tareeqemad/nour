<header class="app-header">

    <!-- Start::main-header-container -->
    <div class="main-header-container container-fluid">

        <!-- Start::header-content-left -->
        <div class="header-content-left align-items-center">

            <!-- Start::header-element -->
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="{{ route('admin.dashboard') }}" class="header-logo">
                        @php
                            $logo = \App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png');
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
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link -->
                <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="main-header-center ms-3 d-sm-none d-md-none d-lg-block form-group">
                <input class="form-control" placeholder="بحث..." type="search">
                <button class="btn"><i class="bi bi-search"></i></button>
            </div>
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-left -->

        <!-- Start::header-content-right -->
        <div class="header-content-right">

            <!-- Start::header-element -->
            <div class="header-element header-search d-block d-sm-none">
                <!-- Start::header-link -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown">
                    <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                </a>

                <ul class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                    <li>
                        <span class="dropdown-item d-flex align-items-center">
                            <span class="input-group">
                                <input type="text" class="form-control" placeholder="بحث..." aria-label="بحث..." aria-describedby="button-addon2">
                                <button class="btn btn-primary" type="button" id="button-addon2">بحث</button>
                            </span>
                        </span>
                    </li>
                </ul>

                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element messages-dropdown">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="messagesDropdown" aria-expanded="false" title="الرسائل">
                    <i class="bi bi-envelope header-link-icon"></i>
                    <span class="badge bg-primary rounded-pill header-icon-badge pulse pulse-secondary" id="messages-icon-badge" style="display: none;">0</span>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <!-- Start::main-header-dropdown -->
                <div class="main-header-dropdown dropdown-menu dropdown-menu-end nour-dropdown-panel" data-popper-placement="none">
                    {{-- Header --}}
                    <div class="nour-dropdown-head">
                        <span class="nour-dropdown-title"><i class="bi bi-envelope me-1"></i> الرسائل</span>
                        <span class="nour-dropdown-badge" id="messages-summary"></span>
                    </div>
                    {{-- Loading --}}
                    <div id="messages-loading" class="nour-dropdown-empty" style="display:none;">
                        <div class="spinner-border spinner-border-sm" style="color:#24308F;" role="status"></div>
                        <p>جاري التحميل...</p>
                    </div>
                    {{-- List --}}
                    <ul class="list-unstyled mb-0 nour-dropdown-list" id="messages-list">
                        <li class="nour-dropdown-empty">
                            <i class="bi bi-envelope-open"></i>
                            <p>لا توجد رسائل</p>
                        </li>
                    </ul>
                    {{-- Footer --}}
                    <div class="nour-dropdown-foot">
                        <a href="{{ route('admin.messages.create') }}" class="nour-dropdown-btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> رسالة جديدة
                        </a>
                        <a href="{{ route('admin.messages.index') }}" class="nour-dropdown-link">
                            عرض الكل <i class="bi bi-arrow-left ms-1"></i>
                        </a>
                    </div>
                </div>
                <!-- End::main-header-dropdown -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element notifications-dropdown">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="notificationDropdown" aria-expanded="false">
                    <i class="bi bi-bell header-link-icon"></i>
                    <span class="badge bg-danger rounded-pill header-icon-badge pulse pulse-secondary" id="notification-icon-badge" style="display: none;">0</span>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <!-- Start::main-header-dropdown -->
                <div class="main-header-dropdown dropdown-menu dropdown-menu-end nour-dropdown-panel" data-popper-placement="none">
                    <div class="nour-dropdown-head">
                        <span class="nour-dropdown-title"><i class="bi bi-bell me-1"></i> الإشعارات</span>
                        <span class="nour-dropdown-badge" id="notification-count">0 غير مقروء</span>
                    </div>
                    <ul class="list-unstyled mb-0 nour-dropdown-list" id="notification-list">
                        <li class="nour-dropdown-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>لا توجد إشعارات</p>
                        </li>
                    </ul>
                    <div class="nour-dropdown-foot">
                        <a href="javascript:void(0);" class="nour-dropdown-link" id="mark-all-read" style="display: none;">
                            تعليم الكل كمقروء
                        </a>
                    </div>
                </div>
                <!-- End::main-header-dropdown -->
            </div>
            <!-- End::header-element -->


            <!-- Start::header-element -->
            <div class="header-element header-fullscreen">
                <!-- Start::header-link -->
                <a onclick="openFullscreen();" href="#" class="header-link">
                    <i class="bi bi-fullscreen full-screen-open header-link-icon"></i>
                    <i class="bi bi-fullscreen-exit full-screen-close header-link-icon d-none"></i>
                </a>
                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="#" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="me-sm-2 me-0">
                            <img src="{{ auth()->user()->avatar_url }}" alt="profile" width="32" height="32" class="rounded-circle shadow-sm">
                        </div>
                        <div class="d-xl-block d-none">
                            <p class="fw-semibold mb-0 lh-1">{{ auth()->user()->name }}</p>
                            <span class="op-7 fw-normal d-block fs-11">{{ auth()->user()->role_name }}</span>
                        </div>
                    </div>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <!-- Start::main-header-dropdown -->
                <div class="main-header-dropdown dropdown-menu dropdown-menu-end header-profile-dropdown" data-popper-placement="none">
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <img src="{{ auth()->user()->avatar_url }}" alt="profile" width="48" height="48" class="rounded-circle shadow-sm">
                            </div>
                            <div class="flex-grow-1">
                                <p class="fw-semibold mb-0">{{ auth()->user()->name }}</p>
                                <small class="text-muted">{{ auth()->user()->email }}</small>
                            </div>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <a href="{{ route('admin.profile.show') }}" class="dropdown-item">
                                <i class="bi bi-person me-2"></i>
                                الملف الشخصي
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item" onclick="lockScreen()">
                                <i class="bi bi-lock me-2"></i>
                                قفل الشاشة
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <!-- End::main-header-dropdown -->
            </div>
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-right -->

    </div>
    <!-- End::main-header-container -->

</header>

@push('scripts')
<script>
function lockScreen() {
    fetch('{{ route('admin.lock-screen.lock') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush
