<aside class="app-sidebar sticky" id="sidebar">
    <div class="main-sidebar-header">
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

    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">

            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path></svg>
            </div>

            @php
                $u = auth()->user();
                $isActive = fn($routes) => request()->routeIs($routes) ? 'active' : '';
                $isOpen   = fn($routes) => request()->routeIs($routes) ? 'open'   : '';
                $show     = fn($routes) => request()->routeIs($routes) ? 'display:block' : '';

                // SVG مختصرة مستخدمة كثيراً
                $svgAngle = '<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>';
                $svgAngleSub = '<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';
            @endphp

            <ul class="main-menu">

                {{-- ══════════════════════════════════════
                   لوحة التحكم — جميع الأدوار
                   ══════════════════════════════════════ --}}
                <li class="slide {{ $isActive('admin.dashboard') }}">
                    <a href="{{ route('admin.dashboard') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M3 13h1v7c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-7h1a1 1 0 0 0 .707-1.707l-9-9a.999.999 0 0 0-1.414 0l-9 9A1 1 0 0 0 3 13zm7 7v-5h4v5h-4zm2-15.586 6 6V15l.001 5H16v-5c0-1.103-.897-2-2-2h-4c-1.103 0-2 .897-2 2v5H6v-9.586l6-6z"/></svg>
                        <span class="side-menu__label">لوحة التحكم</span>
                    </a>
                </li>

                {{-- ══════════════════════════════════════
                   إدارة النظام — SuperAdmin/Admin/EnergyAuthority
                   ══════════════════════════════════════ --}}
                @if($u->isSuperAdmin() || $u->isAdmin() || $u->isEnergyAuthority())
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">إدارة النظام</span>
                </li>

                {{-- المستخدمون --}}
                @can('viewAny', App\Models\User::class)
                <li class="slide has-sub {{ $isOpen('admin.users.*') }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.users.*') }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                        <span class="side-menu__label">المستخدمون</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ $show('admin.users.*') }}">
                        <li class="slide">
                            <a href="{{ route('admin.users.index') }}" class="side-menu__item {{ $isActive('admin.users.index') }}">قائمة المستخدمين</a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- الأدوار والصلاحيات --}}
                @php
                    $canViewRoles = $u->isSuperAdmin() || $u->isEnergyAuthority();
                    $canViewPermissions = $u->isSuperAdmin() || $u->hasPermission('permissions.manage');
                    $canViewAuditLogs = $u->isSuperAdmin() || $u->can('viewAny', \App\Models\PermissionAuditLog::class);
                    $canViewRolesPermissions = $canViewRoles || $canViewPermissions || $canViewAuditLogs;
                @endphp
                @if($canViewRolesPermissions)
                <li class="slide has-sub {{ ($isOpen('admin.roles.*') || $isOpen('admin.permissions.*') || $isOpen('admin.permission-audit-logs.*')) ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ ($isActive('admin.roles.*') || $isActive('admin.permissions.*') || $isActive('admin.permission-audit-logs.*')) ? 'active' : '' }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                        <span class="side-menu__label">الأدوار والصلاحيات</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ ($show('admin.roles.*') || $show('admin.permissions.*') || $show('admin.permission-audit-logs.*')) ? 'display:block' : '' }}">
                        @if($canViewRoles)
                        <li class="slide has-sub {{ $isOpen('admin.roles.*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.roles.*') }}">{!! $svgAngleSub !!} الأدوار</a>
                            <ul class="slide-menu child2" style="{{ $show('admin.roles.*') }}">
                                <li class="slide"><a href="{{ route('admin.roles.index') }}" class="side-menu__item {{ $isActive('admin.roles.index') }}">قائمة الأدوار</a></li>
                                <li class="slide"><a href="{{ route('admin.roles.create') }}" class="side-menu__item {{ $isActive('admin.roles.create') }}">إضافة دور جديد</a></li>
                            </ul>
                        </li>
                        @endif
                        @if($canViewPermissions)
                        <li class="slide"><a href="{{ route('admin.permissions.index') }}" class="side-menu__item {{ $isActive('admin.permissions.index') }}">شجرة الصلاحيات</a></li>
                        @endif
                        @if($canViewAuditLogs)
                        <li class="slide"><a href="{{ route('admin.permission-audit-logs.index') }}" class="side-menu__item {{ $isActive('admin.permission-audit-logs.*') }}">سجل التغييرات</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- المشغلين --}}
                @if($u->isSuperAdmin() || $u->isEnergyAuthority())
                @can('viewAny', App\Models\Operator::class)
                <li class="slide has-sub {{ $isOpen('admin.operators.*') }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.operators.*') }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                        <span class="side-menu__label">المشغلين</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ $show('admin.operators.*') }}">
                        <li class="slide">
                            <a href="{{ route('admin.operators.pending-approval') }}" class="side-menu__item {{ $isActive('admin.operators.pending-approval') }}">
                                <i class="bi bi-hourglass-split me-2 text-warning"></i>في انتظار الاعتماد
                                @php $pendingCount = \App\Models\Operator::where('is_approved', false)->count(); @endphp
                                @if($pendingCount > 0)<span class="badge bg-warning ms-1">{{ $pendingCount }}</span>@endif
                            </a>
                        </li>
                        <li class="slide"><a href="{{ route('admin.operators.index') }}" class="side-menu__item {{ $isActive('admin.operators.index') && !$isActive('admin.operators.pending-approval') }}">جميع المشغلين</a></li>
                    </ul>
                </li>
                @endcan
                @endif

                {{-- الأرقام المصرح بها --}}
                @can('viewAny', App\Models\AuthorizedPhone::class)
                <li class="slide {{ $isActive('admin.authorized-phones.*') }}">
                    <a href="{{ route('admin.authorized-phones.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        <span class="side-menu__label">الأرقام المصرح بها</span>
                    </a>
                </li>
                @endcan

                {{-- الطلبات المقدمة — SuperAdmin/Admin --}}
                @if($u->isSuperAdmin() || $u->isAdmin())
                <li class="slide {{ $isActive('admin.portal-requests.*') }}">
                    <a href="{{ route('admin.portal-requests.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-1 14H5c-.55 0-1-.45-1-1V7c0-.55.45-1 1-1h14c.55 0 1 .45 1 1v10c0 .55-.45 1-1 1zm-5-7H7v-2h7v2zm3-4H7V5h10v2z"/></svg>
                        <span class="side-menu__label">الطلبات المقدمة</span>
                    </a>
                </li>
                @endif

                {{-- إعدادات النظام — SuperAdmin --}}
                @if($u->isSuperAdmin())
                @php
                    $isSystemSettingsOpen = $isOpen('admin.settings.*') || $isOpen('admin.constants.*') || $isOpen('admin.versions.*') || $isOpen('admin.logs.*');
                    $isSystemSettingsActive = $isActive('admin.settings.*') || $isActive('admin.constants.*') || $isActive('admin.versions.*') || $isActive('admin.logs.*');
                @endphp
                <li class="slide has-sub {{ $isSystemSettingsOpen ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ $isSystemSettingsActive ? 'active' : '' }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.488.488 0 0 0-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94L14.4 2.81c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                        <span class="side-menu__label">إعدادات النظام</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ ($show('admin.settings.*') || $show('admin.constants.*') || $show('admin.versions.*') || $show('admin.logs.*')) ? 'display:block' : '' }}">
                        <li class="slide"><a href="{{ route('admin.settings.index') }}" class="side-menu__item {{ $isActive('admin.settings.*') }}">إعدادات الموقع</a></li>
                        <li class="slide"><a href="{{ route('admin.constants.index') }}" class="side-menu__item {{ $isActive('admin.constants.*') }}">إدارة الثوابت</a></li>
                        <li class="slide"><a href="{{ route('admin.versions.index') }}" class="side-menu__item {{ $isActive('admin.versions.*') }}">إدارة الإصدارات</a></li>
                        @if($u->hasPermission('logs.view'))
                        <li class="slide"><a href="{{ route('admin.logs.index') }}" class="side-menu__item {{ $isActive('admin.logs.*') }}">سجل الأخطاء</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- الرسائل الترحيبية + قوالب SMS --}}
                @php
                    $canViewWelcomeMessages = $u->can('viewAny', App\Models\WelcomeMessage::class);
                    $canViewSmsTemplates = $u->can('viewAny', App\Models\SmsTemplate::class);
                @endphp
                @if($canViewWelcomeMessages || $canViewSmsTemplates)
                <li class="slide has-sub {{ ($isOpen('admin.welcome-messages.*') || $isOpen('admin.sms-templates.*')) ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ ($isActive('admin.welcome-messages.*') || $isActive('admin.sms-templates.*')) ? 'active' : '' }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                        <span class="side-menu__label">الرسائل</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ ($show('admin.welcome-messages.*') || $show('admin.sms-templates.*')) ? 'display:block' : '' }}">
                        @if($canViewWelcomeMessages)
                        <li class="slide"><a href="{{ route('admin.welcome-messages.index') }}" class="side-menu__item {{ $isActive('admin.welcome-messages.index') }}">الرسائل الترحيبية</a></li>
                        @endif
                        @if($canViewSmsTemplates)
                        <li class="slide"><a href="{{ route('admin.sms-templates.index') }}" class="side-menu__item {{ $isActive('admin.sms-templates.index') }}">قوالب SMS</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- المستخدمين النشطين --}}
                <li class="slide {{ $isActive('admin.active-users.*') }}">
                    <a href="{{ route('admin.active-users.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        <span class="side-menu__label">المستخدمين النشطين</span>
                    </a>
                </li>
                @endif {{-- end إدارة النظام --}}

                {{-- ══════════════════════════════════════
                   المشغل — CompanyOwner فقط
                   ══════════════════════════════════════ --}}
                @if($u->isCompanyOwner())
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">المشغل</span>
                </li>

                <li class="slide {{ $isActive('admin.operators.profile') }}">
                    <a href="{{ route('admin.operators.profile') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <span class="side-menu__label">ملف المشغل</span>
                    </a>
                </li>

                @if($u->hasApprovedOperator())
                    @can('viewAny', App\Models\User::class)
                    <li class="slide {{ $isActive('admin.users.*') }}">
                        <a href="{{ route('admin.users.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                            <span class="side-menu__label">فريق العمل</span>
                        </a>
                    </li>
                    @endcan

                    <li class="slide {{ $isActive('admin.roles.*') }}">
                        <a href="{{ route('admin.roles.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                            <span class="side-menu__label">الأدوار المخصصة</span>
                        </a>
                    </li>

                    @if($u->hasPermission('permissions.manage'))
                    <li class="slide {{ $isActive('admin.permissions.index') }}">
                        <a href="{{ route('admin.permissions.index') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            <span class="side-menu__label">شجرة الصلاحيات</span>
                        </a>
                    </li>
                    @endif
                @endif
                @endif {{-- end المشغل --}}

                {{-- ══════════════════════════════════════
                   إدارة العمليات — جميع الأدوار (حسب الصلاحيات)
                   ══════════════════════════════════════ --}}
                @php
                    // CompanyOwner بدون اعتماد ما يشوف قسم العمليات (إلا وحدات التوليد والمولدات)
                    $needsApproval = $u->isCompanyOwner() && !$u->hasApprovedOperator();
                    $showOpsCategory = !$needsApproval && (
                        $u->can('viewAny', App\Models\Subscriber::class)
                        || $u->can('viewAny', App\Models\GenerationUnit::class)
                        || $u->can('viewAny', App\Models\Generator::class)
                        || $u->can('viewAny', App\Models\Task::class)
                        || $u->can('viewAny', App\Models\OperationLog::class)
                        || $u->can('viewAny', App\Models\MaintenanceRecord::class)
                        || $u->can('viewAny', App\Models\ComplianceSafety::class)
                    );
                    // لكن المولدات ووحدات التوليد تظهر حتى بدون اعتماد
                    if (!$showOpsCategory && ($u->can('viewAny', App\Models\GenerationUnit::class) || $u->can('viewAny', App\Models\Generator::class))) {
                        $showOpsCategory = true;
                    }
                @endphp
                @if($showOpsCategory)
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">إدارة العمليات</span>
                </li>

                {{-- بيانات المشتركين (CompanyOwner يحتاج اعتماد) --}}
                @if(!$u->isCompanyOwner() || $u->hasApprovedOperator())
                @can('viewAny', App\Models\Subscriber::class)
                @php
                    $subsOpen = $isOpen('admin.subscribers.*') || $isOpen('admin.meter-readings.*') || $isOpen('admin.invoices.*') || $isOpen('admin.subscriber-account.*') || $isOpen('admin.invoice-reports.*') || $isOpen('admin.minimum-charge-rules.*');
                    $subsActive = $isActive('admin.subscribers.*') || $isActive('admin.meter-readings.*') || $isActive('admin.invoices.*') || $isActive('admin.subscriber-account.*') || $isActive('admin.invoice-reports.*') || $isActive('admin.minimum-charge-rules.*');
                @endphp
                <li class="slide has-sub {{ $subsOpen ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ $subsActive ? 'active' : '' }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                        <span class="side-menu__label">إدارة بيانات المشتركين</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ $subsOpen ? 'display:block' : '' }}">
                        <li class="slide"><a href="{{ route('admin.subscribers.index') }}" class="side-menu__item {{ $isActive('admin.subscribers.index') }}">بيانات المشتركين</a></li>
                        <li class="slide"><a href="{{ route('admin.meter-readings.index') }}" class="side-menu__item {{ $isActive('admin.meter-readings.*') }}">قراءات العدادات</a></li>
                        @can('viewAny', App\Models\Invoice::class)
                        <li class="slide"><a href="{{ route('admin.invoices.index') }}" class="side-menu__item {{ $isActive('admin.invoices.*') }}">الفوترة والتحصيل</a></li>
                        @endcan
                        @if($u->hasPermission('subscriber_accounts.view'))
                        <li class="slide"><a href="{{ route('admin.subscriber-account.index') }}" class="side-menu__item {{ $isActive('admin.subscriber-account.*') }}">حساب المشترك</a></li>
                        @endif
                        @if($u->isSuperAdmin() || $u->isAdmin())
                        <li class="slide"><a href="{{ route('admin.invoice-reports.index') }}" class="side-menu__item {{ $isActive('admin.invoice-reports.*') }}">تقارير الفوترة</a></li>
                        <li class="slide"><a href="{{ route('admin.minimum-charge-rules.index') }}" class="side-menu__item {{ $isActive('admin.minimum-charge-rules.*') }}">قواعد الحد الأدنى</a></li>
                        @endif
                    </ul>
                </li>
                @endcan
                @endif

                {{-- وحدات التوليد --}}
                @can('viewAny', App\Models\GenerationUnit::class)
                <li class="slide {{ $isActive('admin.generation-units.*') }}">
                    <a href="{{ route('admin.generation-units.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 2.18l8 4v8.82c0 4.54-3.07 8.86-8 9.82-4.93-.96-8-5.28-8-9.82V8.18l8-4z"/><path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                        <span class="side-menu__label">وحدات التوليد</span>
                    </a>
                </li>
                @endcan

                {{-- المولدات --}}
                @can('viewAny', App\Models\Generator::class)
                <li class="slide has-sub {{ request()->routeIs('admin.generators.*') ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.generators.*') ? 'active' : '' }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                        <span class="side-menu__label">المولدات</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ request()->routeIs('admin.generators.*') ? 'display:block' : '' }}">
                        <li class="slide"><a href="{{ route('admin.generators.index') }}" class="side-menu__item {{ $isActive('admin.generators.index') }}">قائمة المولدات</a></li>
                        @can('create', App\Models\Generator::class)
                        <li class="slide"><a href="{{ route('admin.generators.create') }}" class="side-menu__item {{ $isActive('admin.generators.create') }}">إضافة مولد جديد</a></li>
                        @endcan
                    </ul>
                </li>
                @endcan

                {{-- إدارة المهام --}}
                @can('viewAny', App\Models\Task::class)
                <li class="slide {{ $isActive('admin.tasks.*') }}">
                    <a href="{{ route('admin.tasks.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        <span class="side-menu__label">{{ ($u->isTechnician() || $u->isCivilDefense()) ? 'مهامي' : 'إدارة المهام' }}</span>
                    </a>
                </li>
                @endcan

                {{-- أسعار التعرفة --}}
                @can('viewAny', App\Models\ElectricityTariffPrice::class)
                <li class="slide {{ $isActive('admin.electricity-tariff-prices.*') || $isActive('admin.operators.tariff-prices.*') }}">
                    @if($u->isCompanyOwner() && $u->ownedOperators()->first())
                    <a href="{{ route('admin.operators.tariff-prices.index', $u->ownedOperators()->first()) }}" class="side-menu__item">
                    @else
                    <a href="{{ route('admin.electricity-tariff-prices.index') }}" class="side-menu__item">
                    @endif
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                        <span class="side-menu__label">أسعار التعرفة</span>
                    </a>
                </li>
                @endcan

                {{-- خصم الموظفين --}}
                @if($u->isSuperAdmin() || $u->hasPermission('employee_discount_rates.view'))
                <li class="slide {{ $isActive('admin.employee-discount-rates.*') }}">
                    <a href="{{ route('admin.employee-discount-rates.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/><path d="M19 11h-2v2h-2v2h2v2h2v-2h2v-2h-2z" opacity=".5"/></svg>
                        <span class="side-menu__label">خصم الموظفين</span>
                    </a>
                </li>
                @endif

                {{-- السجلات --}}
                @php
                    $canViewOpLogs = $u->can('viewAny', App\Models\OperationLog::class);
                    $canViewFuel = $u->can('viewAny', App\Models\FuelEfficiency::class);
                    $canViewMaint = $u->can('viewAny', App\Models\MaintenanceRecord::class);
                    $canViewCompliance = $u->can('viewAny', App\Models\ComplianceSafety::class);
                    $canViewRecords = $canViewOpLogs || $canViewFuel || $canViewMaint || $canViewCompliance;
                    $isRecordsOpen = $isOpen('admin.operation-logs.*') || $isOpen('admin.fuel-efficiencies.*') || $isOpen('admin.maintenance-records.*') || $isOpen('admin.compliance-safeties.*');
                    $isRecordsActive = $isActive('admin.operation-logs.*') || $isActive('admin.fuel-efficiencies.*') || $isActive('admin.maintenance-records.*') || $isActive('admin.compliance-safeties.*');
                @endphp
                @if($canViewRecords)
                <li class="slide has-sub {{ $isRecordsOpen ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ $isRecordsActive ? 'active' : '' }}">
                        {!! $svgAngle !!}
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                        <span class="side-menu__label">السجلات</span>
                    </a>
                    <ul class="slide-menu child1" style="{{ ($show('admin.operation-logs.*') || $show('admin.fuel-efficiencies.*') || $show('admin.maintenance-records.*') || $show('admin.compliance-safeties.*')) ? 'display:block' : '' }}">
                        @if($canViewOpLogs)
                        <li class="slide"><a href="{{ route('admin.operation-logs.index') }}" class="side-menu__item {{ $isActive('admin.operation-logs.*') }}">سجلات التشغيل</a></li>
                        @endif
                        @if($canViewFuel)
                        <li class="slide"><a href="{{ route('admin.fuel-efficiencies.index') }}" class="side-menu__item {{ $isActive('admin.fuel-efficiencies.*') }}">كفاءة الوقود</a></li>
                        @endif
                        @if($canViewMaint)
                        <li class="slide"><a href="{{ route('admin.maintenance-records.index') }}" class="side-menu__item {{ $isActive('admin.maintenance-records.*') }}">سجلات الصيانة</a></li>
                        @endif
                        @if($canViewCompliance)
                        <li class="slide"><a href="{{ route('admin.compliance-safeties.index') }}" class="side-menu__item {{ $isActive('admin.compliance-safeties.*') }}">الامتثال والسلامة</a></li>
                        @endif
                    </ul>
                </li>
                @endif
                @endif {{-- end إدارة العمليات --}}

                {{-- ══════════════════════════════════════
                   التواصل — جميع من لديهم صلاحية
                   ══════════════════════════════════════ --}}
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

                {{-- ══════════════════════════════════════
                   خدمات — جميع الأدوار
                   ══════════════════════════════════════ --}}
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">خدمات</span>
                </li>

                <li class="slide {{ $isActive('admin.complaints-suggestions.*') }}">
                    <a href="{{ route('admin.complaints-suggestions.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                        <span class="side-menu__label">الشكاوى والمقترحات</span>
                    </a>
                </li>

                @can('viewAny', App\Models\InspectionViolationCase::class)
                <li class="slide {{ $isActive('admin.inspection-violation-cases.*') }}">
                    <a href="{{ route('admin.inspection-violation-cases.index') }}" class="side-menu__item {{ $isActive('admin.inspection-violation-cases.*') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <span class="side-menu__label">قضايا التفتيش والتعدي</span>
                    </a>
                </li>
                @endcan

                {{-- ══════════════════════════════════════
                   الدعم — جميع الأدوار (آخر شي)
                   ══════════════════════════════════════ --}}
                <li class="slide__category mt-3">
                    <span class="side-menu__label text-muted text-xs opacity-70">الدعم</span>
                </li>

                <li class="slide {{ $isActive('admin.guide.*') }}">
                    <a href="{{ route('admin.guide.index') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <span class="side-menu__label">الدليل الإرشادي</span>
                    </a>
                </li>

                <li class="slide {{ $isActive(['admin.about', 'admin.changelog']) }}">
                    <a href="{{ route('admin.about') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14c-.55 0-1-.45-1-1v-4c0-.55.45-1 1-1s1 .45 1 1v4c0 .55-.45 1-1 1zm1-8h-2V7h2v2z"/></svg>
                        <span class="side-menu__label">حول النظام</span>
                    </a>
                </li>

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg>
            </div>

        </nav>
    </div>
</aside>
