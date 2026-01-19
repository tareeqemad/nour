@php
    $siteName = \App\Models\Setting::get('site_name', 'نور');
    $favicon = \App\Models\Setting::get('site_favicon', 'assets/admin/images/brand-logos/favicon.ico');
    $primaryColor = \App\Models\Setting::get('primary_color', '#19228f');
    $darkColor = \App\Models\Setting::get('dark_color', '#3b4863');
    $headerColor = \App\Models\Setting::get('header_color', '#19228f');
    $menuColor = \App\Models\Setting::get('menu_color', '#F7F7F7');
    // استخدام localStorage إذا كان موجوداً، وإلا استخدام الإعدادات
    // سيتم تطبيق القيمة من localStorage عبر JavaScript
    $menuStyles = \App\Models\Setting::get('menu_styles', 'light');
    $headerStyles = \App\Models\Setting::get('header_styles', 'light');
    
    // Convert hex to RGB (format: --primary-rgb: 25, 34, 143;)
    $hex = ltrim($primaryColor, '#');
    // Handle 3-digit hex colors (e.g., #fff -> #ffffff)
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    // Ensure we have a valid 6-digit hex color
    if (strlen($hex) === 6 && ctype_xdigit($hex)) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $primaryRgb = "{$r}, {$g}, {$b}";
    } else {
        // Default fallback: #19228f -> 25, 34, 143
        $primaryRgb = "25, 34, 143";
    }
    
    // Convert dark color hex to RGB (format: --dark-rgb: 59, 72, 99;)
    $darkHex = ltrim($darkColor, '#');
    if (strlen($darkHex) === 3) {
        $darkHex = $darkHex[0] . $darkHex[0] . $darkHex[1] . $darkHex[1] . $darkHex[2] . $darkHex[2];
    }
    if (strlen($darkHex) === 6 && ctype_xdigit($darkHex)) {
        $dr = hexdec(substr($darkHex, 0, 2));
        $dg = hexdec(substr($darkHex, 2, 2));
        $db = hexdec(substr($darkHex, 4, 2));
        $darkRgb = "{$dr}, {$dg}, {$db}";
    } else {
        // Default fallback: #3b4863 -> 59, 72, 99
        $darkRgb = "59, 72, 99";
    }
    
    // Convert header color hex to RGB (format: --header-rgb: 25, 34, 143;)
    $headerHex = ltrim($headerColor, '#');
    if (strlen($headerHex) === 3) {
        $headerHex = $headerHex[0] . $headerHex[0] . $headerHex[1] . $headerHex[1] . $headerHex[2] . $headerHex[2];
    }
    if (strlen($headerHex) === 6 && ctype_xdigit($headerHex)) {
        $hr = hexdec(substr($headerHex, 0, 2));
        $hg = hexdec(substr($headerHex, 2, 2));
        $hb = hexdec(substr($headerHex, 4, 2));
        $headerRgb = "{$hr}, {$hg}, {$hb}";
    } else {
        // Default fallback: #19228f -> 25, 34, 143
        $headerRgb = "25, 34, 143";
    }
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="{{ $headerStyles }}" data-menu-styles="{{ $menuStyles }}" data-toggled="close">
<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <title>@yield('title', 'لوحة التحكم') - {{ $siteName }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset($favicon) }}" type="image/x-icon">

    <!-- Choices JS -->
    <script src="{{ asset('assets/admin/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <!-- Main Theme Js -->
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>

    <!-- Bootstrap Css -->
    <link id="bootstrap-style" href="{{ asset('assets/admin/libs/bootstrap/css/bootstrap.rtl.min.css') }}" rel="stylesheet">

    <!-- Style Css -->
    <link href="{{ asset('assets/admin/css/styles.min.css') }}" rel="stylesheet">
    
    <!-- Dynamic Primary Color & Dark Color & Header Color (must be after styles.min.css to override) -->
    <style>
        :root {
            --primary-rgb: {{ $primaryRgb }};
            --dark-rgb: {{ $darkRgb }};
            --header-rgb: {{ $headerRgb }};
        }
        [data-menu-styles=dark] {
            --menu-bg: {{ str_starts_with($darkColor, '#') ? $darkColor : '#' . $darkColor }};
        }
        [data-menu-styles=light] {
            --menu-bg: {{ str_starts_with($menuColor, '#') ? $menuColor : '#' . $menuColor }};
        }
        
        /* Header background color for dark and color styles */
        [data-header-styles=dark] .app-header,
        [data-header-styles=color] .app-header {
            background-color: {{ str_starts_with($headerColor, '#') ? $headerColor : '#' . $headerColor }} !important;
        }
        
        /* Apply header color to search input and button in header for dark and color styles */
        @if($headerStyles === 'dark' || $headerStyles === 'color')
        [data-header-styles=dark] .header-content-left .form-control,
        [data-header-styles=color] .header-content-left .form-control {
            background-color: {{ str_starts_with($headerColor, '#') ? $headerColor : '#' . $headerColor }} !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
        }
        
        [data-header-styles=dark] .header-content-left .form-control::placeholder,
        [data-header-styles=color] .header-content-left .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        [data-header-styles=dark] .header-content-left .form-control:focus,
        [data-header-styles=color] .header-content-left .form-control:focus {
            background-color: {{ str_starts_with($headerColor, '#') ? $headerColor : '#' . $headerColor }} !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
        }
        
        [data-header-styles=dark] .header-content-left .btn,
        [data-header-styles=color] .header-content-left .btn {
            background-color: {{ str_starts_with($headerColor, '#') ? $headerColor : '#' . $headerColor }} !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
        }
        
        [data-header-styles=dark] .header-content-left .btn:hover,
        [data-header-styles=color] .header-content-left .btn:hover {
            background-color: {{ str_starts_with($headerColor, '#') ? $headerColor : '#' . $headerColor }} !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
            opacity: 0.9;
        }
        @endif
        
    </style>
    
    <!-- Apply theme mode and menu styles from localStorage on page load (before main.js) -->
    <script>
        (function() {
            const serverHeaderStyles = "{{ $headerStyles }}";
            const serverMenuStyles = "{{ $menuStyles }}";
            
            // Apply theme mode from localStorage
            if (localStorage.getItem("nowadarktheme")) {
                document.documentElement.setAttribute("data-theme-mode", "dark");
                
                // Apply header styles: if dark mode is active, use saved header style or default to dark
                // But respect user's settings if they chose dark or color
                const savedHeaderStyle = localStorage.getItem("nowaHeader");
                if (savedHeaderStyle) {
                    document.documentElement.setAttribute("data-header-styles", savedHeaderStyle);
                } else if (serverHeaderStyles === "dark" || serverHeaderStyles === "color") {
                    // Respect server settings if they're dark or color
                    document.documentElement.setAttribute("data-header-styles", serverHeaderStyles);
                } else {
                    // Default to dark when dark mode is active
                    document.documentElement.setAttribute("data-header-styles", "dark");
                }
            } else {
                document.documentElement.setAttribute("data-theme-mode", "light");
                
                // Apply header styles from localStorage or server value
                const savedHeaderStyle = localStorage.getItem("nowaHeader");
                if (savedHeaderStyle) {
                    document.documentElement.setAttribute("data-header-styles", savedHeaderStyle);
                } else {
                    // Use server value if no localStorage value
                    document.documentElement.setAttribute("data-header-styles", serverHeaderStyles);
                }
            }
            
            // Apply menu styles: prioritize server value from database
            // Only use localStorage if it was set manually (not from settings page)
            // When settings are saved, localStorage is cleared, so server value is used
            const savedMenuStyle = localStorage.getItem("nowaMenu");
            // Always use server value to respect database settings
            document.documentElement.setAttribute("data-menu-styles", serverMenuStyles);
            
            // If localStorage exists and is different, it means user changed it manually in this session
            // In that case, we can optionally keep it, but for consistency, we'll use server value
            // and let the settings page update localStorage when user changes it
        })();
    </script>

    <!-- Icons Css -->
    <link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet">
    <!-- Feather Icons Css (Direct Load) -->
    <link href="{{ asset('assets/admin/icon-fonts/feather/feather.css') }}" rel="stylesheet">

    <!-- Node Waves Css -->
    <link href="{{ asset('assets/admin/libs/node-waves/waves.min.css') }}" rel="stylesheet">

    <!-- Simplebar Css -->
    <link href="{{ asset('assets/admin/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/flatpickr/flatpickr.min.css') }}">

    <!-- Choices Css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/choices.js/public/assets/styles/choices.min.css') }}">

    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">

    <!-- General Cards Css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/general-cards.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Loader -->
    <div id="loader">
        <img src="{{ asset('assets/admin/images/media/loader.svg') }}" alt="">
    </div>
    <!-- Loader -->

    <div class="page">
        <!-- Impersonation Banner -->
        @if(session('impersonator_id'))
            <div class="alert alert-warning alert-dismissible fade show mb-0 border-0 rounded-0" role="alert" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%); border-bottom: 2px solid #ffc107 !important;">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-person-check fs-4 text-warning"></i>
                            <div>
                                <strong>أنت تدخل بحساب مستخدم آخر</strong>
                                <div class="small">المستخدم الحالي: <strong>{{ auth()->user()->name }}</strong> | الحساب الأصلي: <strong>{{ session('impersonator_name') }}</strong></div>
                            </div>
                        </div>
                        <form action="{{ route('admin.users.stop-impersonating') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>
                                العودة للحساب الأصلي
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- app-header -->
        @include('admin.partials.header')
        <!-- /app-header -->

        <!-- Start::app-sidebar -->
        @include('admin.partials.sidebar')
        <!-- End::app-sidebar -->

        <!-- main-content -->
        <div class="main-content app-content">
            <!-- container -->
            <div class="main-container container-fluid">
                <!-- breadcrumb -->
                @include('admin.partials.breadcrumb')
                <!-- /breadcrumb -->

                <!-- Flash Messages -->
                @include('admin.partials.toast')

                @yield('content')
            </div>
            <!-- Container closed -->
        </div>
        <!-- main-content closed -->

        <!-- Footer Start -->
        @include('admin.partials.footer')
        <!-- Footer End -->
    </div>

    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="bi bi-arrow-up fs-20"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->

    <!-- jQuery -->
    <script src="{{ asset('assets/admin/libs/jquery/jquery.min.js') }}"></script>

    <!-- Popper JS -->
    <script src="{{ asset('assets/admin/libs/@popperjs/core/umd/popper.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Defaultmenu JS -->
    <script src="{{ asset('assets/admin/js/defaultmenu.min.js') }}"></script>

    <!-- Node Waves JS-->
    <script src="{{ asset('assets/admin/libs/node-waves/waves.min.js') }}"></script>

    <!-- Simplebar JS -->
    <script src="{{ asset('assets/admin/libs/simplebar/simplebar.min.js') }}"></script>

    <!-- Sticky JS -->
    <script src="{{ asset('assets/admin/js/sticky.js') }}"></script>

    <!-- Flatpickr JS -->
    <script src="{{ asset('assets/admin/libs/flatpickr/flatpickr.min.js') }}"></script>
    @if(file_exists(public_path('assets/admin/libs/flatpickr/l10n/ar.js')))
        <script src="{{ asset('assets/admin/libs/flatpickr/l10n/ar.js') }}"></script>
    @endif

    <!-- Admin Date Picker -->
    <script src="{{ asset('assets/admin/js/admin-datepicker.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/admin/js/custom.js') }}"></script>
    <script src="{{ asset('assets/admin/js/notifications.js') }}"></script>
    <script src="{{ asset('assets/admin/js/notification-panel.js') }}"></script>
    <script src="{{ asset('assets/admin/js/messages-panel.js') }}"></script>

    <!-- Flash Messages as Bootstrap Toasts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // عرض رسائل Flash كـ Bootstrap Toasts
            @if(session('success'))
                if (window.adminNotifications) {
                    window.adminNotifications.success('{{ session('success') }}');
                }
            @endif

            @if(session('error'))
                if (window.adminNotifications) {
                    window.adminNotifications.error('{{ session('error') }}');
                }
            @endif

            @if(session('warning'))
                if (window.adminNotifications) {
                    window.adminNotifications.warning('{{ session('warning') }}');
                }
            @endif

            @if(session('info'))
                if (window.adminNotifications) {
                    window.adminNotifications.info('{{ session('info') }}');
                }
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    if (window.adminNotifications) {
                        window.adminNotifications.error('{{ $error }}');
                    }
                @endforeach
            @endif
        });
    </script>

    <!-- General Helpers JS -->
    <script src="{{ asset('assets/admin/js/general-helpers.js') }}"></script>

    <!-- Admin CRUD JS -->
    <script src="{{ asset('assets/admin/js/admin-crud.js') }}"></script>

    @stack('scripts')
</body>
</html>

