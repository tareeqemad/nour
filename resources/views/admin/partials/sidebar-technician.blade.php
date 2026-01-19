{{-- Sidebar للفني --}}
@php
    $u = auth()->user();
    $isActive = fn($routes) => request()->routeIs($routes) ? 'active' : '';
    $isOpen   = fn($routes) => request()->routeIs($routes) ? 'open' : '';
    $show     = fn($routes) => request()->routeIs($routes) ? 'display:block' : '';
@endphp

{{-- المهام --}}
<li class="slide__category mt-3">
    <span class="side-menu__label text-muted text-xs opacity-70">المهام</span>
</li>

@can('viewAny', App\Models\Task::class)
    <li class="slide {{ $isActive('admin.tasks.*') }}">
        <a href="{{ route('admin.tasks.index') }}" class="side-menu__item">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
            <span class="side-menu__label">مهامي</span>
        </a>
    </li>
@endcan

{{-- سجلات الصيانة --}}
<li class="slide__category mt-3">
    <span class="side-menu__label text-muted text-xs opacity-70">سجلات الصيانة</span>
</li>

@can('viewAny', App\Models\MaintenanceRecord::class)
    <li class="slide {{ $isActive('admin.maintenance-records.*') }}">
        <a href="{{ route('admin.maintenance-records.index') }}" class="side-menu__item">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/></svg>
            <span class="side-menu__label">سجلات الصيانة</span>
        </a>
    </li>
@endcan

@can('create', App\Models\MaintenanceRecord::class)
    <li class="slide {{ $isActive('admin.maintenance-records.create') }}">
        <a href="{{ route('admin.maintenance-records.create') }}" class="side-menu__item">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            <span class="side-menu__label">إضافة سجل صيانة</span>
        </a>
    </li>
@endcan

{{-- المولدات (للاطلاع فقط) --}}
@can('viewAny', App\Models\Generator::class)
    <li class="slide__category mt-3">
        <span class="side-menu__label text-muted text-xs opacity-70">المولدات</span>
    </li>

    <li class="slide {{ $isActive('admin.generators.*') }}">
        <a href="{{ route('admin.generators.index') }}" class="side-menu__item">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
            <span class="side-menu__label">المولدات</span>
        </a>
    </li>
@endcan
