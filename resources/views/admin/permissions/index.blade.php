@extends('layouts.admin')

@section('title', 'شجرة الصلاحيات')

@php
    $breadcrumbTitle = 'شجرة الصلاحيات';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/permissions.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page permissions-page">
        <div class="row g-3">
            {{-- Sidebar: اختيار الهدف + ملخص --}}
            <div class="col-lg-4">
                <x-admin.card class="perm-sidebar">
                    <x-admin.card-header title="تحديد الهدف" icon="bi-crosshair2" />

                    <div class="card-body">
                        <div id="permAlerts" style="display:none;"></div>

                        @if(auth()->user()->isSuperAdmin())
                            @include('admin.permissions.partials.super-admin-tree', ['systemRoles' => $systemRoles])
                        @elseif(auth()->user()->isAdmin())
                            @include('admin.permissions.partials.admin-tree', ['systemRoles' => $systemRoles])
                        @elseif(auth()->user()->isEnergyAuthority())
                            @include('admin.permissions.partials.energy-authority-tree', ['systemRoles' => $systemRoles])
                        @elseif(auth()->user()->isCompanyOwner())
                            @if($operator)
                                <div class="perm-mini-note mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    المشغل الحالي: <strong>{{ $operator->name }}</strong>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-shield-check me-1"></i>
                                    الدور
                                </label>
                                <select id="roleSelect" class="form-select">
                                    <option value="company_owner" selected>مشغل</option>
                                    @foreach(\App\Models\Role::getAvailableCustomRoles(auth()->user()) as $customRole)
                                        <option value="{{ $customRole->id }}">{{ $customRole->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3" id="userSelectWrapper">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-people me-1"></i>
                                    موظف / فني
                                </label>
                                <select id="userSelect" class="form-select"></select>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                لا تملك صلاحية الوصول لهذه الصفحة.
                            </div>
                        @endif


                        <div class="perm-summary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="fw-bold">
                                    <i class="bi bi-clipboard-data me-2"></i>
                                    ملخص الصلاحيات
                                </div>
                                <span class="badge text-bg-light">
                                تغييرات: <span id="statDirty">0</span>
                            </span>
                            </div>

                            <div class="perm-summary-row">
                                <span class="text-muted">المستخدم:</span>
                                <strong id="selectedUserName">—</strong>
                            </div>

                            <div class="perm-summary-row">
                                <span class="text-muted">الدور:</span>
                                <span id="selectedUserRole" class="badge text-bg-secondary">—</span>
                            </div>

                            <div class="perm-stats">
                                <div class="perm-stat">
                                    <div class="label">من الدور</div>
                                    <div class="value" id="statRole">0</div>
                                </div>
                                <div class="perm-stat">
                                    <div class="label">مباشرة</div>
                                    <div class="value" id="statDirect">0</div>
                                </div>
                                <div class="perm-stat">
                                    <div class="label">ممنوعة</div>
                                    <div class="value" id="statRevoked">0</div>
                                </div>
                                <div class="perm-stat">
                                    <div class="label">النتيجة</div>
                                    <div class="value" id="statEffective">0</div>
                                </div>
                            </div>

                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-success" id="savePermissionsBtn" disabled>
                                <i class="bi bi-save me-2"></i>
                                حفظ التغييرات
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="resetPermissionsBtn" disabled>
                                <i class="bi bi-arrow-counterclockwise me-2"></i>
                                تراجع عن التغييرات
                            </button>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            {{-- Main: الشجرة --}}
            <div class="col-lg-8">
                <x-admin.card>
                    <x-admin.card-header title="شجرة الصلاحيات" icon="bi-diagram-3" class="perm-tree-header">
                        <x-slot:actions>
                            <span class="badge text-bg-secondary fw-normal" id="treeCount">{{ $permissions->flatten()->count() }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="expandAllBtn">
                                <i class="bi bi-arrows-angle-expand me-1"></i>
                                توسيع الكل
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="collapseAllBtn">
                                <i class="bi bi-arrows-angle-contract me-1"></i>
                                طي الكل
                            </button>
                        </x-slot:actions>

                        <div class="perm-toolbar mt-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-7">
                                    <div class="perm-searchbar">
                                        <div class="perm-searchfield">
                                            <i class="bi bi-search perm-search-icon"></i>
                                            <input
                                                type="text"
                                                id="searchInput"
                                                class="form-control perm-search-input"
                                                placeholder="ابحث بالاسم، الوصف، أو الكود..."
                                                value="{{ $search }}"
                                                autocomplete="off"
                                            >
                                            <button 
                                                type="button" 
                                                class="perm-clear-input d-none" 
                                                id="clearSearchInput"
                                                title="مسح البحث"
                                            >
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                        <button class="btn btn-primary perm-search-action" type="button" id="searchBtn">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button
                                            class="btn btn-outline-secondary perm-search-action {{ $search ? '' : 'd-none' }}"
                                            type="button"
                                            id="clearSearchBtn"
                                        >
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تفريغ
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="perm-filters">
                                        <button type="button" class="perm-filter active" data-filter="all">
                                            <i class="bi bi-list-ul me-1"></i>
                                            الكل
                                        </button>
                                        <button type="button" class="perm-filter" data-filter="enabled">
                                            <i class="bi bi-check-circle me-1"></i>
                                            مفعلة
                                        </button>
                                        <button type="button" class="perm-filter" data-filter="disabled">
                                            <i class="bi bi-x-circle me-1"></i>
                                            غير مفعلة
                                        </button>
                                        <button type="button" class="perm-filter" data-filter="revoked">
                                            <i class="bi bi-slash-circle me-1"></i>
                                            ممنوعة
                                        </button>
                                        <button type="button" class="perm-filter" data-filter="role">
                                            <i class="bi bi-shield-check me-1"></i>
                                            من الدور
                                        </button>
                                        <button type="button" class="perm-filter" data-filter="direct">
                                            <i class="bi bi-star me-1"></i>
                                            مباشرة
                                        </button>
                                        <button type="button" class="perm-filter" data-filter="dirty">
                                            <i class="bi bi-pencil-square me-1"></i>
                                            تغييرات
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </x-admin.card-header>

                    <div class="card-body position-relative perm-tree-body">
                        <div id="permissionsLoadingOverlay" class="perm-loading" style="display:none;">
                            <div class="text-center">
                                <div class="spinner-border" role="status"></div>
                                <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                            </div>
                        </div>

                        <div id="permissionsTreeContainer">
                            @include('admin.permissions.partials.permissions-tree', ['permissions' => $permissions, 'search' => $search])
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
    @if(file_exists(public_path('assets/admin/libs/select2/i18n/ar.js')))
        <script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
    @endif

    <script>
        window.PERM = {
            ctx: {
                isSuperAdmin: @json(auth()->user()->isSuperAdmin()),
                isAdmin: @json(auth()->user()->isAdmin()),
                isEnergyAuthority: @json(auth()->user()->isEnergyAuthority()),
                isCompanyOwner: @json(auth()->user()->isCompanyOwner()),
                operatorId: @json($operator?->id),
            },
            rolesMeta: @json($rolesMeta),
            routes: {
                selectOperators: @json(route('admin.permissions.select2.operators')),
                selectUsers: @json(route('admin.permissions.select2.users')),
                selectRoles: @json(route('admin.permissions.select2.roles')),
                selectCustomRoles: @json(route('admin.permissions.select2.custom-roles', ['operator' => '__OPERATOR__'])),
                userPermissions: @json(route('admin.permissions.user.permissions', ['user' => '__USER__'])),
                rolePermissions: @json(route('admin.permissions.role.permissions', ['role' => '__ROLE__'])),
                assign: @json(route('admin.permissions.assign')),
                assignRole: @json(route('admin.permissions.role.assign', ['role' => '__ROLE__'])),
                searchTree: @json(route('admin.permissions.search')),
                availablePermissions: @json(route('admin.permissions.available')),
            }
        };
    </script>

    <script src="{{ asset('assets/admin/js/permissions.js') }}"></script>
@endpush
