@extends('layouts.admin')

@section('title', 'الشكاوى والمقترحات')

@php
    $breadcrumbTitle = 'الشكاوى والمقترحات';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/complaints-suggestions.css') }}">
@endpush

@section('content')
<div class="complaints-page" id="complaintsPage" data-index-url="{{ route('admin.complaints-suggestions.index') }}">
    <div class="row g-3">
        <div class="col-12">
            <div class="complaints-card">
                <div class="complaints-card-header">
                    <div>
                        <h5 class="complaints-title">
                            <i class="bi bi-chat-left-text me-2"></i>
                            الشكاوى والمقترحات
                        </h5>
                        <div class="complaints-subtitle">
                            إدارة الشكاوى والمقترحات الواردة من المواطنين
                        </div>
                    </div>
                </div>

                <div class="card-body pb-4">

                    {{-- Statistics Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">إجمالي</h6>
                                    <h4 class="mb-0 text-primary" id="statTotal">{{ number_format($stats['total'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">شكاوى</h6>
                                    <h4 class="mb-0 text-danger" id="statComplaints">{{ number_format($stats['complaints'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">مقترحات</h6>
                                    <h4 class="mb-0 text-info" id="statSuggestions">{{ number_format($stats['suggestions'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">قيد الانتظار</h6>
                                    <h4 class="mb-0 text-warning" id="statPending">{{ number_format($stats['pending'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card border-secondary">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">قيد المعالجة</h6>
                                    <h4 class="mb-0 text-secondary" id="statInProgress">{{ number_format($stats['in_progress'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2 small">تم الحل</h6>
                                    <h4 class="mb-0 text-success" id="statResolved">{{ number_format($stats['resolved'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- كارد الفلاتر --}}
                    <div class="card border mb-0">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-funnel me-2"></i>
                                فلاتر البحث
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        بحث
                                    </label>
                                    <input type="text" id="complaintsSearch" class="form-control" 
                                           placeholder="اسم / هاتف / رمز التتبع..." 
                                           value="{{ request('search', '') }}" autocomplete="off">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag me-1"></i>
                                        النوع
                                    </label>
                                    <select id="typeFilter" class="form-select">
                                        <option value="all" {{ !request('type') || request('type') == 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="complaint" {{ request('type') == 'complaint' ? 'selected' : '' }}>شكوى</option>
                                        <option value="suggestion" {{ request('type') == 'suggestion' ? 'selected' : '' }}>مقترح</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-funnel me-1"></i>
                                        الحالة
                                    </label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="all" {{ !request('status') || request('status') == 'all' ? 'selected' : '' }}>الكل</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>تم الحل</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button type="button" class="btn btn-primary flex-fill" id="btnSearch">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary {{ request('search') || request('type') || request('status') ? '' : 'd-none' }}" id="btnResetFilters">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- HR separator --}}
                <hr class="my-4" style="border-top: 2px solid #dee2e6; opacity: 0.5;">

                {{-- كارد الجدول --}}
                <div class="card border">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-table me-2"></i>
                            نتائج البحث
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 complaints-table">
                                <thead>
                                    <tr>
                                        <th style="min-width:120px;">رمز التتبع</th>
                                        <th>النوع</th>
                                        <th>الاسم</th>
                                        <th>الهاتف</th>
                                        <th class="d-none d-md-table-cell">المولد</th>
                                        <th class="d-none d-lg-table-cell">المشغل</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="d-none d-xl-table-cell">التاريخ</th>
                                        <th style="min-width:140px;" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="complaintsTbody">
                                    @include('admin.complaints-suggestions.partials.tbody-rows', ['complaintsSuggestions' => $complaintsSuggestions])
                                </tbody>
                            </table>
                        </div>

                        @if($complaintsSuggestions->hasPages())
                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                                <div class="small text-muted" id="complaintsMeta">
                                    @if($complaintsSuggestions->total() > 0)
                                        عرض {{ $complaintsSuggestions->firstItem() }} - {{ $complaintsSuggestions->lastItem() }} من {{ $complaintsSuggestions->total() }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <nav>
                                    <ul class="pagination mb-0" id="complaintsPagination">
                                        @include('admin.complaints-suggestions.partials.pagination', ['complaintsSuggestions' => $complaintsSuggestions])
                                    </ul>
                                </nav>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize stats on page load
    const initialStats = @json($stats);
    
    function updateStats(stats) {
        if (!stats) return;
        if ($('#statTotal').length) $('#statTotal').text(stats.total ? number_format(stats.total) : '0');
        if ($('#statComplaints').length) $('#statComplaints').text(stats.complaints ? number_format(stats.complaints) : '0');
        if ($('#statSuggestions').length) $('#statSuggestions').text(stats.suggestions ? number_format(stats.suggestions) : '0');
        if ($('#statPending').length) $('#statPending').text(stats.pending ? number_format(stats.pending) : '0');
        if ($('#statInProgress').length) $('#statInProgress').text(stats.in_progress ? number_format(stats.in_progress) : '0');
        if ($('#statResolved').length) $('#statResolved').text(stats.resolved ? number_format(stats.resolved) : '0');
    }
    
    // Helper function for number formatting
    function number_format(number) {
        return new Intl.NumberFormat('ar-EG').format(number);
    }

    // Initialize list with AdminCRUD
    AdminCRUD.initList({
        url: '{{ route('admin.complaints-suggestions.index') }}',
        container: '#complaintsTbody',
        filters: {
            search: '#complaintsSearch',
            type: '#typeFilter',
            status: '#statusFilter'
        },
        searchButton: '#btnSearch',
        clearButton: '#btnResetFilters',
        paginationContainer: '#complaintsPagination',
        perPage: 100,
        listId: 'complaintsList',
        onSuccess: function(response, state) {
            // Update stats if provided
            if (response && response.stats) {
                updateStats(response.stats);
            }
            
            // Update meta info
            if (response && response.count !== undefined) {
                const count = response.count;
                if (count > 0) {
                    const perPage = 100;
                    const from = (state && state.page > 1) ? ((state.page - 1) * perPage) + 1 : 1;
                    const to = Math.min((state && state.page ? state.page : 1) * perPage, count);
                    $('#complaintsMeta').text(`عرض ${from} - ${to} من ${count}`);
                } else {
                    $('#complaintsMeta').text('—');
                }
            }
        },
        onError: function(error) {
            console.error('Error loading complaints:', error);
            showToast('حدث خطأ أثناء تحميل البيانات', 'error');
        }
    });

    // Handle clear search button visibility
    $('#complaintsSearch').on('input', function() {
        $('#btnClearSearch').toggleClass('d-none', $(this).val().trim().length === 0);
        const hasFilters = $(this).val().trim().length > 0 || $('#typeFilter').val() || $('#statusFilter').val();
        $('#btnResetFilters').toggleClass('d-none', !hasFilters);
    });

    $('#typeFilter, #statusFilter').on('change', function() {
        const hasFilters = $('#complaintsSearch').val().trim().length > 0 || $('#typeFilter').val() || $('#statusFilter').val();
        $('#btnResetFilters').toggleClass('d-none', !hasFilters);
    });

    // Handle delete buttons
    $(document).on('click', '.complaint-delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('complaint-id');
        const tracking = $(this).data('complaint-tracking') || 'هذا الطلب';
        
        AdminCRUD.delete({
            url: '{{ route('admin.complaints-suggestions.destroy', ['complaintSuggestion' => '__ID__']) }}',
            id: id,
            confirmMessage: `هل أنت متأكد من حذف الطلب برمز التتبع ${tracking}؟`,
            onSuccess: function() {
                // Reload list
                const listController = AdminCRUD.activeLists.get('complaintsList');
                if (listController) {
                    listController.refresh();
                }
            }
        });
    });
});
</script>
@endpush
