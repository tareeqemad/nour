@extends('layouts.admin')

@section('title', 'سجل النشاطات')

@php
    $breadcrumbTitle = 'سجل النشاطات';
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $isCompanyOwner = auth()->user()->isCompanyOwner();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
<div class="audit-logs-page">
    <div class="row g-3">
        <div class="col-12">
            <div class="card log-card">
                <div class="log-card-header log-toolbar-header">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <div class="log-title">
                                <i class="bi bi-clock-history me-2"></i>
                                سجل النشاطات
                            </div>
                            <div class="log-subtitle">
                                {{ $isSuperAdmin ? 'متابعة جميع النشاطات على مستوى النظام' : 'متابعة نشاطات المستخدمين التابعين لمشغلك' }}
                            </div>
                        </div>
                    </div>

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
                                {{-- البحث --}}
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-search me-1"></i>
                                        البحث
                                    </label>
                                    <input
                                        type="text"
                                        id="searchInput"
                                        class="form-control"
                                        placeholder="ابحث في النشاطات..."
                                        value="{{ request('search', '') }}"
                                    >
                                </div>

                                {{-- المستخدم (للسوبر أدمن والمشغل) --}}
                                @if($users->isNotEmpty())
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person me-1"></i>
                                            المستخدم
                                        </label>
                                        <select id="userFilter" class="form-select">
                                            <option value="">كل المستخدمين</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }} ({{ $u->username }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- نوع الإجراء --}}
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-activity me-1"></i>
                                        نوع الإجراء
                                    </label>
                                    <select id="actionFilter" class="form-select">
                                        <option value="">كل الإجراءات</option>
                                        @foreach($actions as $action)
                                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                                {{ ucfirst($action) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- نوع الموديل (للسوبر أدمن فقط) --}}
                                @if($isSuperAdmin && $modelTypes->isNotEmpty())
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-file-earmark me-1"></i>
                                            نوع الموديل
                                        </label>
                                        <select id="modelTypeFilter" class="form-select">
                                            <option value="">كل الأنواع</option>
                                            @foreach($modelTypes as $modelType)
                                                <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                                                    {{ class_basename($modelType) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- تاريخ من --}}
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        من تاريخ
                                    </label>
                                    <input type="date" id="dateFromFilter" class="form-control" 
                                           value="{{ request('date_from', '') }}">
                                </div>

                                {{-- تاريخ إلى --}}
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        إلى تاريخ
                                    </label>
                                    <input type="date" id="dateToFilter" class="form-control" 
                                           value="{{ request('date_to', '') }}">
                                </div>

                                {{-- أزرار البحث --}}
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-center gap-2 align-items-center flex-wrap">
                                        <button class="btn btn-primary" id="btnSearch">
                                            <i class="bi bi-search me-2"></i>
                                            بحث
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary {{ request('search') || request('user_id') || request('action') || request('model_type') || request('date_from') || request('date_to') ? '' : 'd-none' }}" id="btnResetFilters">
                                            <i class="bi bi-x me-2"></i>
                                            تفريغ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Card للجدول --}}
                <div class="card border mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:80px;">#</th>
                                        <th style="min-width:120px;">المستخدم</th>
                                        <th style="min-width:100px;">الإجراء</th>
                                        <th>الوصف</th>
                                        @if($isSuperAdmin)
                                            <th style="min-width:150px;">نوع الموديل</th>
                                        @endif
                                        <th style="min-width:150px;">الوقت</th>
                                        <th style="min-width:100px;" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="auditLogsTbody">
                                    @include('admin.audit-logs.partials.tbody-rows', ['logs' => $logs])
                                </tbody>
                            </table>
                        </div>

                        @if($logs->hasPages())
                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                                <div class="small text-muted">
                                    @if($logs->total() > 0)
                                        عرض {{ $logs->firstItem() }} - {{ $logs->lastItem() }} من {{ $logs->total() }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <nav>
                                    <ul class="pagination mb-0" id="auditLogsPagination">
                                        @include('admin.audit-logs.partials.pagination', ['logs' => $logs])
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
    // Initialize list with AdminCRUD
    AdminCRUD.initList({
        url: '{{ route('admin.activity-logs.index') }}',
        container: '#auditLogsTbody',
        filters: {
            search: '#searchInput',
            user_id: '#userFilter',
            action: '#actionFilter',
            model_type: '#modelTypeFilter',
            date_from: '#dateFromFilter',
            date_to: '#dateToFilter'
        },
        searchButton: '#btnSearch',
        clearButton: '#btnResetFilters',
        paginationContainer: '#auditLogsPagination',
        perPage: 20,
        listId: 'auditLogsList'
    });

    // Show/hide clear button
    function toggleClearButton() {
        const hasFilters = $('#searchInput').val() || 
                          ($('#userFilter').length && $('#userFilter').val()) ||
                          ($('#actionFilter').length && $('#actionFilter').val()) ||
                          ($('#modelTypeFilter').length && $('#modelTypeFilter').val()) ||
                          $('#dateFromFilter').val() || 
                          $('#dateToFilter').val();
        if (hasFilters) {
            $('#btnResetFilters').removeClass('d-none');
        } else {
            $('#btnResetFilters').addClass('d-none');
        }
    }

    $('#searchInput, #userFilter, #actionFilter, #modelTypeFilter, #dateFromFilter, #dateToFilter').on('change input', function() {
        toggleClearButton();
    });

    toggleClearButton();
});
</script>
@endpush

