@extends('layouts.admin')

@section('title', 'الامتثال والسلامة')

@php
    $breadcrumbTitle = 'الامتثال والسلامة';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/compliance-safeties.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            {{-- Main: قائمة الامتثال والسلامة --}}
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-shield-check me-2"></i>
                                الامتثال والسلامة
                            </h5>
                            <div class="general-subtitle">
                                إدارة سجلات الامتثال والسلامة. العدد: <span id="complianceSafetiesCount">{{ isset($groupedLogs) ? $groupedLogs->flatten()->count() : $complianceSafeties->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\ComplianceSafety::class)
                                <a href="{{ route('admin.compliance-safeties.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة سجل جديد
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
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
                                    <div class="{{ isset($operators) && $operators->count() > 0 ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input
                                            type="text"
                                            id="searchInput"
                                            class="form-control"
                                            placeholder="ابحث عن سجل بالمشغل أو الجهة..."
                                            value="{{ request('q', '') }}"
                                        >
                                    </div>

                                    {{-- المشغل --}}
                                    @if(isset($operators) && $operators->count() > 0)
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-building me-1"></i>
                                                المشغل
                                            </label>
                                            <select id="operatorFilter" class="form-select">
                                                <option value="">كل المشغلين</option>
                                                @foreach($operators as $op)
                                                    <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                        {{ $op->unit_number ? $op->unit_number . ' - ' : '' }}{{ $op->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- تاريخ من --}}
                                    <div class="{{ isset($operators) && $operators->count() > 0 ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            من تاريخ
                                        </label>
                                        <input type="date" id="dateFromFilter" class="form-control" 
                                               value="{{ request('date_from', '') }}">
                                    </div>

                                    {{-- تاريخ إلى --}}
                                    <div class="{{ isset($operators) && $operators->count() > 0 ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            إلى تاريخ
                                        </label>
                                        <input type="date" id="dateToFilter" class="form-control" 
                                               value="{{ request('date_to', '') }}">
                                    </div>

                                </div>
                                {{-- أزرار البحث والتجميع --}}
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-center gap-2 align-items-center flex-wrap">
                                        <button class="btn btn-primary" type="button" id="searchBtn">
                                            <i class="bi bi-search me-2"></i>
                                            بحث
                                        </button>
                                        <button
                                            class="btn btn-outline-secondary {{ request('q') || request('operator_id') || request('date_from') || request('date_to') ? '' : 'd-none' }}"
                                            type="button"
                                            id="clearSearchBtn"
                                        >
                                            <i class="bi bi-x me-2"></i>
                                            تفريغ
                                        </button>
                                        <div class="form-check form-switch ms-3">
                                            <input class="form-check-input" type="checkbox" id="groupByOperatorToggle" {{ request('group_by_operator') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="groupByOperatorToggle">
                                                <i class="bi bi-grid-3x3-gap me-1"></i>
                                                تجميع حسب المشغل
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        @if(request('group_by_operator') && isset($groupedLogs) && $groupedLogs->isNotEmpty())
                            {{-- Grouped view (non-AJAX only) --}}
                            @include('admin.compliance-safeties.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'complianceSafeties' => $complianceSafeties])
                        @else
                            {{-- Normal table view with AJAX --}}
                            <div class="table-responsive">
                                <table class="table general-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>اسم المشغل</th>
                                            <th>حالة شهادة السلامة</th>
                                            <th>تاريخ آخر زيارة</th>
                                            <th>الجهة المنفذة</th>
                                            <th>المستخدم</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="complianceSafetiesTbody">
                                        @include('admin.compliance-safeties.partials.tbody-rows', ['complianceSafeties' => $complianceSafeties])
                                    </tbody>
                                </table>
                            </div>

                            @if(!request('group_by_operator') && isset($complianceSafeties) && $complianceSafeties->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="small text-muted">
                                        عرض {{ $complianceSafeties->firstItem() }} - {{ $complianceSafeties->lastItem() }} من {{ $complianceSafeties->total() }}
                                    </div>
                                    <nav>
                                        <ul class="pagination mb-0" id="complianceSafetiesPagination">
                                            @include('admin.compliance-safeties.partials.pagination', ['complianceSafeties' => $complianceSafeties])
                                        </ul>
                                    </nav>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Only initialize AdminCRUD if not in grouped view
    @if(!request('group_by_operator'))
    // Initialize list with AdminCRUD
    AdminCRUD.initList({
        url: '{{ route('admin.compliance-safeties.index') }}',
        container: '#complianceSafetiesTbody',
        filters: {
            q: '#searchInput',
            operator_id: '#operatorFilter',
            date_from: '#dateFromFilter',
            date_to: '#dateToFilter'
        },
        searchButton: '#searchBtn',
        clearButton: '#clearSearchBtn',
        paginationContainer: '#complianceSafetiesPagination',
        countElement: '#complianceSafetiesCount',
        perPage: 100,
        listId: 'complianceSafetiesList'
    });

    // Handle delete buttons
    $(document).on('click', '.compliance-safety-delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('compliance-safety-id');
        const name = $(this).data('compliance-safety-name') || 'هذا السجل';
        
        AdminCRUD.delete({
            url: '{{ route('admin.compliance-safeties.destroy', ['compliance_safety' => '__ID__']) }}',
            id: id,
            confirmMessage: `هل أنت متأكد من حذف ${name}؟`,
            onSuccess: function() {
                // Reload list
                const listController = AdminCRUD.activeLists.get('complianceSafetiesList');
                if (listController) {
                    listController.refresh();
                }
            }
        });
    });
    @endif

    // Handle group by operator toggle - reload page with parameter
    $('#groupByOperatorToggle').on('change', function() {
        const url = new URL(window.location.href);
        if ($(this).is(':checked')) {
            url.searchParams.set('group_by_operator', '1');
        } else {
            url.searchParams.delete('group_by_operator');
        }
        window.location.href = url.toString();
    });
});
</script>
@endpush
