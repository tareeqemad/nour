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
    #messagesPage .msg-list{display:flex;flex-direction:column;gap:.7rem;}
    #messagesPage .msg-card{display:flex;align-items:flex-start;gap:.9rem;background:#fff;border:1px solid var(--color-border-soft,#EDF1F5);border-radius:12px;padding:.95rem 1.05rem;transition:box-shadow .15s,border-color .15s;}
    #messagesPage .msg-card:hover{box-shadow:0 6px 18px rgba(31,41,55,.08);border-color:#D9E0EA;}
    #messagesPage .msg-card.is-unread{background:#F5F7FF;border-color:#C7D2FE;border-right:3px solid var(--color-primary,#24308F);}
    #messagesPage .msg-ic{flex-shrink:0;width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;background:var(--color-primary-soft,#EEF2FF);color:var(--color-primary,#24308F);}
    #messagesPage .msg-card.is-unread .msg-ic{background:var(--color-primary,#24308F);color:#fff;}
    #messagesPage .msg-main{flex:1;min-width:0;}
    #messagesPage .msg-top{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem;}
    #messagesPage .msg-subject{font-weight:700;color:var(--color-text-main,#1F2937);font-size:.98rem;text-decoration:none;}
    #messagesPage .msg-subject:hover{color:var(--color-primary,#24308F);text-decoration:underline;}
    #messagesPage .msg-card.is-unread .msg-subject{color:#11186B;}
    #messagesPage .msg-clip{color:var(--color-success,#10B981);}
    #messagesPage .msg-type{font-size:.72rem;font-weight:700;padding:.18rem .6rem;border-radius:30px;}
    #messagesPage .msg-new{font-size:.7rem;font-weight:700;background:var(--color-primary,#24308F);color:#fff;padding:.14rem .55rem;border-radius:30px;}
    #messagesPage .msg-sub{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem;color:var(--color-text-muted,#5B6780);font-size:.8rem;margin-top:.25rem;}
    #messagesPage .msg-sub .sep{opacity:.45;}
    #messagesPage .msg-snippet{color:var(--color-text-muted,#5B6780);font-size:.84rem;margin-top:.4rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    #messagesPage .msg-end{flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:.55rem;}
    #messagesPage .msg-status{font-size:.74rem;font-weight:700;white-space:nowrap;}
    #messagesPage .msg-status.read{color:var(--color-success,#10B981);}
    #messagesPage .msg-status.unread{color:var(--color-warning,#F59E0B);}
    #messagesPage .msg-acts{display:flex;gap:.35rem;}
    #messagesPage .t-blue{background:#E6F1FB;color:#185FA5;}
    #messagesPage .t-green{background:#EAF7EE;color:#1f8f4d;}
    #messagesPage .t-amber{background:#FAEEDA;color:#854F0B;}
    #messagesPage .t-red{background:#FCEBEB;color:#A32D2D;}
</style>
@endpush

