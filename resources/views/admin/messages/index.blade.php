@extends('layouts.admin')

@section('title', 'الرسائل')

@php
    $breadcrumbTitle = 'الرسائل';
    $user = auth()->user();
    $isSuperAdmin = $user->isSuperAdmin();
    $isAdmin = $user->isAdmin();
    $isCompanyOwner = $user->isCompanyOwner();
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/css/messages-list.css') }}">
@endpush

@section('content')
<div class="general-page messages-page" id="messagesPage" data-index-url="{{ route('admin.messages.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h5 class="general-title">
                            <i class="bi bi-envelope me-2"></i>
                            الرسائل
                        </h5>
                        <div class="general-subtitle">
                            إدارة الرسائل الداخلية. العدد: <span id="messagesCount">{{ $messages->total() }}</span>
                        </div>
                    </div>
                    @can('create', App\Models\Message::class)
                        <a href="{{ route('admin.messages.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            رسالة جديدة
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    {{-- التابات --}}
                    <ul class="nav nav-tabs mb-3" id="messagesTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button" role="tab" aria-controls="inbox" aria-selected="true">
                                <i class="bi bi-inbox me-1"></i>
                                الوارد
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="archived-tab" data-bs-toggle="tab" data-bs-target="#archived" type="button" role="tab" aria-controls="archived" aria-selected="false">
                                <i class="bi bi-archive me-1"></i>
                                المؤرشفة
                            </button>
                        </li>
                    </ul>

                    {{-- محتوى التابات --}}
                    <div class="tab-content" id="messagesTabContent">
                        {{-- تاب الوارد --}}
                        <div class="tab-pane fade show active" id="inbox" role="tabpanel" aria-labelledby="inbox-tab">
                            {{-- فلاتر البحث --}}
                            <div class="filter-card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <i class="bi bi-funnel me-2"></i>
                                فلاتر البحث
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- البحث --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        البحث
                                    </label>
                                    <div class="general-search">
                                        <i class="bi bi-search"></i>
                                        <input
                                            type="text"
                                            id="searchInput"
                                            class="form-control"
                                            placeholder="ابحث في الموضوع أو المحتوى..."
                                            value="{{ request('search', '') }}"
                                        >
                                        @if(request('search'))
                                            <button type="button" class="general-clear" id="btnClearSearch" title="إلغاء البحث">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- نوع الرسالة --}}
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag me-1"></i>
                                        نوع الرسالة
                                    </label>
                                    <select id="typeFilter" class="form-select">
                                        <option value="">كل الأنواع</option>
                                        <option value="operator_to_operator" {{ request('type') == 'operator_to_operator' ? 'selected' : '' }}>مشغل لمشغل</option>
                                        <option value="operator_to_staff" {{ request('type') == 'operator_to_staff' ? 'selected' : '' }}>مشغل لموظفين</option>
                                        @if($isSuperAdmin || $isAdmin)
                                            <option value="admin_to_operator" {{ request('type') == 'admin_to_operator' ? 'selected' : '' }}>أدمن لمشغل</option>
                                            <option value="admin_to_all" {{ request('type') == 'admin_to_all' ? 'selected' : '' }}>أدمن للجميع</option>
                                        @endif
                                    </select>
                                </div>

                                {{-- الحالة --}}
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-eye me-1"></i>
                                        الحالة
                                    </label>
                                    <select id="readStatusFilter" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>غير مقروء</option>
                                        <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>مقروء</option>
                                    </select>
                                </div>

                                {{-- أزرار البحث والتفريغ --}}
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button type="button" id="searchBtn" class="btn btn-primary flex-fill">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                            <hr class="my-3">

                            {{-- قائمة الرسائل --}}
                            <div style="position: relative; min-height: 200px;">
                                <div id="messagesLoadingOverlay" class="data-table-loading" style="display:none;">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">جاري التحميل...</p>
                                </div>

                                <div id="messagesListContainer">
                                    @include('admin.messages.partials.tbody-rows', ['messages' => $messages])
                                </div>
                            </div>
                        </div>

                        {{-- تاب المؤرشفة --}}
                        <div class="tab-pane fade" id="archived" role="tabpanel" aria-labelledby="archived-tab">
                            {{-- فلاتر البحث للمؤرشفة --}}
                            <div class="filter-card">
                                <div class="card-header">
                                    <h6 class="card-title">
                                        <i class="bi bi-funnel me-2"></i>
                                        فلاتر البحث
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- البحث --}}
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-search me-1"></i>
                                                البحث
                                            </label>
                                            <div class="general-search">
                                                <i class="bi bi-search"></i>
                                                <input
                                                    type="text"
                                                    id="archivedSearchInput"
                                                    class="form-control"
                                                    placeholder="ابحث في الموضوع أو المحتوى..."
                                                    value="{{ request('search', '') }}"
                                                >
                                                @if(request('search'))
                                                    <button type="button" class="general-clear" id="btnClearArchivedSearch" title="إلغاء البحث">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- نوع الرسالة --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-tag me-1"></i>
                                                نوع الرسالة
                                            </label>
                                            <select id="archivedTypeFilter" class="form-select">
                                                <option value="">كل الأنواع</option>
                                                <option value="operator_to_operator" {{ request('type') == 'operator_to_operator' ? 'selected' : '' }}>مشغل لمشغل</option>
                                                <option value="operator_to_staff" {{ request('type') == 'operator_to_staff' ? 'selected' : '' }}>مشغل لموظفين</option>
                                                @if($isSuperAdmin || $isAdmin)
                                                    <option value="admin_to_operator" {{ request('type') == 'admin_to_operator' ? 'selected' : '' }}>أدمن لمشغل</option>
                                                    <option value="admin_to_all" {{ request('type') == 'admin_to_all' ? 'selected' : '' }}>أدمن للجميع</option>
                                                @endif
                                            </select>
                                        </div>

                                        {{-- الحالة --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-eye me-1"></i>
                                                الحالة
                                            </label>
                                            <select id="archivedReadStatusFilter" class="form-select">
                                                <option value="">الكل</option>
                                                <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>غير مقروء</option>
                                                <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>مقروء</option>
                                            </select>
                                        </div>

                                        {{-- أزرار البحث والتفريغ --}}
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="d-flex gap-2 w-100">
                                                <button type="button" id="archivedSearchBtn" class="btn btn-primary flex-fill">
                                                    <i class="bi bi-search me-1"></i>
                                                    بحث
                                                </button>
                                                <button type="button" id="clearArchivedFiltersBtn" class="btn btn-outline-secondary">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- قائمة الرسائل المؤرشفة --}}
                            <div style="position: relative; min-height: 200px;">
                                <div id="archivedMessagesLoadingOverlay" class="data-table-loading" style="display:none;">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">جاري التحميل...</p>
                                </div>

                                <div id="archivedMessagesListContainer">
                                    <div class="msg-empty-state text-center py-5">
                                        <i class="bi bi-archive fs-1 text-muted d-block mb-3"></i>
                                        <h5 class="text-muted">لا توجد رسائل مؤرشفة</h5>
                                        <p class="text-muted">سيتم عرض الرسائل المؤرشفة هنا</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/js/admin-crud.js') }}"></script>
