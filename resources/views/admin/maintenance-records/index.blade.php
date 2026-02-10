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
    <div class="maintenance-records-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="card log-card">
                    <div class="log-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h1 class="maintenance-records-index__title">
                                <i class="bi bi-tools me-2"></i>
                                سجلات الصيانة
                            </h1>
                            <div class="maintenance-records-index__meta text-muted">
                                العدد: <span id="maintenanceRecordsCount">{{ isset($groupedLogs) ? $groupedLogs->flatten()->count() : $maintenanceRecords->total() }}</span>
                            </div>
                        </div>
                        @can('create', App\Models\MaintenanceRecord::class)
                            <a href="{{ route('admin.maintenance-records.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>
                                إضافة سجل جديد
                            </a>
                        @endcan
                    </div>

                    <div class="card-body p-4">
                        {{-- Filter card (same as fuel-efficiencies) --}}
                        <div class="filter-card mb-4">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <i class="bi bi-funnel me-2"></i>
                                    فلاتر البحث
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority()) && isset($operators) && $operators->count() > 0)
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="operatorFilter">
                                            <i class="bi bi-building me-1"></i>
                                            المشغل
                                        </label>
                                        <select id="operatorFilter" class="form-select">
                                            <option value="">كل المشغلين</option>
                                            @foreach($operators as $op)
                                                <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                    {{ $op->unit_number ? $op->unit_number . ' — ' : '' }}{{ $op->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="generationUnitFilter">
                                            <i class="bi bi-lightning-charge me-1"></i>
                                            وحدة التوليد
                                        </label>
                                        <select id="generationUnitFilter" class="form-select" {{ !request('operator_id') ? 'disabled' : '' }}>
                                            <option value="">اختر المشغل أولاً</option>
                                            @if(request('operator_id') && isset($generationUnits) && $generationUnits->count() > 0)
                                                @foreach($generationUnits as $unit)
                                                    <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }} ({{ $unit->unit_code }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="generatorFilter">
                                            <i class="bi bi-gear me-1"></i>
                                            المولد
                                        </label>
                                        <select id="generatorFilter" class="form-select" {{ !request('generation_unit_id') ? 'disabled' : '' }}>
                                            <option value="">اختر وحدة التوليد أولاً</option>
                                            @if(request('generation_unit_id') && isset($generators) && $generators->count() > 0)
                                                @foreach($generators as $gen)
                                                    <option value="{{ $gen->id }}" data-generation-unit-id="{{ $gen->generation_unit_id ?? '' }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>
                                                        {{ $gen->generator_number }} — {{ $gen->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @elseif((auth()->user()->isCompanyOwner() || auth()->user()->isEmployee() || auth()->user()->isTechnician()) && isset($operators) && $operators->count() > 0)
                                    @php $operator = $operators->first(); @endphp
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="operatorFilter">
                                            <i class="bi bi-building me-1"></i>
                                            المشغل
                                        </label>
                                        <select id="operatorFilter" class="form-select" disabled>
                                            <option value="{{ $operator->id }}" selected>{{ $operator->unit_number ? $operator->unit_number . ' — ' : '' }}{{ $operator->name }}</option>
                                        </select>
                                        <input type="hidden" id="operatorFilterHidden" value="{{ $operator->id }}">
                                    </div>
                                    @if(isset($generationUnits) && $generationUnits->count() > 0)
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label fw-semibold" for="generationUnitFilter">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                وحدة التوليد
                                            </label>
                                            <select id="generationUnitFilter" class="form-select">
                                                <option value="">كل الوحدات</option>
                                                @foreach($generationUnits as $unit)
                                                    <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }} ({{ $unit->unit_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    @if(isset($generators) && $generators->count() > 0)
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label fw-semibold" for="generatorFilter">
                                                <i class="bi bi-gear me-1"></i>
                                                المولد
                                            </label>
                                            <select id="generatorFilter" class="form-select">
                                                <option value="">كل المولدات</option>
                                                @foreach($generators as $gen)
                                                    <option value="{{ $gen->id }}" data-generation-unit-id="{{ $gen->generation_unit_id ?? '' }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>
                                                        {{ $gen->generator_number }} — {{ $gen->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endif
                                {{-- From date / To date — same as compliance-safeties, with Flatpickr for calendar --}}
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label fw-semibold" for="dateFromFilter">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        من تاريخ
                                    </label>
                                    <input type="text" id="dateFromFilter" class="form-control maintenance-records-date-filter" value="{{ request('date_from', '') }}" placeholder="اختر التاريخ" autocomplete="off" readonly style="cursor: pointer;">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label fw-semibold" for="dateToFilter">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        إلى تاريخ
                                    </label>
                                    <input type="text" id="dateToFilter" class="form-control maintenance-records-date-filter" value="{{ request('date_to', '') }}" placeholder="اختر التاريخ" autocomplete="off" readonly style="cursor: pointer;">
                                </div>
                                {{-- Buttons column: margin so search button is not stuck to date input --}}
                                <div class="col-12 col-lg-auto order-last order-lg-0 mt-2 mt-lg-0 ms-lg-3">
                                    <label class="form-label fw-semibold d-none d-lg-block">&nbsp;</label>
                                    <div class="d-flex flex-wrap gap-2 align-items-end">
                                        <button type="button" class="btn btn-primary" id="searchBtn">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary {{ request('operator_id') || request('generation_unit_id') || request('generator_id') || request('date_from') || request('date_to') ? '' : 'd-none' }}" id="clearSearchBtn">
                                            <i class="bi bi-x me-1"></i>
                                            تفريغ
                                        </button>
                                        <div class="form-check form-switch mb-0 align-self-center">
                                            <input class="form-check-input" type="checkbox" id="groupByGeneratorToggle" {{ request('group_by_generator') ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="groupByGeneratorToggle">تجميع حسب المولد</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <hr class="my-3">

                        @if(request('group_by_generator') && isset($groupedLogs) && $groupedLogs->isNotEmpty())
                            @include('admin.maintenance-records.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'maintenanceRecords' => $maintenanceRecords])
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover general-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>رقم المولد</th>
                                            <th>نوع الصيانة</th>
                                            <th>التاريخ</th>
                                            <th>المستخدم</th>
                                            <th>زمن التوقف</th>
                                            <th>التكلفة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="maintenanceRecordsTbody">
                                        @include('admin.maintenance-records.partials.tbody-rows', ['maintenanceRecords' => $maintenanceRecords])
                                    </tbody>
                                </table>
                            </div>

                            @if(isset($maintenanceRecords) && $maintenanceRecords->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="small text-muted">
                                        عرض {{ $maintenanceRecords->firstItem() }} — {{ $maintenanceRecords->lastItem() }} من {{ $maintenanceRecords->total() }}
                                    </div>
                                    <nav>
                                        <ul class="pagination mb-0 pagination-sm" id="maintenanceRecordsPagination">
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

@push('styles')
<style>
    .maintenance-records-index__title { font-size: 1.25rem; font-weight: 700; margin: 0; }
    .maintenance-records-index__meta { font-size: 0.9rem; margin-top: 0.25rem; }
    /* Ensure Flatpickr wrapper takes full width inside filter card */
    .filter-card .flatpickr-wrapper {
        display: block !important;
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    {{-- Flatpickr for filter date inputs (calendar on click, no reliance on type=date) --}}
    (function initFilterDatePickers() {
        if (typeof flatpickr === 'undefined') {
            setTimeout(initFilterDatePickers, 100);
            return;
        }
        var locale = (document.documentElement.getAttribute('dir') || '') === 'rtl' ? 'ar' : 'en';
        var fromEl = document.getElementById('dateFromFilter');
        var toEl = document.getElementById('dateToFilter');
        // Fix full width after Flatpickr init
        function fixFlatpickrWidth(el) {
            el.style.width = '100%';
            el.style.display = 'block';
            // Flatpickr may create a wrapper — ensure it is full width too
            var parent = el.parentElement;
            if (parent && parent.classList && parent.classList.contains('flatpickr-wrapper')) {
                parent.style.width = '100%';
                parent.style.display = 'block';
            }
        }
        if (fromEl && !fromEl.getAttribute('data-flatpickr-initialized')) {
            flatpickr(fromEl, {
                locale: locale,
                dateFormat: 'Y-m-d',
                allowInput: false,
                clickOpens: true,
                defaultDate: fromEl.value || null,
                onChange: function(selectedDates, dateStr) { fromEl.value = dateStr; },
                onReady: function() { fixFlatpickrWidth(fromEl); }
            });
            fromEl.setAttribute('data-flatpickr-initialized', '1');
            fixFlatpickrWidth(fromEl);
        }
        if (toEl && !toEl.getAttribute('data-flatpickr-initialized')) {
            flatpickr(toEl, {
                locale: locale,
                dateFormat: 'Y-m-d',
                allowInput: false,
                clickOpens: true,
                defaultDate: toEl.value || null,
                onChange: function(selectedDates, dateStr) { toEl.value = dateStr; },
                onReady: function() { fixFlatpickrWidth(toEl); }
            });
            toEl.setAttribute('data-flatpickr-initialized', '1');
            fixFlatpickrWidth(toEl);
        }
    })();

    @if(!request('group_by_generator'))
    AdminCRUD.initList({
        url: '{{ route('admin.maintenance-records.index') }}',
        container: '#maintenanceRecordsTbody',
        filters: {
            operator_id: '#operatorFilter, #operatorFilterHidden',
            generation_unit_id: '#generationUnitFilter',
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

    $(document).on('click', '.maintenance-record-delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('maintenance-record-id');
        const name = $(this).data('maintenance-record-name') || 'هذا السجل';
        AdminCRUD.delete({
            url: '{{ route('admin.maintenance-records.destroy', ['maintenance_record' => '__ID__']) }}',
            id: id,
            confirmMessage: 'هل أنت متأكد من حذف ' + name + '؟',
            onSuccess: function() {
                var listController = AdminCRUD.activeLists.get('maintenanceRecordsList');
                if (listController) listController.refresh();
            }
        });
    });
    @endif

    {{-- Cascading: operator → generation unit → generator (SuperAdmin / Admin / Energy Authority) --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
    var $operatorFilter = $('#operatorFilter');
    var $generationUnitFilter = $('#generationUnitFilter');
    var $generatorFilter = $('#generatorFilter');
    if ($operatorFilter.length && $operatorFilter.is('select') && !$operatorFilter.prop('disabled')) {
        $operatorFilter.on('change', async function() {
            var operatorId = $(this).val();
            if ($generationUnitFilter.length) {
                $generationUnitFilter.empty().append('<option value="">كل الوحدات</option>').prop('disabled', true);
            }
            if ($generatorFilter.length) {
                $generatorFilter.empty().append('<option value="">كل المولدات</option>').prop('disabled', true);
            }
            if (!operatorId || operatorId === '') return;
            try {
                var res = await fetch('/admin/operators/' + operatorId + '/generation-units-for-maintenance-records', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '' }
                });
                if (res.ok) {
                    var data = await res.json();
                    if ($generationUnitFilter.length && data.generation_units && data.generation_units.length) {
                        data.generation_units.forEach(function(u) {
                            $generationUnitFilter.append(new Option(u.label, u.id, false, false));
                        });
                        $generationUnitFilter.prop('disabled', false);
                    }
                }
            } catch (e) { console.error(e); }
        });
    }
    if ($generationUnitFilter.length && $generationUnitFilter.is('select')) {
        $generationUnitFilter.on('change', async function() {
            var unitId = $(this).val();
            if ($generatorFilter.length) {
                $generatorFilter.empty().append('<option value="">كل المولدات</option>').prop('disabled', true);
            }
            if (!unitId || unitId === '') return;
            try {
                var res = await fetch('/admin/generation-units/' + unitId + '/generators-for-maintenance-records', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '' }
                });
                if (res.ok) {
                    var data = await res.json();
                    if ($generatorFilter.length && data.generators && data.generators.length) {
                        data.generators.forEach(function(g) {
                            $generatorFilter.append(new Option(g.label, g.id, false, false));
                        });
                        $generatorFilter.prop('disabled', false);
                    }
                }
            } catch (e) { console.error(e); }
        });
    }
    {{-- On page load with operator selected: load generation units via AJAX (unless unit already set from server) --}}
    @if(request('operator_id') && !request('generation_unit_id'))
    if ($operatorFilter.length) $operatorFilter.trigger('change');
    @endif
    @else
    {{-- For operator/employee: filter generators by generation unit --}}
    var $generationUnitFilter = $('#generationUnitFilter');
    var $generatorFilter = $('#generatorFilter');
    if ($generationUnitFilter.length && $generatorFilter.length) {
        $generationUnitFilter.on('change', function() {
            var unitId = $(this).val();
            var cur = $generatorFilter.val();
            $generatorFilter.find('option').each(function() {
                var $o = $(this);
                if (!$o.val()) return;
                var dataUnit = $o.data('generation-unit-id');
                if (unitId && unitId !== '' && dataUnit == unitId) {
                    $o.prop('disabled', false).show();
                } else if (!unitId || unitId === '') {
                    $o.prop('disabled', false).show();
                } else {
                    $o.prop('disabled', true).hide();
                }
            });
            if (cur) {
                var $sel = $generatorFilter.find('option[value="' + cur + '"]');
                if ($sel.length && $sel.prop('disabled')) $generatorFilter.val('');
            }
        });
    }
    @endif

    $('#groupByGeneratorToggle').on('change', function() {
        var url = new URL(window.location.href);
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
