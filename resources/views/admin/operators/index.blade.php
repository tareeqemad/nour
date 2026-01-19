@extends('layouts.admin')

@section('title', auth()->user()->isSuperAdmin() ? 'إدارة المشغلين' : 'المشغل')
@php
    $breadcrumbTitle = auth()->user()->isSuperAdmin() ? 'إدارة المشغلين' : 'المشغل';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/operators.css') }}">
@endpush

@section('content')
<div class="operators-page">

    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
        <div class="general-page" id="operatorsPage">
            <div class="row g-3">
                <div class="col-12">
                    <div class="general-card">
                        <div class="general-card-header">
                            <div>
                                <h5 class="general-title">
                                    <i class="bi bi-buildings me-2"></i>
                                    إدارة المشغلين
                                </h5>
                                <div class="general-subtitle">
                                    إدارة المشغلين والموظفين التابعين لهم.
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                            </div>
                        </div>

                        <div class="card-body pb-4">
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
                                            <i class="bi bi-building me-1"></i>
                                            اسم المشغل
                                        </label>
                                        <input type="text" id="opNameFilter" class="form-control"
                                               placeholder="ابحث باسم المشغل..."
                                               value="{{ request('name') ?? '' }}" autocomplete="off">
                                    </div>

                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-funnel me-1"></i>
                                            الحالة
                                        </label>
                                        <select id="opStatus" class="form-select">
                                            <option value="">كل الحالات</option>
                                            <option value="active" {{ (request('status') ?? '') === 'active' ? 'selected' : '' }}>فعّال</option>
                                            <option value="inactive" {{ (request('status') ?? '') === 'inactive' ? 'selected' : '' }}>غير فعّال</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-primary" id="btnOpSearch">
                                                <i class="bi bi-search me-1"></i>
                                                بحث
                                            </button>
                                            <button class="btn btn-outline-secondary" id="btnOpResetFilters" title="تفريغ الحقول">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                تفريغ الحقول
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- list (body + footer pagination) --}}
                        <div class="position-relative" id="operatorsListWrap">
                            {{-- Loading overlay --}}
                            <div class="op-loading d-none" id="opLoading">
                                <div class="text-center">
                                    <div class="spinner-border" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>
                            @include('admin.operators.partials.list', ['operators' => $operators])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        {{-- Modal: Create/Edit --}}
        <div class="modal fade" id="operatorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content op-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="operatorModalTitle">...</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="operatorModalBody">
                        <div class="py-5 text-center text-muted">
                            <div class="spinner-border" role="status"></div>
                            <div class="mt-2">جاري التحميل...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Confirm Delete --}}
        <div class="modal fade" id="operatorDeleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content op-modal">
                    <div class="modal-header">
                        <h5 class="modal-title">تأكيد الحذف</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-muted">هل أنت متأكد من حذف المشغل:</div>
                        <div class="fw-bold mt-1" id="deleteOpName">—</div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button class="btn btn-danger" id="confirmDeleteOperatorBtn">
                            <i class="bi bi-trash me-1"></i>
                            حذف
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- CompanyOwner / Employee / Technician --}}
        <div class="general-page">
            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <div class="general-card">
                        <div class="general-card-header">
                            <div>
                                <h5 class="general-title">
                                    <i class="bi bi-building me-2"></i>
                                    مشغلي
                                </h5>
                                <div class="general-subtitle">
                                    عرض سريع + روابط للإدارة حسب صلاحياتك.
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                        @if($myOperator)
                            <div class="op-kv">
                                <div class="k">اسم المشغل</div>
                                <div class="v">{{ $myOperator->unit_name ?? $myOperator->name }}</div>
                            </div>
                            <div class="op-kv">
                                <div class="k">المحافظة</div>
                                <div class="v">{{ $myOperator->getGovernorateLabel() ?? '—' }}</div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <span class="badge bg-info">
                                    <i class="bi bi-building me-1"></i>
                                    {{ $myOperator->generation_units_count ?? $myOperator->generationUnits()->count() }} وحدة توليد
                                </span>
                                <span class="badge bg-success">
                                    <i class="bi bi-people me-1"></i>
                                    {{ $myOperator->employees_count ?? $myOperator->users()->count() }} موظف/فني
                                </span>

                                @if($myOperator->profile_completed)
                                    <span class="badge bg-primary">الملف مكتمل</span>
                                @else
                                    <span class="badge bg-warning text-dark">الملف غير مكتمل</span>
                                @endif
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                @if(auth()->user()->isCompanyOwner())
                                    <a class="btn btn-primary" href="{{ route('admin.operators.profile') }}">
                                        <i class="bi bi-ui-checks-grid me-1"></i>
                                        إكمال/تعديل بيانات المشغل
                                    </a>
                                @endif

                                @can('viewAny', App\Models\User::class)
                                    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people me-1"></i>
                                        إدارة الموظفين
                                    </a>
                                @endcan

                                @can('viewAny', App\Models\Generator::class)
                                    <a class="btn btn-outline-secondary" href="{{ route('admin.generators.index') }}">
                                        <i class="bi bi-lightning me-1"></i>
                                        المولدات
                                    </a>
                                @endcan
                            </div>
                        @else
                            <div class="text-muted text-center py-4">
                                لا يوجد مشغل مرتبط بحسابك.
                            </div>
                        @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="general-card">
                        <div class="general-card-header">
                            <div>
                                <h5 class="general-title">
                                    <i class="bi bi-info-circle me-2"></i>
                                    معلومات المشغل
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                        @if($myOperator)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">الاسم</div>
                                        <div class="v">{{ $myOperator->unit_name ?? $myOperator->name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">البريد</div>
                                        <div class="v">{{ $myOperator->email ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">الهاتف</div>
                                        <div class="v">{{ $myOperator->phone ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="op-kv">
                                        <div class="k">الحالة</div>
                                        <div class="v">
                                            @if($myOperator->status === 'active')
                                                <span class="badge bg-success">فعّال</span>
                                            @elseif($myOperator->status === 'inactive')
                                                <span class="badge bg-secondary">غير فعّال</span>
                                            @else
                                                <span class="badge bg-light text-dark">غير محدد</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted text-center py-5">
                                —
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    // ====== Toast helper (Bootstrap Toast عبر adminNotifications) ======
    function notify(type, msg, title) {
        if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
            window.adminNotifications[type](msg, title);
            return;
        }
        // fallback بسيط
        alert(msg);
    }

    // ====== SuperAdmin AJAX only ======
    const isSuperAdmin = @json(auth()->user()->isSuperAdmin());
    if (!isSuperAdmin) return;

    const listUrl = @json(route('admin.operators.index'));
    const modalEl = document.getElementById('operatorModal');
    const modal = new bootstrap.Modal(modalEl);
    const deleteModalEl = document.getElementById('operatorDeleteModal');
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    let deleteUrl = null;

    const $wrap = $('#operatorsListWrap');
    const $loading = $('#opLoading');

    function setLoading(on) {
        $loading.toggleClass('d-none', !on);
    }

    function debounce(fn, ms) {
        let t;
        return function () {
            clearTimeout(t);
            const args = arguments;
            t = setTimeout(() => fn.apply(this, args), ms);
        }
    }

    function currentParams(extra = {}) {
        return Object.assign({
            name: $('#opNameFilter').val() || '',
            status: $('#opStatus').val() || '',
        }, extra);
    }

    function loadList(extra = {}) {
        setLoading(true);
        $.ajax({
            url: listUrl,
            method: 'GET',
            data: currentParams(Object.assign({ ajax: 1 }, extra)),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                if (res && res.success) {
                    $wrap.html(res.html);
                    wireListEvents(); // re-bind
                } else {
                    notify('error', 'فشل تحميل البيانات');
                }
            },
            error: function () {
                notify('error', 'حدث خطأ أثناء تحميل البيانات');
            },
            complete: function () {
                setLoading(false);
            }
        });
    }


    // ========== Modal load (create/edit) ==========
    function openOperatorModal(url) {
        $('#operatorModalTitle').text('...');
        $('#operatorModalBody').html(`
            <div class="py-5 text-center text-muted">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">جاري التحميل...</div>
            </div>
        `);
        modal.show();

        $.ajax({
            url: url,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#operatorModalBody').html(html);
                const title = $('#operatorModalBody').find('[data-modal-title]').attr('data-modal-title') || 'المشغل';
                $('#operatorModalTitle').text(title);
                initOperatorFormAjax();
            },
            error: function () {
                $('#operatorModalBody').html(`<div class="alert alert-danger mb-0">تعذر تحميل النموذج</div>`);
            }
        });
    }

    function clearFormErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
    }

    function applyFormErrors($form, errors) {
        // errors: {field: [msg]}
        Object.keys(errors || {}).forEach(function (field) {
            const msg = errors[field][0];
            const $input = $form.find(`[name="${field}"]`);
            if ($input.length) {
                $input.addClass('is-invalid');
                $input.after(`<div class="invalid-feedback">${msg}</div>`);
            }
        });
    }

    function initOperatorFormAjax() {
        const $form = $('#operatorModalBody').find('form[data-ajax-form="operator"]');
        if (!$form.length) return;

        // toggle password
        $form.on('click', '[data-toggle-pass]', function () {
            const $inp = $form.find('#op_password');
            const $icn = $(this).find('i');
            if ($inp.attr('type') === 'password') {
                $inp.attr('type', 'text');
                $icn.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                $inp.attr('type', 'password');
                $icn.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // send_email enable/disable
        $form.on('input', '#op_email', function () {
            const has = ($(this).val() || '').trim().length > 0;
            const $send = $form.find('#op_send_email');
            if (!has) $send.prop('checked', false);
            $send.prop('disabled', !has);
        }).trigger('input');

        // submit ajax
        $form.on('submit', function (e) {
            e.preventDefault();
            clearFormErrors($form);

            const $btn = $form.find('[data-submit-btn]');
            $btn.prop('disabled', true).addClass('disabled');

            const fd = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (res) {
                    if (res && res.success) {
                        notify('success', res.message || 'تم الحفظ');
                        modal.hide();
                        loadList({ page: 1 });
                    } else {
                        notify('error', res.message || 'فشل الحفظ');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        const json = xhr.responseJSON || {};
                        applyFormErrors($form, json.errors || {});
                        notify('error', 'تحقق من الحقول المطلوبة');
                        return;
                    }
                    notify('error', 'حدث خطأ غير متوقع');
                },
                complete: function () {
                    $btn.prop('disabled', false).removeClass('disabled');
                }
            });
        });
    }

    // ========== Delete ==========
    function askDelete(name, url) {
        deleteUrl = url;
        $('#deleteOpName').text(name || '—');
        deleteModal.show();
    }

    $('#confirmDeleteOperatorBtn').on('click', function () {
        if (!deleteUrl) return;

        const $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: deleteUrl,
            method: 'POST',
            data: { _method: 'DELETE', _token: document.querySelector('meta[name="csrf-token"]').content },
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function (res) {
                if (res && res.success) {
                    notify('success', res.message || 'تم الحذف');
                    deleteModal.hide();
                    loadList();
                } else {
                    notify('error', res.message || 'فشل الحذف');
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'تعذر الحذف';
                notify('error', msg);
            },
            complete: function () {
                $btn.prop('disabled', false);
                deleteUrl = null;
            }
        });
    });

    // ========== Bind list events ==========
    function wireListEvents() {
        // pagination via ajax
        $wrap.find('.pagination a').off('click').on('click', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (!url) return;
            const u = new URL(url, window.location.origin);
            const page = u.searchParams.get('page') || 1;
            loadList({ page: page });
        });

        // edit modal - handle both links and dropdown items
        $wrap.find('[data-action="edit-operator"]').off('click').on('click', function (e) {
            e.preventDefault();
            const url = $(this).data('url') || $(this).attr('href');
            if (url) {
                openOperatorModal(url);
            }
        });

        // delete
        $wrap.find('[data-action="delete-operator"]').off('click').on('click', function () {
            askDelete($(this).data('name'), $(this).data('url'));
        });

        // toggle status
        $wrap.find('[data-action="toggle-status-operator"]').off('click').on('click', function () {
            const $btn = $(this);
            const url = $btn.data('url');
            const currentStatus = $btn.data('status');
            const action = currentStatus === 'active' ? 'إيقاف' : 'تفعيل';
            
            if (!confirm(`هل أنت متأكد من ${action} هذا المشغل؟`)) {
                return;
            }

            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (res) {
                    if (res.success) {
                        notify('success', res.message);
                        loadList({ page: 1 });
                        // تحديث فوري للإشعارات والرسائل
                        if (window.notificationPanel) window.notificationPanel.loadNotifications();
                        if (window.MessagesPanel) {
                            window.MessagesPanel.loadUnreadCount();
                            window.MessagesPanel.loadRecentMessages();
                        }
                    } else {
                        notify('error', res.message || 'حدث خطأ');
                    }
                },
                error: function () {
                    notify('error', 'حدث خطأ أثناء تغيير الحالة');
                }
            });
        });

        // toggle approval
        $wrap.find('[data-action="toggle-approval-operator"]').off('click').on('click', function () {
            const $btn = $(this);
            const url = $btn.data('url');
            const currentApproved = $btn.data('approved') === '1';
            const action = currentApproved ? 'إلغاء اعتماد' : 'اعتماد';
            
            if (!confirm(`هل أنت متأكد من ${action} هذا المشغل؟`)) {
                return;
            }

            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (res) {
                    if (res.success) {
                        notify('success', res.message);
                        loadList({ page: 1 });
                        // تحديث فوري للإشعارات والرسائل
                        if (window.notificationPanel) window.notificationPanel.loadNotifications();
                        if (window.MessagesPanel) {
                            window.MessagesPanel.loadUnreadCount();
                            window.MessagesPanel.loadRecentMessages();
                        }
                    } else {
                        notify('error', res.message || 'حدث خطأ');
                    }
                },
                error: function () {
                    notify('error', 'حدث خطأ أثناء تغيير الاعتماد');
                }
            });
        });
    }

    // ========== UI events ==========
    // Search on button click
    $('#btnOpSearch').on('click', function () {
        loadList({ page: 1 });
    });

    // Reset filters
    $('#btnOpResetFilters').on('click', function () {
        $('#opNameFilter').val('');
        $('#opStatus').val('').trigger('change');
        loadList({ page: 1 });
    });
    
    // Search on Enter key in filter fields
    $('#opNameFilter').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            loadList({ page: 1 });
        }
    });

    // Toggle clear button visibility
    function toggleClearBtn() {
        const hasValue = $('#opNameFilter').val().trim() !== '' || 
                        $('#opStatus').val() !== '';
        $('#btnOpResetFilters').toggleClass('d-none', !hasValue);
    }

    // Update clear button visibility on input change
    $('#opNameFilter').on('input', toggleClearBtn);
    $('#opStatus').on('change', toggleClearBtn);

    // initial
    wireListEvents();
    toggleClearBtn();

})();
</script>
@endpush