@section('content')
<div class="general-page" id="messagesPage" data-index-url="{{ route('admin.messages.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="الرسائل" icon="bi-envelope">
                    <x-slot:actions>
                        @can('create', App\Models\Message::class)
                            <a href="{{ route('admin.messages.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                رسالة جديدة
                            </a>
                        @endcan
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body pb-4">
                    {{-- التابات --}}
                    <ul class="nav nav-tabs mb-4" id="messagesTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button" role="tab">
                                <i class="bi bi-inbox me-1"></i>الوارد
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="archived-tab" data-bs-toggle="tab" data-bs-target="#archived" type="button" role="tab">
                                <i class="bi bi-archive me-1"></i>المؤرشفة
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="messagesTabContent">
                        {{-- تاب الوارد --}}
                        <div class="tab-pane fade show active" id="inbox" role="tabpanel">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">البحث</label>
                                    <input type="text" id="searchInput" class="form-control" placeholder="ابحث في الموضوع أو المحتوى..." value="{{ request('search', '') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">نوع الرسالة</label>
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
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">الحالة</label>
                                    <select id="readStatusFilter" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>غير مقروء</option>
                                        <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>مقروء</option>
                                    </select>
                                </div>
                                <div class="col-md-auto">
                                    <div class="d-flex gap-2">
                                        <button type="button" id="searchBtn" class="btn btn-primary">
                                            <i class="bi bi-search me-2"></i>بحث
                                        </button>
                                        <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary">
                                            <i class="bi bi-x me-2"></i>تفريغ
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 position-relative" style="min-height: 200px;">
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
                        <div class="tab-pane fade" id="archived" role="tabpanel">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">البحث</label>
                                    <input type="text" id="archivedSearchInput" class="form-control" placeholder="ابحث في الموضوع أو المحتوى...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">نوع الرسالة</label>
                                    <select id="archivedTypeFilter" class="form-select">
                                        <option value="">كل الأنواع</option>
                                        <option value="operator_to_operator">مشغل لمشغل</option>
                                        <option value="operator_to_staff">مشغل لموظفين</option>
                                        @if($isSuperAdmin || $isAdmin)
                                            <option value="admin_to_operator">أدمن لمشغل</option>
                                            <option value="admin_to_all">أدمن للجميع</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">الحالة</label>
                                    <select id="archivedReadStatusFilter" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="0">غير مقروء</option>
                                        <option value="1">مقروء</option>
                                    </select>
                                </div>
                                <div class="col-md-auto">
                                    <div class="d-flex gap-2">
                                        <button type="button" id="archivedSearchBtn" class="btn btn-primary">
                                            <i class="bi bi-search me-2"></i>بحث
                                        </button>
                                        <button type="button" id="clearArchivedFiltersBtn" class="btn btn-outline-secondary">
                                            <i class="bi bi-x me-2"></i>تفريغ
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 position-relative" style="min-height: 200px;">
                                <div id="archivedMessagesLoadingOverlay" class="data-table-loading" style="display:none;">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">جاري التحميل...</p>
                                </div>
                                <div id="archivedMessagesListContainer">
                                    <div class="text-center py-5">
                                        <i class="bi bi-archive" style="font-size: 2.5rem; color: var(--color-text-muted, #5B6780);"></i>
                                        <h6 class="text-muted mt-3">لا توجد رسائل مؤرشفة</h6>
                                        <p class="text-muted small">سيتم عرض الرسائل المؤرشفة هنا</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
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
    const indexUrl = $page.data('index-url');

    const state = { page: 1, search: '', type: '', is_read: '', archived: false };

    function loadMessages() {
        const prefix = state.archived ? 'archived' : '';
        const loadingId = state.archived ? '#archivedMessagesLoadingOverlay' : '#messagesLoadingOverlay';
        const containerId = state.archived ? '#archivedMessagesListContainer' : '#messagesListContainer';

        $(loadingId).show();

        $.ajax({
            url: indexUrl,
            method: 'GET',
            data: { search: state.search, type: state.type, is_read: state.is_read, archived: state.archived ? 1 : 0, ajax: 1, page: state.page },
            success: function(response) {
                if (response.html) $(containerId).html(response.html);
                if (response.pagination) {
                    $(containerId).find('.msg-pagination').html(response.pagination);
                }
                if (response.count !== undefined && !state.archived) {
                    $('#messagesCount').text(response.count);
                }
            },
            error: function() { AdminCRUD.notify('error', 'تعذر تحميل الرسائل'); },
            complete: function() { $(loadingId).hide(); }
        });
    }

    // Tab switching
    $('#inbox-tab, #archived-tab').on('shown.bs.tab', function(e) {
        state.archived = $(e.target).attr('id') === 'archived-tab';
        state.page = 1;
        if (state.archived) {
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

    // Search (Inbox)
    $('#searchBtn').on('click', function() {
        state.archived = false;
        state.search = $('#searchInput').val();
        state.type = $('#typeFilter').val();
        state.is_read = $('#readStatusFilter').val();
        state.page = 1;
        loadMessages();
    });

    // Search (Archived)
    $('#archivedSearchBtn').on('click', function() {
        state.archived = true;
        state.search = $('#archivedSearchInput').val();
        state.type = $('#archivedTypeFilter').val();
        state.is_read = $('#archivedReadStatusFilter').val();
        state.page = 1;
        loadMessages();
    });

    // Clear (Inbox)
    $('#clearFiltersBtn').on('click', function() {
        state.archived = false;
        $('#searchInput, #typeFilter, #readStatusFilter').val('');
        state.search = state.type = state.is_read = '';
        state.page = 1;
        loadMessages();
    });

    // Clear (Archived)
    $('#clearArchivedFiltersBtn').on('click', function() {
        state.archived = true;
        $('#archivedSearchInput, #archivedTypeFilter, #archivedReadStatusFilter').val('');
        state.search = state.type = state.is_read = '';
        state.page = 1;
        loadMessages();
    });

    // Enter key
    $('#searchInput').on('keypress', function(e) { if (e.which === 13) $('#searchBtn').click(); });
    $('#archivedSearchInput').on('keypress', function(e) { if (e.which === 13) $('#archivedSearchBtn').click(); });

    // Pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            state.page = parseInt(new URL(url).searchParams.get('page') || 1);
            loadMessages();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Archive message
    $(document).on('click', '.btn-delete-message', function() {
        const url = $(this).data('url');
        if (!confirm('هل أنت متأكد من أرشفة هذه الرسالة؟')) return;
        $.ajax({
            url: url,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    AdminCRUD.notify('success', resp.message || 'تم أرشفة الرسالة بنجاح');
                    loadMessages();
                    if (window.MessagesPanel) { window.MessagesPanel.loadUnreadCount(); window.MessagesPanel.loadRecentMessages(); }
                }
            },
            error: function(xhr) {
                AdminCRUD.notify('error', (xhr.responseJSON && xhr.responseJSON.message) || 'تعذر أرشفة الرسالة');
            }
        });
    });

    @if(session('message_sent'))
        if (window.MessagesPanel) window.MessagesPanel.refresh();
    @endif
})();
</script>
@endpush
