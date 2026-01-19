{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')

@php
    $breadcrumbTitle = 'إدارة المستخدمين';

    $isSuperAdmin   = auth()->user()->isSuperAdmin();
    $isCompanyOwner = auth()->user()->isCompanyOwner();

    // Get available roles for filter (system roles + custom roles)
    $availableRoles = $availableRoles ?? [];

    // Separate system roles and custom roles for display
    $systemRoles = [];
    $customRoles = [];

    foreach ($availableRoles as $roleName => $roleData) {
        if (isset($roleData['is_custom']) && $roleData['is_custom']) {
            $customRoles[$roleName] = $roleData;
        } else {
            $systemRoles[$roleName] = $roleData;
        }
    }

    // System roles are already sorted by order from database
    $sortedSystemRoles = $systemRoles;

    // Meta for roles (JS uses it to render badges + labels) - includes all roles
    $roleMeta = array_merge($sortedSystemRoles, $customRoles);

    // Roles allowed in create modal (for backward compatibility)
    $createRoleKeys = $isSuperAdmin ? ['company_owner','admin','employee','technician'] : ['employee','technician'];
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/users.css') }}">
@endpush

@section('content')
<div class="general-page"
     id="usersPage"
     data-index-url="{{ route('admin.users.index') }}"
     data-users-base-url="{{ route('admin.users.index') }}"
     data-operators-search-url="{{ route('admin.users.ajaxOperators') }}"
     data-is-super-admin="{{ $isSuperAdmin ? 1 : 0 }}"
     data-is-company-owner="{{ $isCompanyOwner ? 1 : 0 }}"
     data-is-energy-authority="{{ auth()->user()->isEnergyAuthority() ? 1 : 0 }}">

    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">

                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-people me-2"></i>
                            إدارة المستخدمين
                        </h5>
                        <div class="general-subtitle">
                            إدارة الحسابات حسب الصلاحيات والارتباط بالمشغل.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('create', App\Models\User::class)
                            <button type="button" class="btn btn-primary" id="btnOpenCreate">
                                <i class="bi bi-person-plus me-1"></i>
                                إضافة مستخدم
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body pb-4">
                    @if($isCompanyOwner)
                        <div class="users-note mb-3">
                            <i class="bi bi-shield-lock me-1"></i>
                            يمكنك إضافة مستخدمين جدد بأدوار مخصصة (<strong>فني مشغل</strong> و <strong>محاسب</strong>) وإدارة المستخدمين التابعين لمشغّلك.
                        </div>
                    @endif


                    {{-- كارد واحد للفلاتر --}}
                    <div class="filter-card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <i class="bi bi-funnel me-2"></i>
                                فلاتر البحث
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person me-1"></i>
                                        الاسم
                                    </label>
                                    <input type="text" class="form-control" id="nameFilter" placeholder="ابحث بالاسم..." autocomplete="off">
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge me-1"></i>
                                        اسم المستخدم
                                    </label>
                                    <input type="text" class="form-control" id="usernameFilter" placeholder="ابحث باسم المستخدم..." autocomplete="off">
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-envelope me-1"></i>
                                        البريد الإلكتروني
                                    </label>
                                    <input type="text" class="form-control" id="emailFilter" placeholder="ابحث بالبريد الإلكتروني..." autocomplete="off">
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-shield-check me-1"></i>
                                        الدور
                                    </label>
                                    <select class="form-select" id="roleFilter">
                                        <option value="">الكل</option>
                                        @if(!empty($sortedSystemRoles))
                                            <optgroup label="الأدوار النظامية">
                                                @foreach($sortedSystemRoles as $roleName => $roleData)
                                                    <option value="{{ $roleName }}">{{ $roleData['label'] ?? $roleName }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if(!empty($customRoles))
                                            <optgroup label="الأدوار المخصصة">
                                                @foreach($customRoles as $roleName => $roleData)
                                                    <option value="{{ $roleName }}">{{ $roleData['label'] ?? $roleName }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>

                                @if($isSuperAdmin)
                                    <div class="col-lg-4" id="operatorFilterWrap" style="display: none;">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building me-1"></i>
                                            المشغل
                                        </label>
                                        <select class="form-select" id="operatorFilter">
                                            <option value="">اختر المشغل</option>
                                        </select>
                                        <div class="form-text small">عند اختيار مشغل، سيتم عرض المشغل + موظفيه وفنييه.</div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary" id="btnSearch">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button class="btn btn-outline-secondary" id="btnResetFilters" title="تفريغ الحقول">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تفريغ الحقول
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="position-relative" id="usersTableContainer">
                        {{-- Loading overlay --}}
                        <div id="usersLoading" class="d-none" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.9); z-index: 10; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 users-table">
                                <thead class="table-primary">
                                <tr>
                                    <th style="min-width:60px;" class="text-center text-nowrap">#</th>
                                    <th style="min-width:220px;" class="text-nowrap">الاسم</th>
                                    <th style="min-width:120px;" class="text-nowrap">رقم الجوال</th>
                                    <th class="text-center text-nowrap" style="min-width:120px;">الدور</th>
                                    <th class="text-center text-nowrap" style="min-width:150px;">المشغل</th>
                                    <th class="text-center text-nowrap" style="min-width:100px;">عدد الموظفين</th>
                                    <th class="text-center text-nowrap" style="min-width:100px;">الصلاحيات</th>
                                    <th class="text-center text-nowrap" style="min-width:100px;">الحالة</th>
                                    <th style="min-width:80px;" class="text-center text-nowrap">الإجراءات</th>
                                </tr>
                                </thead>
                                <tbody id="usersTbody">
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state text-center py-4">
                                                <div class="text-muted">لا توجد بيانات</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                        <div class="small text-muted" id="usersMeta">—</div>
                        <nav>
                            <ul class="pagination mb-0" id="usersPagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create User Modal --}}
    @can('create', App\Models\User::class)
        <div class="modal fade" id="userCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-person-plus me-1"></i>
                            إضافة مستخدم جديد
                        </h5>
                        <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        @include('admin.users.partials.modal-form', [
                            'mode' => 'create',
                            'user' => null,
                            'roles' => collect($rolesForCreate ?? []),
                            'defaultRole' => '',
                            'operatorLocked' => $operatorLocked ?? null,
                            'operators' => $operators ?? collect(),
                            'selectedOperator' => null,
                        ])
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Delete confirm --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-trash3 me-1"></i>
                        تأكيد الحذف
                    </h5>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="text-muted">هل أنت متأكد من حذف المستخدم:</div>
                    <div class="fw-bold mt-2" id="deleteUserName">—</div>
                    <div class="text-danger small mt-2">هذا الإجراء لا يمكن التراجع عنه.</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="deleteSpinner"></span>
                        حذف
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Suspend User Modal --}}
    <div class="modal fade" id="suspendUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-x-circle-fill me-1"></i>
                        تعطيل/حظر مستخدم
                    </h5>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="text-muted mb-3">هل أنت متأكد من تعطيل/حظر المستخدم:</div>
                    <div class="fw-bold mb-3" id="suspendUserName">—</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            سبب التعطيل/الحظر <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="suspendReason" 
                            rows="4" 
                            placeholder="أدخل سبب التعطيل/الحظر (مطلوب)"
                            maxlength="1000"
                            required></textarea>
                        <div class="form-text">الحد الأقصى 1000 حرف</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmSuspend">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="suspendSpinner"></span>
                        تعطيل/حظر
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {{-- Select2 --}}
    <script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
    @if(file_exists(public_path('assets/admin/libs/select2/i18n/ar.js')))
        <script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
    @endif

    <script>
        (function () {
            const ROLE_META = @json($roleMeta);

            const $page = $('#usersPage');
            const INDEX_URL = $page.data('index-url');
            const USERS_BASE_URL = $page.data('users-base-url'); // /admin/users
            const OPERATORS_SEARCH_URL = $page.data('operators-search-url'); // /admin/operators
            const IS_SUPER_ADMIN = parseInt($page.data('is-super-admin'), 10) === 1;

            // Toast helper (uses your notifications.js)
            function notify(type, message){
                if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
                    window.adminNotifications[type](message);
                    return;
                }
                // fallback
                if(type === 'error') console.error(message);
                else console.log(message);
            }

            function escapeHtml(str){
                return String(str ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function debounce(fn, wait){
                let t;
                return function(...args){
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), wait);
                }
            }

            // CSRF for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const state = {
                name: '',
                username: '',
                email: '',
                role: '',
                operator_id: 0,
                page: 1,
            };

            const $container = $('#usersTableContainer');
            const $tbody = $('#usersTbody');
            const $meta = $('#usersMeta');
            const $pagination = $('#usersPagination');

            const $statTotal = $('#statTotal');
            const $statOwners = $('#statOwners');
            const $statAdmins = $('#statAdmins');
            const $statEmployees = $('#statEmployees');
            const $statTechnicians = $('#statTechnicians');

            function setLoading(on){
                const $loading = $('#usersLoading');
                if(on) {
                    $loading.removeClass('d-none');
                } else {
                    $loading.addClass('d-none');
                }
            }

            function roleBadge(roleKey, roleLabelFromServer){
                const meta = ROLE_META[roleKey] || {label: roleLabelFromServer || roleKey, badge: ''};
                const label = meta.label || roleLabelFromServer || roleKey;
                const cls = meta.badge ? meta.badge : 'badge-soft';
                return `<span class="badge-soft ${escapeHtml(cls)}">${escapeHtml(label)}</span>`;
            }

            function renderEmpty(text){
                $tbody.html(`
                    <tr>
                        <td colspan="9">
                            <div class="empty-state text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted mb-0">${escapeHtml(text || 'لا يوجد نتائج')}</p>
                            </div>
                        </td>
                    </tr>
                `);
            }

            function renderRows(rows){
                if(!rows || !rows.length){
                    renderEmpty('لا يوجد نتائج مطابقة');
                    return;
                }

                // الحصول على رقم الصفحة الحالية لحساب الرقم الترتيبي
                const currentPage = state.page || 1;
                const perPage = 15; // عدد العناصر في الصفحة الواحدة
                const startNumber = (currentPage - 1) * perPage;

                const html = rows.map((u, index) => {
                    // حساب الرقم الترتيبي
                    const rowNumber = startNumber + index + 1;
                    const name = u.name || '-';
                    const username = u.username || '-';
                    const email = u.email || '-';
                    const phone = u.phone || '';
                    const roleKey = u.role || u.role_key || '';
                    const roleLabel = u.role_label || '';
                    const operatorName = u.operator || u.operator_name || (u.operator && u.operator.name) || '';
                    const employeesCount = (u.employees_count !== undefined && u.employees_count !== null) ? u.employees_count : '-';
                    const userStatus = u.status || 'active';

                    const initials = (name && name !== '-') ? name.trim().charAt(0) : '?';

                    const showUrl = (u.urls && u.urls.show) ? u.urls.show : `${USERS_BASE_URL}/${u.id}`;
                    const editUrl = (u.urls && u.urls.edit) ? u.urls.edit : `${USERS_BASE_URL}/${u.id}/edit`;
                    const permsUrl = (u.urls && u.urls.permissions) ? u.urls.permissions : `${USERS_BASE_URL}/${u.id}/permissions`;
                    // رابط ملف المشغل (للمشغل فقط)
                    const operatorProfileUrl = (roleKey === 'company_owner' && u.operator_id) 
                        ? `{{ route('admin.operators.profile') }}` 
                        : null;

                    // can flags (optional) - if not provided, show buttons and let server protect
                    const canView = (u.can && u.can.view !== undefined) ? !!u.can.view : true;
                    const canEdit = (u.can && u.can.update !== undefined) ? !!u.can.update : true;
                    const canDelete = (u.can && u.can.delete !== undefined) ? !!u.can.delete : true;
                    const isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};
                    const currentUserId = parseInt({{ auth()->id() }}, 10);
                    const targetUserId = parseInt(u.id, 10);

                    const operatorCell = operatorName
                        ? `<span class="badge-soft">${escapeHtml(operatorName)}</span>`
                        : `<span class="text-muted">-</span>`;

                    const employeesCell = (roleKey === 'company_owner')
                        ? `<span class="fw-bold">${escapeHtml(employeesCount)}</span>`
                        : `<span class="text-muted">-</span>`;

                    const phoneCell = phone 
                        ? `<a href="tel:${escapeHtml(phone)}" class="text-decoration-none">${escapeHtml(phone)}</a>`
                        : `<span class="text-muted">-</span>`;

                    // Permissions cell - عرض عدد الصلاحيات
                    const permsCount = u.permissions_count ?? 0;
                    const permsInfo = u.permissions_info || {};
                    let permissionsCell = '';
                    
                    if (permsCount === 'الكل' || permsInfo.type === 'all') {
                        permissionsCell = `<span class="badge bg-success" title="لديه جميع الصلاحيات">
                            <i class="bi bi-shield-check me-1"></i>
                            الكل
                        </span>`;
                    } else {
                        const count = typeof permsCount === 'number' ? permsCount : 0;
                        const roleCount = permsInfo.role_count || 0;
                        const directCount = permsInfo.direct_count || 0;
                        const revokedCount = permsInfo.revoked_count || 0;
                        
                        let tooltipText = `عدد الصلاحيات: ${count}`;
                        if (roleCount > 0) {
                            tooltipText += `\nمن الدور: ${roleCount}`;
                        }
                        if (directCount > 0) {
                            tooltipText += `\nمباشرة: ${directCount}`;
                        }
                        if (revokedCount > 0) {
                            tooltipText += `\nملغاة: ${revokedCount}`;
                        }
                        
                        permissionsCell = `<a href="${escapeHtml(permsUrl)}" 
                                class="badge bg-info text-decoration-none" 
                                title="${escapeHtml(tooltipText)}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top">
                            <i class="bi bi-shield-check me-1"></i>
                            ${count}
                        </a>`;
                    }

                    // Status cell with suspended support
                    let statusCell = '';
                    if (userStatus === 'suspended') {
                        statusCell = `<span class="badge bg-danger">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            محظور/معطل
                        </span>`;
                    } else if (userStatus === 'active') {
                        statusCell = `<span class="badge bg-success">فعال</span>`;
                    } else {
                        statusCell = `<span class="badge bg-danger">غير فعال</span>`;
                    }

                    // بناء قائمة الإجراءات بشكل بسيط وواضح
                    const isCompanyOwner = {{ $isCompanyOwner ? 'true' : 'false' }};
                    const canToggleStatus = (isSuperAdmin || isCompanyOwner) && targetUserId !== currentUserId;
                    const canSuspend = isSuperAdmin && targetUserId !== currentUserId && userStatus !== 'suspended';
                    const canUnsuspend = isSuperAdmin && targetUserId !== currentUserId && userStatus === 'suspended';
                    
                    // بناء HTML للقائمة مباشرة - أبسط وأوضح
                    let menuHtml = '';
                    
                    // الإجراءات الأساسية
                    if (canEdit) {
                        menuHtml += `<a class="dropdown-item" href="${escapeHtml(editUrl)}">
                            <i class="bi bi-pencil me-2 text-primary"></i> تعديل المستخدم
                        </a>`;
                    }
                    if (canView) {
                        menuHtml += `<a class="dropdown-item" href="${escapeHtml(showUrl)}">
                            <i class="bi bi-eye me-2 text-info"></i> عرض التفاصيل
                        </a>`;
                    }
                    // عرض عدد الصلاحيات في القائمة (استخدام permsCount الموجود مسبقاً)
                    const permsText = permsCount === 'الكل' || permsCount === 'all' 
                        ? 'إدارة الصلاحيات (الكل)' 
                        : `إدارة الصلاحيات (${permsCount})`;
                    
                    menuHtml += `<a class="dropdown-item" href="${escapeHtml(permsUrl)}">
                        <i class="bi bi-shield-check me-2 text-warning"></i> ${escapeHtml(permsText)}
                    </a>`;
                    
                    // فاصل للإجراءات الإضافية
                    let hasMoreActions = operatorProfileUrl || canToggleStatus || canSuspend || canUnsuspend || 
                                       (isSuperAdmin && targetUserId !== currentUserId) || 
                                       (canDelete && targetUserId !== currentUserId);
                    if (hasMoreActions) {
                        menuHtml += '<hr class="dropdown-divider">';
                    }
                    
                    // ملف المشغل (إن وجد)
                    if (operatorProfileUrl) {
                        menuHtml += `<a class="dropdown-item" href="${escapeHtml(operatorProfileUrl)}">
                            <i class="bi bi-building me-2"></i> ملف المشغل
                        </a>`;
                    }
                    
                    // إجراءات الحالة
                    if (canToggleStatus) {
                        menuHtml += `<button type="button" class="dropdown-item btn-toggle-status"
                                data-id="${escapeHtml(u.id)}"
                                data-status="${escapeHtml(userStatus)}"
                                data-name="${escapeHtml(name)}">
                            <i class="bi bi-${userStatus === 'active' ? 'pause' : 'play'}-fill me-2 text-${userStatus === 'active' ? 'warning' : 'success'}"></i>
                            ${userStatus === 'active' ? 'إيقاف المستخدم' : 'تفعيل المستخدم'}
                        </button>`;
                    }
                    if (canSuspend) {
                        menuHtml += `<button type="button" class="dropdown-item text-danger btn-suspend-user"
                                data-id="${escapeHtml(u.id)}"
                                data-name="${escapeHtml(name)}">
                            <i class="bi bi-x-circle-fill me-2"></i> تعطيل/حظر المستخدم
                        </button>`;
                    }
                    if (canUnsuspend) {
                        menuHtml += `<button type="button" class="dropdown-item text-success btn-unsuspend-user"
                                data-id="${escapeHtml(u.id)}"
                                data-name="${escapeHtml(name)}">
                            <i class="bi bi-check-circle-fill me-2"></i> رفع الحظر
                        </button>`;
                    }
                    
                    // الدخول بحسابه (للسوبر أدمن فقط)
                    if (isSuperAdmin && targetUserId !== currentUserId) {
                        if (canSuspend || canUnsuspend || canToggleStatus) {
                            menuHtml += '<hr class="dropdown-divider">';
                        }
                        menuHtml += `<button type="button" class="dropdown-item text-info btn-impersonate-user"
                                data-id="${escapeHtml(u.id)}"
                                data-name="${escapeHtml(name)}">
                            <i class="bi bi-person-check me-2"></i> الدخول بحسابه
                        </button>`;
                    }
                    
                    // حذف المستخدم (إجراء خطير)
                    if (canDelete && targetUserId !== currentUserId) {
                        menuHtml += '<hr class="dropdown-divider">';
                        menuHtml += `<button type="button" class="dropdown-item text-danger btn-delete-user"
                                data-id="${escapeHtml(u.id)}"
                                data-name="${escapeHtml(name)}">
                            <i class="bi bi-trash me-2"></i> حذف المستخدم
                        </button>`;
                    }
                    
                    // بناء Dropdown Menu مع زر بسيط وواضح
                    const actionsMenu = `
                        <div class="btn-group" role="group">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false" 
                                    title="إجراءات">
                                <span class="visually-hidden">إجراءات</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ${menuHtml}
                            </ul>
                        </div>
                    `;

                    return `
                        <tr>
                            <td class="text-center text-nowrap">
                                <span class="text-muted fw-semibold">${rowNumber}</span>
                            </td>
                            <td class="text-nowrap">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-pill flex-shrink-0">${escapeHtml(initials)}</div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">${escapeHtml(name)}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap">${phoneCell}</td>
                            <td class="text-center text-nowrap">${roleBadge(roleKey, roleLabel)}</td>
                            <td class="text-center text-nowrap">${operatorCell}</td>
                            <td class="text-center text-nowrap">${employeesCell}</td>
                            <td class="text-center text-nowrap">${permissionsCell}</td>
                            <td class="text-center text-nowrap">${statusCell}</td>
                            <td class="text-nowrap">
                                <div class="d-flex justify-content-center">
                                    ${actionsMenu}
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');

                $tbody.html(html);
                
                // تهيئة tooltips للصلاحيات
                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    $tbody.find('[data-bs-toggle="tooltip"]').each(function() {
                        new bootstrap.Tooltip(this);
                    });
                }
            }

            function renderMeta(meta){
                if(!meta || typeof meta.total === 'undefined'){
                    $meta.text('—');
                    return;
                }
                $meta.text(`عرض ${meta.from || 0} - ${meta.to || 0} من ${meta.total || 0}`);
            }

            function renderPagination(meta){
                if(!meta || !meta.last_page || meta.last_page <= 1){
                    $pagination.html('');
                    return;
                }

                const current = meta.current_page || 1;
                const last = meta.last_page || 1;

                const makeItem = (page, label, disabled=false, active=false) => `
                    <li class="page-item ${disabled?'disabled':''} ${active?'active':''}">
                        <a class="page-link" href="#" data-page="${page}">${label}</a>
                    </li>
                `;

                let html = '';
                html += makeItem(current-1, '‹', current <= 1);

                // window pages
                const start = Math.max(1, current - 2);
                const end = Math.min(last, current + 2);

                if(start > 1) html += makeItem(1, '1', false, current === 1);
                if(start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;

                for(let p=start; p<=end; p++){
                    html += makeItem(p, String(p), false, p === current);
                }

                if(end < last - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                if(end < last) html += makeItem(last, String(last), false, current === last);

                html += makeItem(current+1, '›', current >= last);

                $pagination.html(html);
            }

            function renderStats(stats){
                if(!stats) return;

                if($statTotal.length) $statTotal.text(stats.total ?? '—');
                if($statEmployees.length) $statEmployees.text(stats.employees ?? '—');
                if($statTechnicians.length) $statTechnicians.text(stats.technicians ?? '—');

                if($statOwners.length) $statOwners.text(stats.company_owners ?? '—');
                if($statAdmins.length) $statAdmins.text(stats.admins ?? '—');
            }

            function loadUsers(page=1){
                state.page = page;

                setLoading(true);
                $.ajax({
                    url: INDEX_URL,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        ajax: 1,
                        name: state.name,
                        username: state.username,
                        email: state.email,
                        role: state.role,
                        operator_id: state.operator_id,
                        page: state.page,
                    },
                    success: function(resp){
                        // Expected: { ok:true, data:[], meta:{}, stats:{} }
                        if(resp && resp.ok === false){
                            notify('error', resp.message || 'حدث خطأ أثناء جلب البيانات');
                            renderEmpty('تعذر تحميل البيانات');
                            return;
                        }

                        renderRows(resp.data || []);
                        renderMeta(resp.meta || {});
                        renderPagination(resp.meta || {});
                        renderStats(resp.stats || {});
                    },
                    error: function(xhr){
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'تعذر تحميل البيانات';
                        notify('error', msg);
                        renderEmpty('تعذر تحميل البيانات');
                    },
                    complete: function(){
                        setLoading(false);
                    }
                });
            }

            // ===== Filters
            const $nameFilter = $('#nameFilter');
            const $usernameFilter = $('#usernameFilter');
            const $emailFilter = $('#emailFilter');
            const $roleFilter = $('#roleFilter');

            const doSearch = function(){
                state.name = $nameFilter.val().trim();
                state.username = $usernameFilter.val().trim();
                state.email = $emailFilter.val().trim();
                state.role = $roleFilter.val() || '';
                
                if(IS_SUPER_ADMIN){
                    const val = $('#operatorFilter').val();
                    // Select2 قد يعيد '' أو null
                    state.operator_id = (val && val !== '' && val !== null) ? parseInt(val, 10) : 0;
                }
                
                loadUsers(1);
            };

            // Search on button click
            $('#btnSearch').on('click', doSearch);
            
            // Search on Enter key in any filter field
            $nameFilter.add($usernameFilter).add($emailFilter).on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    doSearch();
                }
            });

            $roleFilter.on('change', function(){
                const role = $(this).val() || '';

                // SuperAdmin: إظهار فلتر المشغل فقط عند اختيار دور "مشغل"
                if(IS_SUPER_ADMIN){
                    const shouldShowOperator = (role === 'company_owner');
                    $('#operatorFilterWrap').toggle(shouldShowOperator);

                    if(!shouldShowOperator){
                        try { 
                            $('#operatorFilter').val(null).trigger('change');
                            // إعادة تهيئة select2 إذا كان موجود
                            if($('#operatorFilter').hasClass('select2-hidden-accessible')) {
                                $('#operatorFilter').select2('destroy');
                            }
                        } catch(e){}
                    } else {
                        // إذا كان role = company_owner، نحمل قائمة المشغلين
                        if(!$('#operatorFilter').hasClass('select2-hidden-accessible')) {
                            initOperatorSelect2();
                        }
                    }
                }
                // لا نقوم بتحميل البيانات تلقائياً - فقط عند الضغط على زر البحث
            });

            $('#btnResetFilters').on('click', function(){
                $nameFilter.val('');
                $usernameFilter.val('');
                $emailFilter.val('');
                $roleFilter.val('').trigger('change');

                state.name = '';
                state.username = '';
                state.email = '';
                state.role = '';
                state.operator_id = 0;

                if(IS_SUPER_ADMIN){
                    try { 
                        $('#operatorFilter').val(null).trigger('change');
                        if($('#operatorFilter').hasClass('select2-hidden-accessible')) {
                            $('#operatorFilter').select2('destroy');
                        }
                    } catch(e){}
                    $('#operatorFilterWrap').hide();
                }

                // تفريغ الحقول فقط - لا تحميل تلقائي
                // يمكن للمستخدم الضغط على "بحث" بعد تفريغ الحقول
            });

            // Pagination click
            $pagination.on('click', 'a.page-link', function(e){
                e.preventDefault();
                const p = parseInt($(this).data('page'), 10);
                if(!p || p < 1) return;
                loadUsers(p);
            });

            // ===== Select2: operator filter (super admin)
            function initOperatorSelect2(){
                if(!IS_SUPER_ADMIN) return;
                
                // إذا كان select2 موجود، ندمّره أولاً
                if($('#operatorFilter').hasClass('select2-hidden-accessible')) {
                    $('#operatorFilter').select2('destroy');
                }

                $('#operatorFilter').select2({
                    dir: 'rtl',
                    width: '100%',
                    placeholder: 'ابحث عن مشغل...',
                    allowClear: true,
                    language: 'ar',
                    ajax: {
                        url: OPERATORS_SEARCH_URL,
                        dataType: 'json',
                        delay: 250,
                        data: function(params){
                            return { ajax: 1, term: params.term || '', page: params.page || 1 };
                        },
                        processResults: function(resp, params){
                            // Accept either {results:[{id,text}]} OR {data:[{id,name}], meta:{...}}
                            if(resp && resp.results){
                                return resp;
                            }

                            const items = (resp && resp.data) ? resp.data : [];
                            const results = items.map(o => ({
                                id: o.id,
                                text: o.text || o.name || ('مشغل #' + o.id),
                            }));

                            const more = resp && resp.meta ? (resp.meta.current_page < resp.meta.last_page) : false;
                            return { results: results, pagination: { more: more } };
                        }
                    }
                });

                $('#operatorFilter').on('change', function(){
                    // لا نقوم بتحميل البيانات تلقائياً - فقط عند الضغط على زر البحث
                    // القيمة ستُقرأ في doSearch()
                });
            }

            // لا نبدأ select2 إلا عند الحاجة (عند اختيار دور "مشغل")

            // ===== Create Modal (AJAX)
            @can('create', App\Models\User::class)
                const createModalEl = document.getElementById('userCreateModal');
                const createModal = createModalEl ? new bootstrap.Modal(createModalEl) : null;

                const $createForm = $('#userAjaxForm');
                const $createRole = $('#roleSelect');

                function clearCreateErrors(){
                    $createForm.find('.is-invalid').removeClass('is-invalid');
                    $createForm.find('[data-error-for]').addClass('d-none').text('');
                }

                function setCreateLoading(on){
                    $('#userModalSubmitBtn').prop('disabled', on);
                    if(on) {
                        $('#userModalSubmitBtn').html('<span class="spinner-border spinner-border-sm me-1"></span> جاري الحفظ...');
                    } else {
                        $('#userModalSubmitBtn').html('<i class="bi bi-check-lg me-1"></i> حفظ');
                    }
                }

                function showCreateError(field, msg){
                    const $errorDiv = $(`[data-error-for="${field}"]`);
                    if($errorDiv.length) {
                        $errorDiv.removeClass('d-none').text(msg);
                    }
                    const $input = $createForm.find(`[name="${field}"]`);
                    if($input.length) {
                        $input.addClass('is-invalid');
                    }
                }

                // Select2 for operator inside create modal (super admin and energy authority)
                const IS_ENERGY_AUTHORITY = parseInt($page.data('is-energy-authority'), 10) === 1;
                function initOperatorSelect2InModal(){
                    if(!IS_SUPER_ADMIN && !IS_ENERGY_AUTHORITY) return;
                    
                    const $operatorSelect = $('#operatorSelect');
                    if($operatorSelect.length && !$operatorSelect.hasClass('select2-hidden-accessible')){
                        $operatorSelect.select2({
                            dropdownParent: $('#userCreateModal'),
                            dir: 'rtl',
                            width: '100%',
                            placeholder: 'ابحث عن مشغل...',
                            allowClear: true,
                            language: 'ar',
                            ajax: {
                                url: OPERATORS_SEARCH_URL,
                                dataType: 'json',
                                delay: 250,
                                data: function(params){
                                    return { ajax: 1, term: params.term || '', page: params.page || 1 };
                                },
                                processResults: function(resp){
                                    if(resp && resp.results){
                                        return resp;
                                    }
                                    const items = (resp && resp.data) ? resp.data : [];
                                    const results = items.map(o => ({ id: o.id, text: o.text || o.name || ('مشغل #' + o.id) }));
                                    return { results };
                                }
                            }
                        });
                    }
                }

                $('#btnOpenCreate').on('click', function(){
                    clearCreateErrors();
                    if($createForm.length) $createForm[0].reset();
                    
                    // Reset fields visibility
                    $('#nameField, #phoneField, #operatorField, #companyOwnerWarning').hide();
                    
                    if(createModal) createModal.show();
                });

                $createForm.on('submit', function(e){
                    e.preventDefault();
                    clearCreateErrors();
                    setCreateLoading(true);

                    $.ajax({
                        url: USERS_BASE_URL,
                        method: 'POST',
                        dataType: 'json',
                        data: $createForm.serialize(),
                        success: function(resp){
                            notify('success', resp.message || 'تم إنشاء المستخدم بنجاح');
                            if(createModal) createModal.hide();
                            loadUsers(1);
                        },
                        error: function(xhr){
                            if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
                                const errs = xhr.responseJSON.errors;
                                Object.keys(errs).forEach(k => showCreateError(k, (errs[k] && errs[k][0]) ? errs[k][0] : 'خطأ'));
                                notify('error', 'تحقق من البيانات المدخلة');
                                return;
                            }

                            const msg = (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'تعذر حفظ المستخدم';
                            notify('error', msg);
                        },
                        complete: function(){
                            setCreateLoading(false);
                        }
                    });
                });

                // Initialize operator select2 when modal is shown
                if(createModalEl) {
                    createModalEl.addEventListener('shown.bs.modal', function(){
                        initOperatorSelect2InModal();
                    });
                }
            @endcan

            // ===== Delete (AJAX)
            const deleteModalEl = document.getElementById('deleteUserModal');
            const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
            let pendingDeleteId = null;

            const $deleteName = $('#deleteUserName');
            const $deleteSpinner = $('#deleteSpinner');

            function setDeleteLoading(on){
                $('#btnConfirmDelete').prop('disabled', on);
                $deleteSpinner.toggleClass('d-none', !on);
            }

            // Toggle status handler
            $tbody.on('click', '.btn-toggle-status', function(){
                const $btn = $(this);
                const id = $btn.data('id');
                const currentStatus = $btn.data('status');
                const name = $btn.data('name');
                const action = currentStatus === 'active' ? 'إيقاف' : 'تفعيل';
                
                if (!confirm(`هل أنت متأكد من ${action} المستخدم "${name}"؟`)) {
                    return;
                }

                $.ajax({
                    url: `${USERS_BASE_URL}/${id}/toggle-status`,
                    method: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(resp){
                        if(resp.ok){
                            notify('success', resp.message || `تم ${action} المستخدم بنجاح`);
                            loadUsers(state.page || 1);
                        } else {
                            notify('error', resp.message || 'حدث خطأ');
                        }
                    },
                    error: function(xhr){
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'حدث خطأ أثناء تغيير الحالة';
                        notify('error', msg);
                    }
                });
            });

            $tbody.on('click', '.btn-delete-user', function(){
                const id = $(this).data('id');
                const name = $(this).data('name');

                pendingDeleteId = id;
                $deleteName.text(name || '—');

                if(deleteModal) deleteModal.show();
            });

            // ===== Suspend User (AJAX)
            const suspendModalEl = document.getElementById('suspendUserModal');
            const suspendModal = suspendModalEl ? new bootstrap.Modal(suspendModalEl) : null;
            let pendingSuspendId = null;

            const $suspendName = $('#suspendUserName');
            const $suspendReason = $('#suspendReason');
            const $suspendSpinner = $('#suspendSpinner');

            function setSuspendLoading(on){
                $('#btnConfirmSuspend').prop('disabled', on);
                $suspendReason.prop('disabled', on);
                $suspendSpinner.toggleClass('d-none', !on);
            }

            function clearSuspendModal(){
                $suspendReason.val('').removeClass('is-invalid');
                $suspendReason.next('.invalid-feedback').text('');
                pendingSuspendId = null;
            }

            $tbody.on('click', '.btn-suspend-user', function(){
                const id = $(this).data('id');
                const name = $(this).data('name');

                pendingSuspendId = id;
                $suspendName.text(name || '—');
                clearSuspendModal();

                if(suspendModal) suspendModal.show();
            });

            $('#suspendUserModal').on('hidden.bs.modal', function(){
                clearSuspendModal();
            });

            $('#btnConfirmSuspend').on('click', function(){
                if(!pendingSuspendId) return;

                const reason = $suspendReason.val().trim();
                if(!reason || reason.length < 5){
                    $suspendReason.addClass('is-invalid');
                    $suspendReason.next('.invalid-feedback').text('يجب إدخال سبب التعطيل (5 أحرف على الأقل)');
                    return;
                }

                if(reason.length > 1000){
                    $suspendReason.addClass('is-invalid');
                    $suspendReason.next('.invalid-feedback').text('سبب التعطيل يجب أن لا يتجاوز 1000 حرف');
                    return;
                }

                setSuspendLoading(true);

                $.ajax({
                    url: `${USERS_BASE_URL}/${pendingSuspendId}/suspend`,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        reason: reason
                    },
                    success: function(resp){
                        if(resp.ok){
                            notify('success', resp.message || 'تم تعطيل/حظر المستخدم بنجاح');
                            if(suspendModal) suspendModal.hide();
                            loadUsers(state.page || 1);
                        } else {
                            notify('error', resp.message || 'حدث خطأ');
                        }
                    },
                    error: function(xhr){
                        if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
                            const errs = xhr.responseJSON.errors;
                            if(errs.reason && errs.reason[0]){
                                $suspendReason.addClass('is-invalid');
                                $suspendReason.next('.invalid-feedback').text(errs.reason[0]);
                            }
                            notify('error', 'تحقق من البيانات المدخلة');
                            return;
                        }

                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'تعذر تعطيل/حظر المستخدم';
                        notify('error', msg);
                    },
                    complete: function(){
                        setSuspendLoading(false);
                    }
                });
            });

            // ===== Impersonate User (Form Submit)
            $tbody.on('click', '.btn-impersonate-user', function(){
                const $btn = $(this);
                const id = $btn.data('id');
                const name = $btn.data('name');

                if (!confirm(`هل أنت متأكد من الدخول بحساب "${name}"؟`)) {
                    return;
                }

                // إنشاء form وإرساله
                const form = $('<form>', {
                    method: 'POST',
                    action: `${USERS_BASE_URL}/${id}/impersonate`
                });
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: $('meta[name="csrf-token"]').attr('content')
                }));

                $('body').append(form);
                form.submit();
            });

            // ===== Unsuspend User (AJAX)
            $tbody.on('click', '.btn-unsuspend-user', function(){
                const $btn = $(this);
                const id = $btn.data('id');
                const name = $btn.data('name');

                if (!confirm(`هل أنت متأكد من رفع الحظر عن المستخدم "${name}"؟`)) {
                    return;
                }

                $.ajax({
                    url: `${USERS_BASE_URL}/${id}/unsuspend`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(resp){
                        if(resp.ok){
                            notify('success', resp.message || 'تم رفع الحظر عن المستخدم بنجاح');
                            loadUsers(state.page || 1);
                        } else {
                            notify('error', resp.message || 'حدث خطأ');
                        }
                    },
                    error: function(xhr){
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'تعذر رفع الحظر عن المستخدم';
                        notify('error', msg);
                    }
                });
            });

            $('#btnConfirmDelete').on('click', function(){
                if(!pendingDeleteId) return;

                setDeleteLoading(true);

                $.ajax({
                    url: `${USERS_BASE_URL}/${pendingDeleteId}`,
                    method: 'POST',
                    dataType: 'json',
                    data: { _method: 'DELETE' },
                    success: function(resp){
                        notify('success', resp.message || 'تم حذف المستخدم');
                        if(deleteModal) deleteModal.hide();
                        pendingDeleteId = null;
                        loadUsers(1);
                    },
                    error: function(xhr){
                        const msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'تعذر حذف المستخدم';
                        notify('error', msg);
                    },
                    complete: function(){
                        setDeleteLoading(false);
                    }
                });
            });

            // ===== Init
            loadUsers(1);

        })();
    </script>
@endpush
