{{-- Sidebar للمشغل والموظفين --}}
@php
    $u = auth()->user();
    $isActive = fn($routes) => request()->routeIs($routes) ? 'active' : '';
    $isOpen   = fn($routes) => request()->routeIs($routes) ? 'open'   : '';
    $show     = fn($routes) => request()->routeIs($routes) ? 'display:block' : '';
@endphp

@if($u->isCompanyOwner())
    {{-- المشغل --}}
    <li class="slide__category mt-3">
        <span class="side-menu__label text-muted text-xs opacity-70">المشغل</span>
    </li>

    {{-- ملف المشغل --}}
    <li class="slide {{ $isActive('admin.operators.profile') }}">
        <a href="{{ route('admin.operators.profile') }}" class="side-menu__item">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <span class="side-menu__label">ملف المشغل</span>
        </a>
    </li>

    {{-- فريق العمل (للمشغل المعتمد فقط) --}}
    @if($u->hasApprovedOperator())
        @can('viewAny', App\Models\User::class)
            <li class="slide {{ $isActive('admin.users.*') }}">
                <a href="{{ route('admin.users.index') }}" class="side-menu__item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                    <span class="side-menu__label">فريق العمل</span>
                </a>
            </li>
        @endcan

        {{-- الأدوار المخصصة --}}
        <li class="slide {{ $isActive('admin.roles.*') }}">
            <a href="{{ route('admin.roles.index') }}" class="side-menu__item">
                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm6 9.09c0 4-2.55 7.7-6 8.83-3.45-1.13-6-4.83-6-8.83V6.31l6-2.12 6 2.12v4.78z"/></svg>
                <span class="side-menu__label">الأدوار المخصصة</span>
            </a>
        </li>

        {{-- شجرة الصلاحيات --}}
        <li class="slide {{ $isActive('admin.permissions.index') }}">
            <a href="{{ route('admin.permissions.index') }}" class="side-menu__item">
                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                <span class="side-menu__label">شجرة الصلاحيات</span>
            </a>
        </li>
    @endif

    {{-- إدارة العمليات --}}
    <li class="slide__category mt-3">
        <span class="side-menu__label text-muted text-xs opacity-70">إدارة العمليات</span>
    </li>

    {{-- إدارة المهام (للمشغل المعتمد فقط) --}}
    @if($u->hasApprovedOperator())
        @can('viewAny', App\Models\Task::class)
            <li class="slide {{ $isActive('admin.tasks.*') }}">
                <a href="{{ route('admin.tasks.index') }}" class="side-menu__item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    <span class="side-menu__label">إدارة المهام</span>
                </a>
            </li>
        @endcan
    @endif

    {{-- إدارة المشغل --}}
    <li class="slide has-sub {{ $isOpen('admin.generation-units.*') || $isOpen('admin.generators.*') || $isOpen('admin.operators.tariff-prices.*') ? 'open' : '' }}">
        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.generation-units.*') || $isActive('admin.generators.*') || $isActive('admin.operators.tariff-prices.*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M4 21h16v-2H4v2zm0-4h16v-2H4v2zm0-4h16v-2H4v2zm0-4h7V5H4v4zm9 0h7V5h-7v4z"/></svg>
            <span class="side-menu__label">إدارة المشغل</span>
        </a>
        <ul class="slide-menu child1" style="{{ ($show('admin.generation-units.*') || $show('admin.generators.*') || $show('admin.operators.tariff-prices.*')) ? 'display:block' : '' }}">
            @can('viewAny', App\Models\GenerationUnit::class)
                <li class="slide">
                    <a href="{{ route('admin.generation-units.index') }}" class="side-menu__item {{ $isActive('admin.generation-units.*') }}">
                        وحدات التوليد
                    </a>
                </li>
            @endcan
            @can('viewAny', App\Models\Generator::class)
                <li class="slide has-sub {{ request()->routeIs('admin.generators.*') ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="side-menu__item {{ request()->routeIs('admin.generators.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        المولدات
                    </a>
                    <ul class="slide-menu child2" style="{{ request()->routeIs('admin.generators.*') ? 'display:block' : '' }}">
                        <li class="slide">
                            <a href="{{ route('admin.generators.index') }}" class="side-menu__item {{ $isActive('admin.generators.index') }}">
                                قائمة المولدات
                            </a>
                        </li>
                        @can('create', App\Models\Generator::class)
                            <li class="slide">
                                <a href="{{ route('admin.generators.create') }}" class="side-menu__item {{ $isActive('admin.generators.create') }}">
                                    إضافة مولد جديد
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan
            {{-- أسعار التعرفة (للمشغل المعتمد فقط) --}}
            @if($u->hasApprovedOperator())
                @can('viewAny', App\Models\ElectricityTariffPrice::class)
                    <li class="slide">
                        <a href="{{ route('admin.operators.tariff-prices.index', $u->ownedOperators()->first()) }}" class="side-menu__item {{ $isActive('admin.operators.tariff-prices.*') }}">
                            أسعار التعرفة
                        </a>
                    </li>
                @endcan
            @endif
        </ul>
    </li>

    {{-- السجلات (للمشغل المعتمد فقط) --}}
    @if($u->hasApprovedOperator())
        @php
            $canViewOperationLogs = auth()->user()->can('viewAny', App\Models\OperationLog::class);
            $canViewFuelEfficiencies = auth()->user()->can('viewAny', App\Models\FuelEfficiency::class);
            $canViewMaintenanceRecords = auth()->user()->can('viewAny', App\Models\MaintenanceRecord::class);
            $canViewComplianceSafeties = auth()->user()->can('viewAny', App\Models\ComplianceSafety::class);
            $canViewRecords = $canViewOperationLogs || $canViewFuelEfficiencies || $canViewMaintenanceRecords || $canViewComplianceSafeties;
            $isRecordsOpen = $isOpen('admin.operation-logs.*') || $isOpen('admin.fuel-efficiencies.*') || $isOpen('admin.maintenance-records.*') || $isOpen('admin.compliance-safeties.*');
            $isRecordsActive = $isActive('admin.operation-logs.*') || $isActive('admin.fuel-efficiencies.*') || $isActive('admin.maintenance-records.*') || $isActive('admin.compliance-safeties.*');
        @endphp
        @if($canViewRecords)
            <li class="slide has-sub {{ $isRecordsOpen ? 'open' : '' }}">
                <a href="javascript:void(0);" class="side-menu__item {{ $isRecordsActive ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    <span class="side-menu__label">السجلات</span>
                </a>
                <ul class="slide-menu child1" style="{{ ($show('admin.operation-logs.*') || $show('admin.fuel-efficiencies.*') || $show('admin.maintenance-records.*') || $show('admin.compliance-safeties.*')) ? 'display:block' : '' }}">
                    @if($canViewOperationLogs)
                        <li class="slide">
                            <a href="{{ route('admin.operation-logs.index') }}" class="side-menu__item {{ $isActive('admin.operation-logs.*') }}">
                                سجلات التشغيل
                            </a>
                        </li>
                    @endif
                    @if($canViewFuelEfficiencies)
                        <li class="slide">
                            <a href="{{ route('admin.fuel-efficiencies.index') }}" class="side-menu__item {{ $isActive('admin.fuel-efficiencies.*') }}">
                                كفاءة الوقود
                            </a>
                        </li>
                    @endif
                    @if($canViewMaintenanceRecords)
                        <li class="slide">
                            <a href="{{ route('admin.maintenance-records.index') }}" class="side-menu__item {{ $isActive('admin.maintenance-records.*') }}">
                                سجلات الصيانة
                            </a>
                        </li>
                    @endif
                    @if($canViewComplianceSafeties)
                        <li class="slide">
                            <a href="{{ route('admin.compliance-safeties.index') }}" class="side-menu__item {{ $isActive('admin.compliance-safeties.*') }}">
                                الامتثال والسلامة
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif
    @endif
@elseif($u->isEmployee() || $u->isTechnician())
    {{-- الموظفون والفنيون --}}
    <li class="slide__category mt-3">
        <span class="side-menu__label text-muted text-xs opacity-70">إدارة العمليات</span>
    </li>

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
                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                <span class="side-menu__label">المولدات</span>
            </a>
            <ul class="slide-menu child1" style="{{ request()->routeIs('admin.generators.*') ? 'display:block' : '' }}">
                <li class="slide">
                    <a href="{{ route('admin.generators.index') }}" class="side-menu__item {{ $isActive('admin.generators.index') }}">
                        قائمة المولدات
                    </a>
                </li>
                @can('create', App\Models\Generator::class)
                    <li class="slide">
                        <a href="{{ route('admin.generators.create') }}" class="side-menu__item {{ $isActive('admin.generators.create') }}">
                            إضافة مولد جديد
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcan

    {{-- السجلات --}}
    @php
        $canViewOperationLogs = auth()->user()->can('viewAny', App\Models\OperationLog::class);
        $canViewFuelEfficiencies = auth()->user()->can('viewAny', App\Models\FuelEfficiency::class);
        $canViewMaintenanceRecords = auth()->user()->can('viewAny', App\Models\MaintenanceRecord::class);
        $canViewComplianceSafeties = auth()->user()->can('viewAny', App\Models\ComplianceSafety::class);
        $canViewRecords = $canViewOperationLogs || $canViewFuelEfficiencies || $canViewMaintenanceRecords || $canViewComplianceSafeties;
        $isRecordsOpen = $isOpen('admin.operation-logs.*') || $isOpen('admin.fuel-efficiencies.*') || $isOpen('admin.maintenance-records.*') || $isOpen('admin.compliance-safeties.*');
        $isRecordsActive = $isActive('admin.operation-logs.*') || $isActive('admin.fuel-efficiencies.*') || $isActive('admin.maintenance-records.*') || $isActive('admin.compliance-safeties.*');
    @endphp
    @if($canViewRecords)
        <li class="slide has-sub {{ $isRecordsOpen ? 'open' : '' }}">
            <a href="javascript:void(0);" class="side-menu__item {{ $isRecordsActive ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                <span class="side-menu__label">السجلات</span>
            </a>
            <ul class="slide-menu child1" style="{{ ($show('admin.operation-logs.*') || $show('admin.fuel-efficiencies.*') || $show('admin.maintenance-records.*') || $show('admin.compliance-safeties.*')) ? 'display:block' : '' }}">
                @if($canViewOperationLogs)
                    <li class="slide">
                        <a href="{{ route('admin.operation-logs.index') }}" class="side-menu__item {{ $isActive('admin.operation-logs.*') }}">
                            سجلات التشغيل
                        </a>
                    </li>
                @endif
                @if($canViewFuelEfficiencies)
                    <li class="slide">
                        <a href="{{ route('admin.fuel-efficiencies.index') }}" class="side-menu__item {{ $isActive('admin.fuel-efficiencies.*') }}">
                            كفاءة الوقود
                        </a>
                    </li>
                @endif
                @if($canViewMaintenanceRecords)
                    <li class="slide">
                        <a href="{{ route('admin.maintenance-records.index') }}" class="side-menu__item {{ $isActive('admin.maintenance-records.*') }}">
                            سجلات الصيانة
                        </a>
                    </li>
                @endif
                @if($canViewComplianceSafeties)
                    <li class="slide">
                        <a href="{{ route('admin.compliance-safeties.index') }}" class="side-menu__item {{ $isActive('admin.compliance-safeties.*') }}">
                            الامتثال والسلامة
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif
@endif
