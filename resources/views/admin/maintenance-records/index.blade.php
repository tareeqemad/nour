@extends('layouts.admin')

@section('title', 'سجلات الصيانة')

@php
    $breadcrumbTitle = 'سجلات الصيانة';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/maintenance-records.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <div class="general-page">
        <div class="row g-3">
            {{-- Main: قائمة سجلات الصيانة --}}
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-tools me-2"></i>
                                سجلات الصيانة
                            </h5>
                            <div class="general-subtitle">
                                إدارة سجلات الصيانة. العدد: <span id="maintenanceRecordsCount">{{ isset($groupedLogs) ? $groupedLogs->flatten()->count() : $maintenanceRecords->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\MaintenanceRecord::class)
                                <a href="{{ route('admin.maintenance-records.create') }}" class="btn btn-primary">
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
                                    <div class="{{ (auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0) ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input
                                            type="text"
                                            id="searchInput"
                                            class="form-control"
                                            placeholder="ابحث عن سجل بالمولد أو الفني..."
                                            value="{{ request('q', '') }}"
                                        >
                                    </div>

                                    {{-- المشغل (SuperAdmin فقط) --}}
                                    @if(auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0)
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

                                    {{-- المولد --}}
                                    @if(isset($generators) && $generators->count() > 0)
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                المولد
                                            </label>
                                            <select id="generatorFilter" class="form-select">
                                                <option value="">كل المولدات</option>
                                                @foreach($generators as $gen)
                                                    <option value="{{ $gen->id }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>
                                                        {{ $gen->generator_number }} - {{ $gen->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- تاريخ من --}}
                                    <div class="{{ ((auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0)) ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            من تاريخ
                                        </label>
                                        <input type="date" id="dateFromFilter" class="form-control" 
                                               value="{{ request('date_from', '') }}">
                                    </div>

                                    {{-- تاريخ إلى --}}
                                    <div class="{{ ((auth()->user()->isSuperAdmin() && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0)) ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            إلى تاريخ
                                        </label>
                                        <input type="date" id="dateToFilter" class="form-control" 
                                               value="{{ request('date_to', '') }}">
                                    </div>

                                    {{-- أزرار البحث والتجميع --}}
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-center gap-2 align-items-center flex-wrap">
                                            <button class="btn btn-primary" type="button" id="searchBtn">
                                                <i class="bi bi-search me-2"></i>
                                                بحث
                                            </button>
                                            <button
                                                class="btn btn-outline-secondary {{ request('q') || request('operator_id') || request('generator_id') || request('date_from') || request('date_to') ? '' : 'd-none' }}"
                                                type="button"
                                                id="clearSearchBtn"
                                            >
                                                <i class="bi bi-x me-2"></i>
                                                تفريغ
                                            </button>
                                            <div class="form-check form-switch ms-3">
                                                <input class="form-check-input" type="checkbox" id="groupByGeneratorToggle" {{ request('group_by_generator') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="groupByGeneratorToggle">
                                                    <i class="bi bi-grid-3x3-gap me-1"></i>
                                                    تجميع حسب المولد
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        @if(request('group_by_generator') && isset($groupedLogs) && $groupedLogs->isNotEmpty())
                            {{-- Grouped view (non-AJAX only) --}}
                            @include('admin.maintenance-records.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'maintenanceRecords' => $maintenanceRecords])
                        @else
                            {{-- Normal table view with AJAX --}}
                            <div class="table-responsive">
                                <table class="table general-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>رقم المولد</th>
                                            <th>نوع الصيانة</th>
                                            <th>تاريخ الصيانة</th>
                                            <th>المستخدم</th>
                                            <th>زمن التوقف</th>
                                            <th>تكلفة الصيانة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="maintenanceRecordsTbody">
                                        @include('admin.maintenance-records.partials.tbody-rows', ['maintenanceRecords' => $maintenanceRecords])
                                    </tbody>
                                </table>
                            </div>

                            @if(isset($maintenanceRecords) && $maintenanceRecords->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="small text-muted">
                                        عرض {{ $maintenanceRecords->firstItem() }} - {{ $maintenanceRecords->lastItem() }} من {{ $maintenanceRecords->total() }}
                                    </div>
                                    <nav>
                                        <ul class="pagination mb-0" id="maintenanceRecordsPagination">
                                            @include('admin.maintenance-records.partials.pagination', ['maintenanceRecords' => $maintenanceRecords])
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
    @if(!request('group_by_generator'))
    // Initialize list with AdminCRUD
    AdminCRUD.initList({
        url: '{{ route('admin.maintenance-records.index') }}',
        container: '#maintenanceRecordsTbody',
        filters: {
            q: '#searchInput',
            operator_id: '#operatorFilter',
            generator_id: '#generatorFilter',
            date_from: '#dateFromFilter',
            date_to: '#dateToFilter'
        },
        searchButton: '#searchBtn',
        clearButton: '#clearSearchBtn',
        paginationContainer: '#maintenanceRecordsPagination',
        countElement: '#maintenanceRecordsCount',
        perPage: 100,
        listId: 'maintenanceRecordsList'
    });

    // Handle delete buttons
    $(document).on('click', '.maintenance-record-delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('maintenance-record-id');
        const name = $(this).data('maintenance-record-name') || 'هذا السجل';
        
        AdminCRUD.delete({
            url: '{{ route('admin.maintenance-records.destroy', ['maintenance_record' => '__ID__']) }}',
            id: id,
            confirmMessage: `هل أنت متأكد من حذف ${name}؟`,
            onSuccess: function() {
                const listController = AdminCRUD.activeLists.get('maintenanceRecordsList');
                if (listController) {
                    listController.refresh();
                }
            }
        });
    });
    @endif

    // Handle group by generator toggle
    $('#groupByGeneratorToggle').on('change', function() {
        const url = new URL(window.location.href);
        if ($(this).is(':checked')) {
            url.searchParams.set('group_by_generator', '1');
        } else {
            url.searchParams.delete('group_by_generator');
        }
        window.location.href = url.toString();
    });
});
</script>
@endpush
