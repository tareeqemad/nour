@extends('layouts.admin')

@section('title', 'سجلات التشغيل')

@php
    $breadcrumbTitle = 'سجلات التشغيل';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/operation-logs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page operation-logs-page" id="operationLogsPage">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header title="سجلات التشغيل" icon="bi-journal-text">
                        <x-slot:actions>
                            @can('create', App\Models\OperationLog::class)
                                <a href="{{ route('admin.operation-logs.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة سجل جديد
                                </a>
                            @endcan
                        </x-slot:actions>
                    </x-admin.card-header>

                    <div class="card-body pb-4">
                        {{-- فلاتر البحث --}}
                        <div class="row g-3 align-items-end">
                            {{-- المشغل --}}
                            @if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority()) && isset($operators) && $operators->count() > 0)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">المشغل <span class="text-danger">*</span></label>
                                    <select id="operatorFilter" class="form-select select2" required>
                                        <option value="0">اختر المشغل</option>
                                        @foreach($operators as $op)
                                            <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                {{ $op->name }}@if($op->unit_number) - {{ $op->unit_number }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif((auth()->user()->isCompanyOwner() || auth()->user()->isEmployee() || auth()->user()->isTechnician()) && isset($operators) && $operators->count() > 0)
                                @php $operator = $operators->first(); @endphp
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <select id="operatorFilter" class="form-select select2" disabled>
                                        <option value="{{ $operator->id }}" selected>{{ $operator->name }}@if($operator->unit_number) - {{ $operator->unit_number }}@endif</option>
                                    </select>
                                    <input type="hidden" id="operatorFilterHidden" value="{{ $operator->id }}">
                                </div>
                            @endif

                            {{-- وحدة التوليد --}}
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">وحدة التوليد <span class="text-danger">*</span></label>
                                    <select id="generationUnitFilter" class="form-select select2" required>
                                        <option value="0">اختر وحدة التوليد</option>
                                        @if(isset($generationUnits) && $generationUnits->count() > 0)
                                            @foreach($generationUnits as $unit)
                                                <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @elseif(isset($generationUnits) && $generationUnits->count() > 0)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">وحدة التوليد <span class="text-danger">*</span></label>
                                    <select id="generationUnitFilter" class="form-select select2" required>
                                        <option value="0">اختر وحدة التوليد</option>
                                        @foreach($generationUnits as $unit)
                                            <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- المولد --}}
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">المولد</label>
                                    <select id="generatorFilter" class="form-select select2">
                                        <option value="0">اختر المولد</option>
                                        @if(isset($generators) && $generators->count() > 0)
                                            @foreach($generators as $gen)
                                                <option value="{{ $gen->id }}" data-generation-unit-id="{{ $gen->generation_unit_id }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>{{ $gen->generator_number }} - {{ $gen->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @elseif(isset($generators) && $generators->count() > 0)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <label class="form-label fw-semibold">المولد</label>
                                    <select id="generatorFilter" class="form-select select2">
                                        <option value="0">اختر المولد</option>
                                        @foreach($generators as $gen)
                                            <option value="{{ $gen->id }}" data-generation-unit-id="{{ $gen->generation_unit_id }}" {{ request('generator_id') == $gen->id ? 'selected' : '' }}>{{ $gen->generator_number }} - {{ $gen->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- تاريخ من --}}
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="form-label fw-semibold">من تاريخ</label>
                                <input type="date" id="dateFromFilter" class="form-control" value="{{ request('date_from', '') }}">
                            </div>

                            {{-- تاريخ إلى --}}
                            <div class="col-6 col-md-4 col-lg-3">
                                <label class="form-label fw-semibold">إلى تاريخ</label>
                                <input type="date" id="dateToFilter" class="form-control" value="{{ request('date_to', '') }}">
                            </div>

                            {{-- أزرار البحث --}}
                            <div class="col-12 col-lg-auto order-last order-lg-0 mt-2 mt-lg-0">
                                <label class="form-label fw-semibold d-none d-lg-block">&nbsp;</label>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button class="btn btn-primary" type="button" id="searchBtn">
                                        <i class="bi bi-search me-2"></i>بحث
                                    </button>
                                    <button class="btn btn-outline-secondary {{ request('operator_id') || request('generator_id') || request('generation_unit_id') || request('date_from') || request('date_to') || request('load_percentage_value') || request('fuel_consumed_value') || request('energy_produced_value') ? '' : 'd-none' }}" type="button" id="clearSearchBtn">
                                        <i class="bi bi-x me-2"></i>تفريغ
                                    </button>
                                    @php
                                        $hasAdvancedFilters = request('load_percentage_value') || request('fuel_consumed_value') || request('energy_produced_value');
                                    @endphp
                                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}">
                                        <i class="bi bi-sliders me-1"></i>فلاتر متقدمة
                                    </button>
                                    <div class="form-check form-switch ms-2 mb-0">
                                        <input class="form-check-input" type="checkbox" id="groupByGeneratorToggle" {{ request('group_by_generator') ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="groupByGeneratorToggle">تجميع حسب المولد</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- فلاتر متقدمة (مخفية بالعادة) --}}
                        <div class="collapse {{ $hasAdvancedFilters ? 'show' : '' }} mt-3" id="advancedFilters">
                            <div class="row g-3 align-items-end" style="background: var(--color-bg-secondary, #F9FAFB); border-radius: 8px; padding: 1rem;">
                                <div class="col-6 col-md-3">
                                    <label class="form-label fw-semibold">نوع المقارنة</label>
                                    <select class="form-select" id="commonOperator">
                                        <option value="equals" {{ request('load_percentage_operator') == 'equals' ? 'selected' : '' }}>= يساوي</option>
                                        <option value="greater_than" {{ request('load_percentage_operator') == 'greater_than' ? 'selected' : '' }}>&gt; أكبر من</option>
                                        <option value="less_than" {{ request('load_percentage_operator') == 'less_than' ? 'selected' : '' }}>&lt; أصغر من</option>
                                        <option value="greater_equal" {{ request('load_percentage_operator') == 'greater_equal' ? 'selected' : '' }}>&gt;= أكبر أو يساوي</option>
                                        <option value="less_equal" {{ request('load_percentage_operator') == 'less_equal' ? 'selected' : '' }}>&lt;= أصغر أو يساوي</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label fw-semibold">نسبة التحميل (%)</label>
                                    <input type="number" id="loadPercentageValue" class="form-control" placeholder="النسبة" value="{{ request('load_percentage_value', '') }}" step="0.01" min="0" max="100">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label fw-semibold">الوقود المستهلك (لتر)</label>
                                    <input type="number" id="fuelConsumedValue" class="form-control" placeholder="الكمية" value="{{ request('fuel_consumed_value', '') }}" step="0.01" min="0">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label fw-semibold">الطاقة المنتجة (kWh)</label>
                                    <input type="number" id="energyProducedValue" class="form-control" placeholder="الطاقة" value="{{ request('energy_produced_value', '') }}" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- النتائج --}}
                        <div class="data-table-container mt-4">
                            <div id="operationLogsListContainer">
                                @if(request()->filled('operator_id') && request()->filled('generation_unit_id'))
                                    @if(isset($groupedLogs) && $groupedLogs->isNotEmpty())
                                        @include('admin.operation-logs.partials.grouped-list', ['groupedLogs' => $groupedLogs, 'operationLogs' => $operationLogs])
                                    @elseif(isset($operationLogs) && $operationLogs->count() > 0)
                                        @include('admin.operation-logs.partials.list', ['operationLogs' => $operationLogs])
                                    @else
                                        <x-admin.empty-state icon="bi-inbox" message="لا توجد نتائج للبحث" />
                                    @endif
                                @else
                                    <x-admin.empty-state icon="bi-search" message="يرجى استخدام الفلاتر أعلاه للبحث عن سجلات التشغيل" />
                                @endif
                            </div>
                        </div>
                    </div> {{-- /card-body --}}
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
    @if(file_exists(public_path('assets/admin/libs/select2/i18n/ar.js')))
        <script src="{{ asset('assets/admin/libs/select2/i18n/ar.js') }}"></script>
    @endif
    <script src="{{ asset('assets/admin/js/data-table-loading.js') }}"></script>
    <script>
        window.OPLOG = {
            routes: {
                index: @json(route('admin.operation-logs.index')),
                search: @json(route('admin.operation-logs.index')),
                delete: @json(route('admin.operation-logs.destroy', ['operation_log' => '__ID__'])),
            }
        };
        
        // تهيئة Select2 و Cascading Selection - نفس منطق create
        $(document).ready(function() {
            const $operatorFilter = $('#operatorFilter');
            const $operatorFilterHidden = $('#operatorFilterHidden');
            const $generationUnitFilter = $('#generationUnitFilter');
            const $generatorFilter = $('#generatorFilter');
            
            // Initialize Select2 for all selects
            $('.select2').each(function() {
                const $select = $(this);
                const hasEmptyOption = $select.find('option[value="0"]').length > 0;
                
                $select.select2({
                    dir: 'rtl',
                    language: 'ar',
                    allowClear: !hasEmptyOption, // لا تسمح بالمسح إذا كان هناك option فارغ
                    width: '100%',
                    placeholder: hasEmptyOption ? null : 'اختر...'
                });
            });
            
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                // للسوبر أدمن، الأدمن، وسلطة الطاقة: المشغل → وحدة التوليد → المولد
                // عند اختيار المشغل
                $operatorFilter.on('change', async function() {
                    const operatorId = $(this).val();
                    
                    // إعادة تهيئة Select2 لوحدة التوليد
                    $generationUnitFilter.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: false, // لا تسمح بالمسح لأن هناك option فارغ
                        width: '100%',
                        placeholder: null
                    }).prop('disabled', true);
                    
                    // إعادة تهيئة Select2 للمولد
                    $generatorFilter.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: false, // لا تسمح بالمسح لأن هناك option فارغ
                        width: '100%',
                        placeholder: null
                    }).prop('disabled', true);
                    
                    if (!operatorId || operatorId == '0') {
                        $generationUnitFilter.empty().append('<option value="0">-- اختر وحدة التوليد --</option>').prop('disabled', true);
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/operators/${operatorId}/generation-units-for-logs`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val()
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
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
                    } catch (error) {
                        console.error('Error loading generation units:', error);
                        $generationUnitFilter.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                });
                
                // عند اختيار وحدة التوليد
                $generationUnitFilter.on('change', async function() {
                    const generationUnitId = $(this).val();
                    
                    // إعادة تهيئة Select2 للمولد
                    $generatorFilter.empty().append('<option value="0">-- اختر المولد --</option>').select2('destroy').select2({
                        dir: 'rtl',
                        language: 'ar',
                        allowClear: false, // لا تسمح بالمسح لأن هناك option فارغ
                        width: '100%',
                        placeholder: null
                    }).prop('disabled', true);
                    
                    if (!generationUnitId || generationUnitId == '0') {
                        $generatorFilter.empty().append('<option value="0">-- اختر المولد --</option>').prop('disabled', true);
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/generation-units/${generationUnitId}/generators-for-logs`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val()
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
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
                    } catch (error) {
                        console.error('Error loading generators:', error);
                        $generatorFilter.empty().append('<option value="">حدث خطأ في التحميل</option>');
                    }
                });
                
                @if(request('operator_id'))
                    $operatorFilter.trigger('change');
                @endif
            @else
                // للمشغل/الموظف: المشغل محدد → وحدات التوليد تظهر تلقائياً → يختار المولد
                
                // إذا كان المشغل محدد ووحدة التوليد غير محددة، نحدد أول وحدة توليد تلقائياً
                const operatorValue = $operatorFilterHidden ? $operatorFilterHidden.val() : ($operatorFilter.val() && $operatorFilter.val() != '0' ? $operatorFilter.val() : null);
                if (operatorValue && (!$generationUnitFilter.val() || $generationUnitFilter.val() == '0')) {
                    // اختر أول وحدة توليد متاحة
                    const firstGenerationUnit = $generationUnitFilter.find('option:not([value="0"])').first();
                    if (firstGenerationUnit.length) {
                        $generationUnitFilter.val(firstGenerationUnit.val()).trigger('change');
                        
                        // تنفيذ البحث تلقائياً إذا كان المشغل ووحدة التوليد محددين
                        setTimeout(function() {
                            if (window.OPLOG && typeof window.OPLOG.loadOperationLogs === 'function') {
                                const operatorId = $operatorFilterHidden ? $operatorFilterHidden.val() : operatorValue;
                                const generationUnitId = $generationUnitFilter.val();
                                if (operatorId && generationUnitId && generationUnitId != '0') {
                                    window.OPLOG.loadOperationLogs({
                                        operator_id: operatorId,
                                        generation_unit_id: generationUnitId
                                    });
                                }
                            }
                        }, 100);
                    }
                }
                
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
                        allowClear: false, // لا تسمح بالمسح لأن هناك option فارغ
                        width: '100%',
                        placeholder: null
                    });
                    
                    // إذا كانت القيمة الحالية غير متاحة، امسح الاختيار
                    if (currentValue && currentValue != '0') {
                        const $selectedOption = $generatorFilter.find(`option[value="${currentValue}"]`);
                        if ($selectedOption.length && $selectedOption.prop('disabled')) {
                            $generatorFilter.val('0').trigger('change');
                        }
                    }
                });
            @endif
        });
    </script>
    <script src="{{ asset('assets/admin/js/operation-logs.js') }}"></script>
@endpush
