@extends('layouts.admin')

@section('title', 'إدارة الأدوار')

@php
    $breadcrumbTitle = 'إدارة الأدوار';
    $isCompanyOwner = auth()->user()->isCompanyOwner();
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $operator = $isCompanyOwner ? auth()->user()->ownedOperators()->first() : null;
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/roles.css') }}">
@endpush

@section('content')
<div class="general-page" id="rolesPage" data-index-url="{{ route('admin.roles.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header :title="$isCompanyOwner ? 'أدوار المستخدمين' : 'إدارة الأدوار'" icon="bi-shield-check">
                    <x-slot:actions>
                        @can('create', App\Models\Role::class)
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة دور جديد
                            </a>
                        @endcan
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body pb-4">
                    @if($isCompanyOwner && $operator)
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle me-2 fs-5 mt-1"></i>
                                <div class="flex-grow-1">
                                    <strong>ملاحظة:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>الأدوار <strong>النظامية</strong> (موظف، فني) هي أدوار أساسية في النظام ويمكنك استخدامها فقط.</li>
                                        <li>يمكنك إنشاء أدوار <strong>مخصصة</strong> لمستخدمي مشغلك وتحديد الصلاحيات المناسبة لهم.</li>
                                        <li>الأدوار المخصصة الخاصة بك تكون مرتبطة بمشغلك فقط.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif


                    {{-- فلاتر البحث --}}
                    <form id="searchForm" method="POST" action="{{ route('admin.roles.filter') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tag me-1"></i>اسم الدور
                                </label>
                                <input type="text" name="name" id="nameFilter" class="form-control"
                                       placeholder="اسم الدور..."
                                       value="{{ session('roles_filter.name', '') }}" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-bookmark me-1"></i>التسمية
                                </label>
                                <input type="text" name="label" id="labelFilter" class="form-control"
                                       placeholder="التسمية..."
                                       value="{{ session('roles_filter.label', '') }}" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-file-text me-1"></i>الوصف
                                </label>
                                <input type="text" name="description" id="descriptionFilter" class="form-control"
                                       placeholder="الوصف..."
                                       value="{{ session('roles_filter.description', '') }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-center gap-2">
                                <button type="submit" class="btn btn-primary" id="btnSearch">
                                    <i class="bi bi-search me-1"></i>بحث
                                </button>
                                <button type="button" class="btn btn-outline-secondary {{ session('roles_filter.name') || session('roles_filter.label') || session('roles_filter.description') ? '' : 'd-none' }}" id="btnResetFilters">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>تفريغ
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="position-relative mt-3" id="rolesTableContainer">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-striped mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th class="text-center fw-semibold" style="width:50px;">#</th>
                                    <th class="fw-semibold">اسم الدور</th>
                                    <th class="fw-semibold">التسمية</th>
                                    <th class="d-none d-md-table-cell fw-semibold">الوصف</th>
                                    @if(auth()->user()->isSuperAdmin())
                                        <th class="text-center fw-semibold">المشغل</th>
                                    @endif
                                    <th class="text-center fw-semibold">المستخدمين</th>
                                    <th class="text-center fw-semibold">الصلاحيات</th>
                                    <th class="text-center fw-semibold">النوع</th>
                                    <th class="text-end fw-semibold">الإجراءات</th>
                                </tr>
                                </thead>
                            <tbody id="rolesTbody">
                                @include('admin.roles.partials.tbody', ['roles' => $roles, 'isSuperAdmin' => $isSuperAdmin, 'isCompanyOwner' => $isCompanyOwner])
                            </tbody>
                        </table>
                    </div>
                    </div> {{-- /rolesTableContainer --}}

                    <div id="rolesPagination">
                        @if($roles->hasPages())
                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                                <div class="small text-muted">
                                    @if($roles->total() > 0)
                                        عرض {{ $roles->firstItem() }} - {{ $roles->lastItem() }} من {{ $roles->total() }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div>
                                    {{ $roles->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div> {{-- /card-body --}}
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function($) {
        $(document).ready(function() {
            const $form = $('#searchForm');
            const $nameFilter = $('#nameFilter');
            const $labelFilter = $('#labelFilter');
            const $descriptionFilter = $('#descriptionFilter');
            const $btnResetFilters = $('#btnResetFilters');
            const $rolesTableContainer = $('#rolesTableContainer');
            const $rolesTbody = $('#rolesTbody');
            const $rolesPagination = $('#rolesPagination');
            const $rolesLoading = $('#rolesLoading');
            const INDEX_URL = '{{ route('admin.roles.index') }}';
            const FILTER_URL = '{{ route('admin.roles.filter') }}';

            // دالة لعرض/إخفاء Loading
            function setLoading(show) {
                if (show) {
                    $rolesLoading.removeClass('d-none').css('display', 'flex');
                } else {
                    $rolesLoading.addClass('d-none');
                }
            }

            // دالة لتحميل البيانات عبر AJAX
            function loadRoles(page = 1) {
                setLoading(true);

                $.ajax({
                    url: INDEX_URL,
                    method: 'GET',
                    data: {
                        page: page,
                        ajax: 1
                    },
                    success: function(response) {
                        if (response.html) {
                            $rolesTbody.html(response.html.tbody || '');
                            $rolesPagination.html(response.html.pagination || '');
                            
                            // إعادة ربط event handlers للـ pagination
                            bindPaginationEvents();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading roles:', xhr);
                        notify('error', 'حدث خطأ أثناء تحميل البيانات');
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            }

            // ربط events للـ pagination
            function bindPaginationEvents() {
                $rolesPagination.on('click', 'a.page-link', function(e) {
                    e.preventDefault();
                    const url = $(this).attr('href');
                    if (!url) return;
                    
                    // استخراج رقم الصفحة من URL
                    const match = url.match(/[?&]page=(\d+)/);
                    const page = match ? parseInt(match[1], 10) : 1;
                    loadRoles(page);
                });
            }

            // إرسال البحث via AJAX POST
            $form.on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    name: $nameFilter.val().trim(),
                    label: $labelFilter.val().trim(),
                    description: $descriptionFilter.val().trim(),
                    _token: $('input[name="_token"]').val()
                };

                setLoading(true);

                $.ajax({
                    url: FILTER_URL,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.html) {
                            $rolesTbody.html(response.html.tbody || '');
                            $rolesPagination.html(response.html.pagination || '');
                            
                            // تحديث زر تفريغ الحقول
                            if (formData.name || formData.label || formData.description) {
                                if (!$btnResetFilters.length) {
                                    $('#btnSearch').after('<button type="button" class="btn btn-outline-secondary" id="btnResetFilters"><i class="bi bi-arrow-counterclockwise me-1"></i> تفريغ الحقول</button>');
                                    $('#btnResetFilters').on('click', resetFilters);
                                }
                            } else {
                                $btnResetFilters.remove();
                            }
                            
                            // إعادة ربط event handlers
                            bindPaginationEvents();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error filtering roles:', xhr);
                        notify('error', 'حدث خطأ أثناء البحث');
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            });

            // تفريغ الفلاتر
            function resetFilters() {
                $nameFilter.val('');
                $labelFilter.val('');
                $descriptionFilter.val('');

                $.ajax({
                    url: FILTER_URL,
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        reset: 1
                    },
                    success: function(response) {
                        if (response.html) {
                            $rolesTbody.html(response.html.tbody || '');
                            $rolesPagination.html(response.html.pagination || '');
                            $btnResetFilters.remove();
                            bindPaginationEvents();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error resetting filters:', xhr);
                    }
                });
            }

            // زر تفريغ الحقول
            $btnResetFilters.on('click', resetFilters);

            // إرسال البحث عند الضغط على Enter
            $nameFilter.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $form.submit();
                }
            });

            $labelFilter.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $form.submit();
                }
            });

            $descriptionFilter.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $form.submit();
                }
            });

            // ربط events للـ pagination عند التحميل الأول
            bindPaginationEvents();

            // Helper function للإشعارات
            function notify(type, message) {
                if (window.adminNotifications && typeof window.adminNotifications[type] === 'function') {
                    window.adminNotifications[type](message);
                } else {
                    console.log(type + ':', message);
                }
            }
        });
    })(jQuery);
</script>
@endpush
