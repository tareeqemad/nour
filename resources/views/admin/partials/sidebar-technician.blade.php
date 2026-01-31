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

{{-- إدارة بيانات المشتركين --}}
<li class="slide__category mt-3">
    <span class="side-menu__label text-muted text-xs opacity-70">إدارة المشتركين</span>
</li>

@can('viewAny', App\Models\Subscriber::class)
    <li class="slide {{ $isActive('admin.subscribers.*') }}">
        <a href="{{ route('admin.subscribers.index') }}" class="side-menu__item">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
            <span class="side-menu__label">بيانات المشتركين</span>
        </a>
    </li>
@endcan

@can('viewAny', App\Models\MeterReading::class)
    <li class="slide has-sub {{ $isOpen('admin.meter-readings.*') }}">
        <a href="javascript:void(0);" class="side-menu__item {{ $isActive('admin.meter-readings.*') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__angle" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.28-.87-2.59-2.86-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.39-2.1 1.39-1.6 0-2.23-.72-2.32-1.64H8.04c.1 1.7 1.36 2.66 2.86 2.97V19h2.34v-1.67c1.52-.29 2.72-1.16 2.72-2.81 0-1.81-1.49-2.69-3.65-3.21z"/></svg>
            <span class="side-menu__label">قراءات العدادات</span>
        </a>
        <ul class="slide-menu child1" style="{{ $show('admin.meter-readings.*') }}">
            <li class="slide">
                <a href="{{ route('admin.meter-readings.index') }}" class="side-menu__item {{ $isActive('admin.meter-readings.index') }}">
                    قائمة القراءات
                </a>
            </li>
            @can('create', App\Models\MeterReading::class)
                <li class="slide">
                    <a href="{{ route('admin.meter-readings.create') }}" class="side-menu__item {{ $isActive('admin.meter-readings.create') }}">
                        إضافة قراءة جديدة
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
