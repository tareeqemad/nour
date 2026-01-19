@extends('layouts.admin')

@section('title', 'إدارة الثوابت')

@php
    $breadcrumbTitle = 'إدارة الثوابت';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/constants.css') }}">
@endpush

@section('content')
<div class="general-page" id="constantsPage" data-index-url="{{ route('admin.constants.index') }}">

    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">

                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                        <i class="bi bi-database me-2"></i>
                        إدارة الثوابت
                    </h5>
                        <div class="general-subtitle">
                            إدارة وتنظيم ثوابت النظام
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        @can('create', App\Models\ConstantMaster::class)
                            <a href="{{ route('admin.constants.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة ثابت
                            </a>
                        @endcan
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
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </label>
                                    <input type="text" class="form-control" id="constantsSearch" placeholder="رقم الثابت / الاسم / الوصف..." autocomplete="off" value="{{ request('search', '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-funnel me-1"></i>
                                        الحالة
                                    </label>
                                    <select class="form-select" id="statusFilter">
                                        <option value="">الكل</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط فقط</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط فقط</option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button class="btn btn-primary flex-fill" id="btnSearch">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary {{ request('search') || request('status') ? '' : 'd-none' }}" id="btnResetFilters">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 general-table">
                            <thead>
                                <tr>
                                    <th style="min-width:120px;">رقم الثابت</th>
                                    <th>اسم الثابت</th>
                                    <th class="d-none d-md-table-cell">الوصف</th>
                                    <th class="text-center">التفاصيل</th>
                                    <th class="text-center">الحالة</th>
                                    <th class="text-center d-none d-lg-table-cell">الترتيب</th>
                                    <th style="min-width:140px;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="constantsTbody">
                                @include('admin.constants.partials.tbody-rows', ['constants' => $constants])
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                        <div class="small text-muted" id="constantsMeta">
                            @if($constants->total() > 0)
                                عرض {{ $constants->firstItem() }} - {{ $constants->lastItem() }} من {{ $constants->total() }}
                            @else
                                —
                            @endif
                        </div>
                        <nav>
                            <ul class="pagination mb-0" id="constantsPagination">
                                @include('admin.constants.partials.pagination', ['constants' => $constants])
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Container -->
    <div id="modalsContainer">
        @include('admin.constants.partials.modals', ['constants' => $constants])
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // Initialize list with AdminCRUD
    AdminCRUD.initList({
        url: '{{ route('admin.constants.index') }}',
        container: '#constantsTbody',
        filters: {
            search: '#constantsSearch',
            status: '#statusFilter'
        },
        searchButton: '#btnSearch',
        clearButton: '#btnResetFilters',
        paginationContainer: '#constantsPagination',
        countElement: '#statTotal',
        perPage: 100,
        listId: 'constantsList',
        onSuccess: function(response, state) {
            // Update modals if provided
            if (response.modals) {
                $('#modalsContainer').html(response.modals);
                // Reinitialize Bootstrap modals
                $('[data-bs-toggle="modal"]').each(function() {
                    const $this = $(this);
                    $this.off('click.modal-init').on('click.modal-init', function(e) {
                        e.preventDefault();
                        const target = $this.data('bs-target');
                        if (target) {
                            $(target).modal('show');
                        }
                    });
                });
            }
            
            // Update meta info
            if (response.count !== undefined && response.count > 0) {
                const count = response.count;
                const perPage = 100;
                const from = state.page === 1 ? 1 : ((state.page - 1) * perPage) + 1;
                const to = Math.min(state.page * perPage, count);
                $('#constantsMeta').text(`عرض ${from} - ${to} من ${count}`);
            } else {
                $('#constantsMeta').text('—');
            }
        }
    });

    // Handle clear search button visibility
    $('#constantsSearch').on('input', function() {
        $('#btnClearSearch').toggleClass('d-none', $(this).val().trim().length === 0);
    });

    // Handle delete buttons (if using modals)
    $(document).on('click', '.constant-delete-btn', function(e) {
        // This is handled by the modal, but we can refresh list after delete
        $(document).on('admincrud:deleted', function() {
            const listController = AdminCRUD.activeLists.get('constantsList');
            if (listController) {
                listController.refresh();
            }
        });
    });
});
</script>
@endpush
