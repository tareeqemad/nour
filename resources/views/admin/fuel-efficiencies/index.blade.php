@extends('layouts.admin')

@section('title', 'كفاءة الوقود')

@php
    $breadcrumbTitle = 'كفاءة الوقود';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <div class="general-page" id="fuelEfficienciesPage">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header title="كفاءة الوقود" icon="bi-speedometer2">
                        <x-slot:actions>
                            @can('create', App\Models\FuelEfficiency::class)
                                <a href="{{ route('admin.fuel-efficiencies.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة سجل جديد
                                </a>
                            @endcan
                        </x-slot:actions>
                    </x-admin.card-header>

                    <div class="card-body pb-4">
                        {{-- فلاتر البحث --}}
                        <div class="row g-3 align-items-end">
                            @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority()) && isset($operators) && $operators->count() > 0)
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <select id="operatorFilter" class="form-select">
                                        <option value="">كل المشغلين</option>
                                        @foreach($operators as $op)
                                            <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>{{ $op->unit_number ? $op->unit_number . ' — ' : '' }}{{ $op->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label fw-semibold">وحدة التوليد</label>
                                    <select id="generationUnitFilter" class="form-select" {{ !request('operator_id') ? 'disabled' : '' }}>
                                        <option value="">اختر المشغل أولاً</option>
                                        @if(request('operator_id') && isset($generationUnits) && $generationUnits->count() > 0)
                                            @foreach($generationUnits as $unit)
                                                <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label fw-semibold">المولد</label>
                                    <select id="generatorFilter" class="form-select" {{ !request('generation_unit_id') ? 'disabled' : '' }}>
                                        <option value="">اختر وحدة التوليد أولاً</option>
                                        @if(request('generation_unit_id') && isset($generators) && $generators->count() > 0)
                                            @foreach($generators as $gen)
                                                <option value="{{ $gen->id }}" data-generation-unit-id="{{ $gen->generation_unit_id ?? '' }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>{{ $gen->generator_number }} — {{ $gen->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @elseif((auth()->user()->isCompanyOwner() || auth()->user()->isEmployee() || auth()->user()->isTechnician()) && isset($operators) && $operators->count() > 0)
                                @php $operator = $operators->first(); @endphp
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <select id="operatorFilter" class="form-select" disabled>
                                        <option value="{{ $operator->id }}" selected>{{ $operator->unit_number ? $operator->unit_number . ' — ' : '' }}{{ $operator->name }}</option>
                                    </select>
                                    <input type="hidden" id="operatorFilterHidden" value="{{ $operator->id }}">
                                </div>
                                @if(isset($generationUnits) && $generationUnits->count() > 0)
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">وحدة التوليد</label>
                                        <select id="generationUnitFilter" class="form-select">
                                            <option value="">كل الوحدات</option>
                                            @foreach($generationUnits as $unit)
                                                <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                @if(isset($generators) && $generators->count() > 0)
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">المولد</label>
                                        <select id="generatorFilter" class="form-select">
                                            <option value="">كل المولدات</option>
                                            @foreach($generators as $gen)
                                                <option value="{{ $gen->id }}" data-generation-unit-id="{{ $gen->generation_unit_id ?? '' }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>{{ $gen->generator_number }} — {{ $gen->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endif

                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-semibold">من تاريخ</label>
                                <input type="date" id="dateFromFilter" class="form-control" value="{{ request('date_from', '') }}">
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label fw-semibold">إلى تاريخ</label>
                                <input type="date" id="dateToFilter" class="form-control" value="{{ request('date_to', '') }}">
                            </div>

                            <div class="col-12 col-lg-auto order-last order-lg-0 mt-2 mt-lg-0">
                                <label class="form-label fw-semibold d-none d-lg-block">&nbsp;</label>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button class="btn btn-primary" type="button" id="searchBtn">
                                        <i class="bi bi-search me-2"></i>بحث
                                    </button>
                                    <button class="btn btn-outline-secondary {{ request('operator_id') || request('generation_unit_id') || request('generator_id') || request('date_from') || request('date_to') ? '' : 'd-none' }}" type="button" id="clearSearchBtn">
                                        <i class="bi bi-x me-2"></i>تفريغ
                                    </button>
                                    <div class="form-check form-switch ms-2 mb-0">
                                        <input class="form-check-input" type="checkbox" id="groupByGeneratorToggle" {{ request('group_by_generator') ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="groupByGeneratorToggle">تجميع حسب المولد</label>
                                    </div>
                                </div>
                            </div>
                        </div>


                        @if(request('group_by_generator') && isset($groupedLogs) && $groupedLogs->isNotEmpty())
                            {{-- Grouped view (non-AJAX only) --}}
                            @include('admin.fuel-efficiencies.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'fuelEfficiencies' => $fuelEfficiencies])
                        @else
                            {{-- Normal table view with AJAX --}}
                            <div class="table-responsive">
                                <table class="table general-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>رقم المولد</th>
                                            <th>تاريخ الاستهلاك</th>
                                            <th>ساعات التشغيل</th>
                                            <th>كفاءة الوقود</th>
                                            <th>كفاءة الطاقة</th>
                                            <th>التكلفة الإجمالية</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fuelEfficienciesTbody">
                                        @if(isset($fuelEfficiencies) && $fuelEfficiencies->count() > 0)
                                            @include('admin.fuel-efficiencies.partials.tbody-rows', ['fuelEfficiencies' => $fuelEfficiencies])
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="bi bi-search fs-1 text-muted"></i>
                                                    <p class="text-muted mt-3">يرجى استخدام الفلاتر أعلاه للبحث عن سجلات كفاءة الوقود</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            @if(isset($fuelEfficiencies) && $fuelEfficiencies->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="small text-muted">
                                        عرض {{ $fuelEfficiencies->firstItem() }} - {{ $fuelEfficiencies->lastItem() }} من {{ $fuelEfficiencies->total() }}
                                    </div>
                                    <nav>
                                        <ul class="pagination mb-0" id="fuelEfficienciesPagination">
                                            @include('admin.fuel-efficiencies.partials.pagination', ['fuelEfficiencies' => $fuelEfficiencies])
                                        </ul>
                                    </nav>
                                </div>
                            @endif
                        @endif
                    </div>
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if(!request('group_by_generator'))
    AdminCRUD.initList({
        url: '{{ route('admin.fuel-efficiencies.index') }}',
        container: '#fuelEfficienciesTbody',
        filters: {
            operator_id: '#operatorFilter, #operatorFilterHidden',
            generation_unit_id: '#generationUnitFilter',
            generator_id: '#generatorFilter',
            date_from: '#dateFromFilter',
            date_to: '#dateToFilter'
        },
        searchButton: '#searchBtn',
        clearButton: '#clearSearchBtn',
        paginationContainer: '#fuelEfficienciesPagination',
        countElement: '#fuelEfficienciesCount',
        perPage: 100,
        listId: 'fuelEfficienciesList'
    });

    $(document).on('click', '.fuel-efficiency-delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('fuel-efficiency-id');
        const name = $(this).data('fuel-efficiency-name') || 'هذا السجل';
        AdminCRUD.delete({
            url: '{{ route('admin.fuel-efficiencies.destroy', ['fuel_efficiency' => '__ID__']) }}',
            id: id,
            confirmMessage: 'هل أنت متأكد من حذف ' + name + '؟',
            onSuccess: function() {
                var listController = AdminCRUD.activeLists.get('fuelEfficienciesList');
                if (listController) listController.refresh();
            }
        });
    });
    @endif

    {{-- Cascading: same idea as maintenance-records (fuel-efficiencies endpoints) --}}
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
                var res = await fetch('/admin/operators/' + operatorId + '/generation-units-for-efficiencies', {
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
                var res = await fetch('/admin/generation-units/' + unitId + '/generators-for-efficiencies', {
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
    @if(request('operator_id') && !request('generation_unit_id'))
    if ($operatorFilter.length) $operatorFilter.trigger('change');
    @endif
    @else
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
