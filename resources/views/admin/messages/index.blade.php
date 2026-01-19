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
<style>
    /* Message Cards Styles */
    .messages-page .msg-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .messages-page .msg-row {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.25rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .messages-page .msg-row:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .messages-page .msg-row.msg-unread {
        background: linear-gradient(135deg, #e7f3ff 0%, #d0e7ff 100%);
        border-left: 4px solid #3b82f6;
    }
    .messages-page .msg-row-main {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }
    .messages-page .msg-row-content {
        flex: 1;
    }
    .messages-page .msg-row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .messages-page .msg-row-title {
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        font-weight: 600;
    }
    .messages-page .msg-row-meta {
        display: flex;
        gap: 0.5rem;
    }
    .messages-page .msg-row-details {
        margin-top: 0.75rem;
    }
    .messages-page .msg-detail-item {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    .messages-page .msg-detail-item i {
        width: 20px;
    }
    .messages-page .msg-preview {
        padding-top: 0.5rem;
        border-top: 1px solid #e9ecef;
        margin-top: 0.5rem;
    }
    .messages-page .msg-row-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    .messages-page .msg-empty-state {
        padding: 3rem 1rem;
    }
    .messages-page .msg-pagination {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }
</style>
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
                    <div id="messagesLoadingOverlay" class="data-table-loading" style="display:none;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">جاري التحميل...</p>
                    </div>

                    <div id="messagesListContainer">
                        @include('admin.messages.partials.tbody-rows', ['messages' => $messages])
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
    };

    function loadMessages() {
        $('#messagesLoadingOverlay').show();
        
        $.ajax({
            url: indexUrl,
            method: 'GET',
            data: {
                search: state.search,
                type: state.type,
                is_read: state.is_read,
                ajax: 1,
                page: state.page,
            },
            success: function(response) {
                if (response.html) {
                    $container.html(response.html);
                }
                if (response.pagination) {
                    // Update pagination if needed
                    const $pagination = $container.find('.msg-pagination');
                    if ($pagination.length) {
                        $pagination.html(response.pagination);
                    }
                }
                if (response.count !== undefined) {
                    $count.text(response.count);
                }
            },
            error: function(xhr) {
                AdminCRUD.notify('error', 'تعذر تحميل الرسائل');
                console.error('Error loading messages:', xhr);
            },
            complete: function() {
                $('#messagesLoadingOverlay').hide();
            }
        });
    }

    // Search button
    $('#searchBtn').on('click', function() {
        state.search = $('#searchInput').val();
        state.type = $('#typeFilter').val();
        state.is_read = $('#readStatusFilter').val();
        state.page = 1;
        loadMessages();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#searchInput').val('');
        $('#typeFilter').val('');
        $('#readStatusFilter').val('');
        state.search = '';
        state.type = '';
        state.is_read = '';
        state.page = 1;
        loadMessages();
    });

    // Clear search button
    $('#btnClearSearch').on('click', function() {
        $('#searchInput').val('');
        state.search = '';
        state.page = 1;
        loadMessages();
    });

    // Enter key in search
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            $('#searchBtn').click();
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

    // Delete message
    $(document).on('click', '.btn-delete-message', function() {
        const id = $(this).data('id');
        const url = $(this).data('url');
        
        if (!confirm('هل أنت متأكد من حذف هذه الرسالة؟')) {
            return;
        }

        $.ajax({
            url: url,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    AdminCRUD.notify('success', resp.message || 'تم حذف الرسالة بنجاح');
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
                    : 'تعذر حذف الرسالة';
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