<script>
(function() {
    'use strict';
    
    const $page = $('#messagesPage');
    const $container = $('#messagesListContainer');
    const $count = $('#messagesCount');
    const indexUrl = $page.data('index-url');
    
    const state = {
        page: 1,
        search: '',
        type: '',
        is_read: '',
        archived: false,
    };

    function loadMessages() {
        const loadingOverlay = state.archived ? '#archivedMessagesLoadingOverlay' : '#messagesLoadingOverlay';
        const container = state.archived ? '#archivedMessagesListContainer' : '#messagesListContainer';
        
        $(loadingOverlay).show();
        
        $.ajax({
            url: indexUrl,
            method: 'GET',
            data: {
                search: state.search,
                type: state.type,
                is_read: state.is_read,
                archived: state.archived ? 1 : 0,
                ajax: 1,
                page: state.page,
            },
            success: function(response) {
                if (response.html) {
                    $(container).html(response.html);
                }
                if (response.pagination) {
                    const $pagination = $(container).find('.msg-pagination');
                    if ($pagination.length) {
                        $pagination.html(response.pagination);
                    }
                }
                if (response.count !== undefined && !state.archived) {
                    $count.text(response.count);
                }
            },
            error: function(xhr) {
                AdminCRUD.notify('error', 'تعذر تحميل الرسائل');
                console.error('Error loading messages:', xhr);
            },
            complete: function() {
                $(loadingOverlay).hide();
            }
        });
    }

    // Tab switching
    $('#inbox-tab, #archived-tab').on('shown.bs.tab', function(e) {
        const isArchived = $(e.target).attr('id') === 'archived-tab';
        state.archived = isArchived;
        state.page = 1;
        
        // Update search inputs based on active tab
        if (isArchived) {
            state.search = $('#archivedSearchInput').val();
            state.type = $('#archivedTypeFilter').val();
            state.is_read = $('#archivedReadStatusFilter').val();
        } else {
            state.search = $('#searchInput').val();
            state.type = $('#typeFilter').val();
            state.is_read = $('#readStatusFilter').val();
        }
        
        loadMessages();
    });

    // Search button (Inbox)
    $('#searchBtn').on('click', function() {
        state.archived = false;
        state.search = $('#searchInput').val();
        state.type = $('#typeFilter').val();
        state.is_read = $('#readStatusFilter').val();
        state.page = 1;
        loadMessages();
    });

    // Search button (Archived)
    $('#archivedSearchBtn').on('click', function() {
        state.archived = true;
        state.search = $('#archivedSearchInput').val();
        state.type = $('#archivedTypeFilter').val();
        state.is_read = $('#archivedReadStatusFilter').val();
        state.page = 1;
        loadMessages();
    });

    // Clear filters (Inbox)
    $('#clearFiltersBtn').on('click', function() {
        state.archived = false;
        $('#searchInput').val('');
        $('#typeFilter').val('');
        $('#readStatusFilter').val('');
        state.search = '';
        state.type = '';
        state.is_read = '';
        state.page = 1;
        loadMessages();
    });

    // Clear filters (Archived)
    $('#clearArchivedFiltersBtn').on('click', function() {
        state.archived = true;
        $('#archivedSearchInput').val('');
        $('#archivedTypeFilter').val('');
        $('#archivedReadStatusFilter').val('');
        state.search = '';
        state.type = '';
        state.is_read = '';
        state.page = 1;
        loadMessages();
    });

    // Clear search button (Inbox)
    $('#btnClearSearch').on('click', function() {
        state.archived = false;
        $('#searchInput').val('');
        state.search = '';
        state.page = 1;
        loadMessages();
    });

    // Clear search button (Archived)
    $('#btnClearArchivedSearch').on('click', function() {
        state.archived = true;
        $('#archivedSearchInput').val('');
        state.search = '';
        state.page = 1;
        loadMessages();
    });

    // Enter key in search (Inbox)
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            $('#searchBtn').click();
        }
    });

    // Enter key in search (Archived)
    $('#archivedSearchInput').on('keypress', function(e) {
        if (e.which === 13) {
            $('#archivedSearchBtn').click();
        }
    });

    // Pagination links
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            const page = new URL(url).searchParams.get('page') || 1;
            state.page = parseInt(page);
            loadMessages();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Archive message
    $(document).on('click', '.btn-delete-message', function() {
        const id = $(this).data('id');
        const url = $(this).data('url');
        
        if (!confirm('هل أنت متأكد من أرشفة هذه الرسالة؟')) {
            return;
        }

        $.ajax({
            url: url,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    AdminCRUD.notify('success', resp.message || 'تم أرشفة الرسالة بنجاح');
                    loadMessages();
                    // Refresh messages panel
                    if (window.MessagesPanel) {
                        window.MessagesPanel.loadUnreadCount();
                        window.MessagesPanel.loadRecentMessages();
                    }
                }
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'تعذر أرشفة الرسالة';
                AdminCRUD.notify('error', msg);
            }
        });
    });

    // Initial load
    // loadMessages(); // Don't reload on initial page load

    // Trigger event if message was just sent
    @if(session('message_sent'))
        if (window.MessagesPanel) {
            window.MessagesPanel.refresh();
        }
    @endif
})();
</script>
@endpush
