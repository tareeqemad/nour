@extends('layouts.admin')

@section('title', 'كفاءة الوقود')

@php
    $breadcrumbTitle = 'كفاءة الوقود';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/fuel-efficiencies.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <div class="general-page" id="fuelEfficienciesPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-speedometer2 me-2"></i>
                                كفاءة الوقود
                            </h5>
                            <div class="general-subtitle">
                                إدارة سجلات كفاءة الوقود. العدد: <span id="fuelEfficienciesCount">{{ $fuelEfficiencies ? $fuelEfficiencies->total() : 0 }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\FuelEfficiency::class)
                                <a href="{{ route('admin.fuel-efficiencies.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة سجل جديد
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
                                    {{-- المشغل --}}
                                    @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority()) && isset($operators) && $operators->count() > 0)
                                        {{-- للسوبر أدمن، الأدمن، وسلطة الطاقة: select قابل للاختيار --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-building me-1"></i>
                                                المشغل
                                            </label>
                                            <select id="operatorFilter" class="form-select select2">
                                                <option value="0">-- اختر المشغل --</option>
                                                @foreach($operators as $op)
                                                    <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                        {{ $op->name }}
                                                        @if($op->unit_number)
                                                            - {{ $op->unit_number }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif((auth()->user()->isCompanyOwner() || auth()->user()->isEmployee() || auth()->user()->isTechnician()) && isset($operators) && $operators->count() > 0)
                                        @php
                                            $operator = $operators->first();
                                        @endphp
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-building me-1"></i>
                                                المشغل
                                            </label>
                                            <select id="operatorFilter" class="form-select select2" disabled>
                                                <option value="{{ $operator->id }}" selected>
                                                    {{ $operator->name }}
                                                    @if($operator->unit_number)
                                                        - {{ $operator->unit_number }}
                                                    @endif
                                                </option>
                                            </select>
                                            <input type="hidden" id="operatorFilterHidden" value="{{ $operator->id }}">
                                        </div>
                                    @endif

                                    {{-- وحدة التوليد --}}
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                                        {{-- للسوبر أدمن، الأدمن، وسلطة الطاقة: تبدأ فارغة وتُملأ عند اختيار المشغل --}}
                                        {{-- إذا كان المشغل محدداً في الـ request، نعرض وحدات التوليد مباشرة --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-grid-3x3 me-1"></i>
                                                وحدة التوليد
                                            </label>
                                            <select id="generationUnitFilter" class="form-select select2">
                                                <option value="0">-- اختر وحدة التوليد --</option>
                                                @if(isset($generationUnits) && $generationUnits->count() > 0)
                                                    @foreach($generationUnits as $unit)
                                                        <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>
                                                            {{ $unit->name }} ({{ $unit->unit_code }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    @elseif(isset($generationUnits) && $generationUnits->count() > 0)
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-grid-3x3 me-1"></i>
                                                وحدة التوليد
                                            </label>
                                            <select id="generationUnitFilter" class="form-select select2">
                                                <option value="0">-- اختر وحدة التوليد --</option>
                                                @foreach($generationUnits as $unit)
                                                    <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }} ({{ $unit->unit_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- المولد --}}
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                                        {{-- للسوبر أدمن، الأدمن، وسلطة الطاقة: تبدأ فارغة وتُملأ عند اختيار وحدة التوليد --}}
                                        {{-- إذا كانت وحدة التوليد محددة في الـ request، نعرض المولدات مباشرة --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                المولد
                                            </label>
                                            <select id="generatorFilter" class="form-select select2">
                                                <option value="0">-- اختر المولد --</option>
                                                @if(isset($generators) && $generators->count() > 0)
                                                    @foreach($generators as $gen)
                                                        <option value="{{ $gen->id }}" 
                                                                data-generation-unit-id="{{ $gen->generation_unit_id }}"
                                                                {{ request('generator_id') == $gen->id ? 'selected' : '' }}>
                                                            {{ $gen->generator_number }} - {{ $gen->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    @elseif(isset($generators) && $generators->count() > 0)
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                المولد
                                            </label>
                                            <select id="generatorFilter" class="form-select select2">
                                                <option value="0">-- اختر المولد --</option>
                                                @foreach($generators as $gen)
                                                    <option value="{{ $gen->id }}" 
                                                            data-generation-unit-id="{{ $gen->generation_unit_id }}"
                                                            {{ request('generator_id') == $gen->id ? 'selected' : '' }}>
                                                        {{ $gen->generator_number }} - {{ $gen->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    {{-- تاريخ من --}}
                                    <div class="{{ (((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority()) && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0)) ? 'col-md-3' : 'col-md-4' }}">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            من تاريخ
                                        </label>
                                        <input type="date" id="dateFromFilter" class="form-control" 
                                               value="{{ request('date_from', '') }}">
                                    </div>

                                    {{-- تاريخ إلى --}}
                                    <div class="{{ (((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority()) && isset($operators) && $operators->count() > 0) || (isset($generators) && $generators->count() > 0)) ? 'col-md-3' : 'col-md-4' }}">
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
                </div>
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
            confirmMessage: `هل أنت متأكد من حذف ${name}؟`,
            onSuccess: function() {
                const listController = AdminCRUD.activeLists.get('fuelEfficienciesList');
                if (listController) {
                    listController.refresh();
                }
            }
        });
    });
    @endif

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
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
<script src="{{ asset('assets/admin/js/fuel-efficiencies.js') }}"></script>
<script>
$(document).ready(function() {
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
        // للسوبر أدمن، الأدمن، وسلطة الطاقة: المشغل → وحدة التوليد → المولد
        // عند اختيار المشغل
        const $operatorFilter = $('#operatorFilter');
        const $generationUnitFilter = $('#generationUnitFilter');
        const $generatorFilter = $('#generatorFilter');
        
        if ($operatorFilter.length && $operatorFilter.is('select')) {
            $operatorFilter.on('change', async function() {
                const operatorId = $(this).val();
                
                // إعادة تهيئة Select2 لوحدة التوليد
                if ($generationUnitFilter.length) {
                    $generationUnitFilter.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                }
                
                // إعادة تهيئة Select2 للمولد
                if ($generatorFilter.length) {
                    $generatorFilter.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                }
                
                if (!operatorId || operatorId == '0') {
                    if ($generationUnitFilter.length) {
                        $generationUnitFilter.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').prop('disabled', true);
                    }
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/operators/${operatorId}/generation-units-for-efficiencies`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val()
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if ($generationUnitFilter.length) {
                            $generationUnitFilter.empty().append('<option value="0">-- اختر وحدة التوليد --</option>');
                            
                            if (data.generation_units && data.generation_units.length > 0) {
                                data.generation_units.forEach(unit => {
                                    $generationUnitFilter.append(new Option(unit.label, unit.id, false, false));
                                });
                                $generationUnitFilter.prop('disabled', false).trigger('change');
                            } else {
                                $generationUnitFilter.append('<option value="">لا توجد وحدات توليد</option>');
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error loading generation units:', error);
                    if ($generationUnitFilter.length) {
                        $generationUnitFilter.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                }
            });
        }
        
        // عند اختيار وحدة التوليد
        if ($generationUnitFilter.length) {
            $generationUnitFilter.on('change', async function() {
                const generationUnitId = $(this).val();
                
                // إعادة تهيئة Select2 للمولد
                if ($generatorFilter.length) {
                    $generatorFilter.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: true,
                        width: '100%'
                    }).prop('disabled', true);
                }
                
                if (!generationUnitId || generationUnitId == '0') {
                    if ($generatorFilter.length) {
                        $generatorFilter.empty().append('<option value="0">-- اختر المولد --</option>').prop('disabled', true);
                    }
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/generation-units/${generationUnitId}/generators-for-efficiencies`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val()
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if ($generatorFilter.length) {
                            $generatorFilter.empty().append('<option value="0">-- اختر المولد --</option>');
                            
                            if (data.generators && data.generators.length > 0) {
                                data.generators.forEach(generator => {
                                    $generatorFilter.append(new Option(generator.label, generator.id, false, false));
                                });
                                $generatorFilter.prop('disabled', false).trigger('change');
                            } else {
                                $generatorFilter.append('<option value="">لا توجد مولدات</option>');
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error loading generators:', error);
                    if ($generatorFilter.length) {
                        $generatorFilter.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                }
            });
        }
        
        @if(request('operator_id'))
            if ($operatorFilter.length) {
                $operatorFilter.trigger('change');
            }
        @endif
    @else
        // للمشغل/الموظف: المشغل محدد → وحدات التوليد تظهر تلقائياً → يختار المولد
        
        // إذا كان المشغل محدد ووحدة التوليد غير محددة، نحدد أول وحدة توليد تلقائياً
        const $operatorFilterHidden = $('#operatorFilterHidden');
        const $generationUnitFilter = $('#generationUnitFilter');
        const $generatorFilter = $('#generatorFilter');
        
        const operatorValue = $operatorFilterHidden.length ? $operatorFilterHidden.val() : null;
        if (operatorValue && (!$generationUnitFilter.val() || $generationUnitFilter.val() == '0')) {
            // اختر أول وحدة توليد متاحة
            const firstGenerationUnit = $generationUnitFilter.find('option:not([value="0"])').first();
            if (firstGenerationUnit.length) {
                $generationUnitFilter.val(firstGenerationUnit.val()).trigger('change');
                
                // تنفيذ البحث تلقائياً إذا كان المشغل ووحدة التوليد محددين
                setTimeout(function() {
                    // محاولة استخدام AdminCRUD إذا كان متاحاً
                    if (typeof AdminCRUD !== 'undefined' && AdminCRUD.activeLists) {
                        const listController = AdminCRUD.activeLists.get('fuelEfficienciesList');
                        if (listController && typeof listController.refresh === 'function') {
                            listController.refresh();
                        } else if (typeof AdminCRUD.initList === 'function') {
                            // إذا لم يتم تهيئة القائمة بعد، انتظر قليلاً ثم حاول مرة أخرى
                            setTimeout(function() {
                                const listController = AdminCRUD.activeLists && AdminCRUD.activeLists.get('fuelEfficienciesList');
                                if (listController && typeof listController.refresh === 'function') {
                                    listController.refresh();
                                } else {
                                    // بديل: تنفيذ البحث يدوياً
                                    $('#searchBtn').click();
                                }
                            }, 500);
                        }
                    } else {
                        // بديل: تنفيذ البحث يدوياً
                        $('#searchBtn').click();
                    }
                }, 200);
            }
        }
        
        if ($generationUnitFilter.length) {
            $generationUnitFilter.on('change', function() {
                const generationUnitId = $(this).val();
                const currentValue = $generatorFilter.val();
                
                // تصفية المولدات حسب وحدة التوليد
                $generatorFilter.find('option').each(function() {
                    const $option = $(this);
                    if (!$option.val() || $option.val() == '0') return; // تجاهل option الفارغ
                    
                    const optionGenerationUnitId = $option.data('generation-unit-id');
                    if (generationUnitId && generationUnitId != '0' && optionGenerationUnitId == generationUnitId) {
                        $option.prop('disabled', false).show();
                    } else if (!generationUnitId || generationUnitId == '0') {
                        $option.prop('disabled', false).show();
                    } else {
                        $option.prop('disabled', true).hide();
                    }
                });
                
                // إعادة تهيئة Select2
                $generatorFilter.select2('destroy').select2({
                    dir: 'rtl',
                    language: 'ar',
                    allowClear: true,
                    width: '100%'
                });
                
                // إذا كانت القيمة الحالية غير متاحة، امسح الاختيار
                if (currentValue && currentValue != '0') {
                    const $selectedOption = $generatorFilter.find(`option[value="${currentValue}"]`);
                    if ($selectedOption.length && $selectedOption.prop('disabled')) {
                        $generatorFilter.val('0').trigger('change');
                    }
                }
            });
        }
    @endif
});
</script>
@endpush
