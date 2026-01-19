@extends('layouts.admin')

@section('title', 'إدارة المولدات')

@php
    $breadcrumbTitle = 'إدارة المولدات';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/generators.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page" id="generatorsPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-lightning-charge me-2"></i>
                                إدارة المولدات
                            </h5>
                            <div class="general-subtitle">
                                البحث والفلترة وإدارة المولدات. العدد: <span id="generatorsCount">{{ $generators->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\Generator::class)
                                <a href="{{ route('admin.generators.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة مولد جديد
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
                                    @php
                                        $user = auth()->user();
                                        $isCompanyOwner = $user->isCompanyOwner();
                                        $isEmployeeOrTechnician = $user->isEmployee() || $user->isTechnician();
                                        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority() || $user->isCivilDefense();
                                    @endphp

                                    {{-- فلتر المشغل --}}
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-building me-1"></i>
                                                المشغل
                                            </label>
                                        @if($canSelectOperator && isset($operators) && $operators->count() > 0)
                                            <select id="operatorFilter" class="form-select">
                                                <option value="">كل المشغلين</option>
                                                @foreach($operators as $op)
                                                    <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                                        {{ $op->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif(($isCompanyOwner || $isEmployeeOrTechnician) && isset($currentOperator))
                                            {{-- للمشغل والموظفين: المشغل معطل --}}
                                            <select id="operatorFilter" class="form-select" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                                <option value="{{ $currentOperator->id }}" selected>{{ $currentOperator->name }}</option>
                                            </select>
                                            <input type="hidden" name="operator_id" value="{{ $currentOperator->id }}">
                                        @endif
                                    </div>

                                    {{-- فلتر وحدات التوليد --}}
                                    @if(($isCompanyOwner || $isEmployeeOrTechnician) && isset($currentOperator) && isset($generationUnitsList))
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                وحدة التوليد
                                            </label>
                                            <select id="generationUnitFilter" class="form-select">
                                                <option value="">كل الوحدات</option>
                                                @foreach($generationUnitsList as $unit)
                                                    <option value="{{ $unit->id }}" {{ request('generation_unit_id') == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }} ({{ $unit->unit_code }})
                                                        @if($unit->statusDetail)
                                                            - {{ $unit->statusDetail->label }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($canSelectOperator)
                                        {{-- للأدوار الأخرى: فلتر وحدات التوليد يظهر عند اختيار المشغل --}}
                                        <div class="col-md-3" id="generationUnitFilterWrapper" style="display: none;">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                وحدة التوليد
                                            </label>
                                            <select id="generationUnitFilter" class="form-select">
                                                <option value="">كل الوحدات</option>
                                            </select>
                                        </div>
                                    @endif

                                    {{-- فلتر المولدات --}}
                                    @if(($isCompanyOwner || $isEmployeeOrTechnician) && isset($currentOperator) && isset($generationUnitsList))
                                        <div class="col-md-3" id="generatorFilterWrapper" style="display: none;">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-gear-wide-connected me-1"></i>
                                                المولد
                                            </label>
                                            <select id="generatorFilter" class="form-select">
                                                <option value="">كل المولدات</option>
                                            </select>
                                        </div>
                                    @elseif($canSelectOperator)
                                        <div class="col-md-3" id="generatorFilterWrapper" style="display: none;">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-gear-wide-connected me-1"></i>
                                                المولد
                                            </label>
                                            <select id="generatorFilter" class="form-select">
                                                <option value="">كل المولدات</option>
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-funnel me-1"></i>
                                            الحالة
                                        </label>
                                        <select id="statusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعال</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير فعال</option>
                                        </select>
                                    </div>
                                    </div>

                                {{-- صف جديد لزر البحث --}}
                                <div class="row g-3 mt-2">
                                    <div class="col-12 d-flex justify-content-center gap-2">
                                        <button class="btn btn-primary" type="button" id="searchBtn">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                            </button>
                                            <button
                                            class="btn btn-outline-secondary {{ request('operator_id') || request('generation_unit_id') || request('status') ? '' : 'd-none' }}"
                                                type="button"
                                            id="clearBtn"
                                            title="تفريغ الحقول"
                                            >
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تفريغ الحقول
                                            </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div id="generatorsListWrap" class="position-relative">
                            {{-- Loading overlay --}}
                            <div id="genLoading" class="data-table-loading d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>

                                @include('admin.generators.partials.list', ['generators' => $generators])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
(function() {
    const listUrl = @json(route('admin.generators.index'));
    const $wrap = $('#generatorsListWrap');
    let $loading = $('#genLoading');
    const $operatorFilter = $('#operatorFilter');
    const $generationUnitFilter = $('#generationUnitFilter');
    const $generatorFilter = $('#generatorFilter');
    const $statusFilter = $('#statusFilter');
    const $searchBtn = $('#searchBtn');
    const $clearBtn = $('#clearBtn');
    const $generationUnitFilterWrapper = $('#generationUnitFilterWrapper');
    const $generatorFilterWrapper = $('#generatorFilterWrapper');

    function setLoading(on) {
        if (on) {
            $loading.removeClass('d-none');
            $wrap.find('.table, .pagination, .card').hide();
        } else {
            $loading.addClass('d-none');
            $wrap.find('.table, .pagination, .card').show();
        }
    }

    function currentParams(extra = {}) {
        let operatorId = $operatorFilter.val() || '';
        if ($operatorFilter.prop('disabled')) {
            operatorId = $('input[name="operator_id"]').val() || '';
        }
        
        return Object.assign({
            operator_id: operatorId,
            generation_unit_id: $generationUnitFilter.val() || '',
            generator_id: $generatorFilter.val() || '',
            status: $statusFilter.val() || '',
        }, extra);
    }

    // ====== جلب وحدات التوليد عند تغيير المشغل ======
    function loadGenerationUnits(operatorId) {
        if (!operatorId || !$generationUnitFilterWrapper.length) return;

        $generationUnitFilterWrapper.show();
        $generationUnitFilter.prop('disabled', true);
        $generationUnitFilter.html('<option value="">جاري التحميل...</option>');
        $generatorFilterWrapper.hide();
        $generatorFilter.val('').html('<option value="">كل المولدات</option>');

        const status = $statusFilter.val() || '';
        const params = { status: status };

        $.ajax({
            url: `{{ url('admin/operators') }}/${operatorId}/generation-units-list`,
            method: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(res) {
                if (res && res.success && res.generation_units) {
                    $generationUnitFilter.html('<option value="">كل الوحدات</option>');
                    res.generation_units.forEach(function(unit) {
                        const statusLabel = unit.status_label ? ` - ${unit.status_label}` : '';
                        $generationUnitFilter.append(
                            `<option value="${unit.id}">${unit.name} (${unit.unit_code})${statusLabel}</option>`
                        );
                    });
                } else {
                    $generationUnitFilter.html('<option value="">لا توجد وحدات</option>');
                }
            },
            error: function() {
                $generationUnitFilter.html('<option value="">حدث خطأ</option>');
            },
            complete: function() {
                $generationUnitFilter.prop('disabled', false);
            }
        });
    }

    // ====== جلب المولدات عند تغيير وحدة التوليد ======
    function loadGenerators(generationUnitId) {
        if (!generationUnitId || !$generatorFilterWrapper.length) return;

        $generatorFilterWrapper.show();
        $generatorFilter.prop('disabled', true);
        $generatorFilter.html('<option value="">جاري التحميل...</option>');

        const status = $statusFilter.val() || '';
        const params = { status: status };

        $.ajax({
            url: `{{ url('admin/generation-units') }}/${generationUnitId}/generators-list`,
            method: 'GET',
            data: params,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(res) {
                if (res && res.success && res.generators) {
                    $generatorFilter.html('<option value="">كل المولدات</option>');
                    res.generators.forEach(function(generator) {
                        const statusLabel = generator.status_label ? ` - ${generator.status_label}` : '';
                        $generatorFilter.append(
                            `<option value="${generator.id}">${generator.name} (${generator.generator_number})${statusLabel}</option>`
                        );
                    });
                } else {
                    $generatorFilter.html('<option value="">لا توجد مولدات</option>');
                }
            },
            error: function() {
                $generatorFilter.html('<option value="">حدث خطأ</option>');
            },
            complete: function() {
                $generatorFilter.prop('disabled', false);
            }
        });
    }

    // عند تغيير المشغل
    $operatorFilter.on('change', function() {
        if ($(this).prop('disabled')) {
            return;
        }
        
        const operatorId = $(this).val();
        if (operatorId) {
            loadGenerationUnits(operatorId);
        } else {
            $generationUnitFilterWrapper.hide();
            $generationUnitFilter.val('').html('<option value="">كل الوحدات</option>');
            $generatorFilterWrapper.hide();
            $generatorFilter.val('').html('<option value="">كل المولدات</option>');
        }
        toggleClearBtn();
    });

    // عند تغيير وحدة التوليد
    $generationUnitFilter.on('change', function() {
        const generationUnitId = $(this).val();
        if (generationUnitId) {
            loadGenerators(generationUnitId);
        } else {
            $generatorFilterWrapper.hide();
            $generatorFilter.val('').html('<option value="">كل المولدات</option>');
        }
        toggleClearBtn();
    });

    // تحميل وحدات التوليد عند تحميل الصفحة إذا كان المشغل محدداً
    @if($canSelectOperator && request('operator_id'))
        loadGenerationUnits({{ request('operator_id') }});
    @endif

    // تحميل المولدات عند تحميل الصفحة إذا كانت وحدة التوليد محددة
    @if(request('generation_unit_id'))
        loadGenerators({{ request('generation_unit_id') }});
    @endif

    function loadList(extra = {}) {
        setLoading(true);
        $.ajax({
            url: listUrl,
            method: 'GET',
            data: currentParams(Object.assign({ ajax: 1 }, extra)),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                if (res && res.success) {
                    const loadingHtml = $loading[0].outerHTML;
                    $wrap.html(res.html);
                    $wrap.prepend(loadingHtml);
                    $loading = $('#genLoading');
                    $('#generatorsCount').text(res.count || 0);
                    wireListEvents();
                }
            },
            error: function () {
                alert('حدث خطأ أثناء تحميل البيانات');
            },
            complete: function () {
                setLoading(false);
            }
        });
    }

    function wireListEvents() {
        $wrap.find('.pagination a').off('click').on('click', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (!url) return;
            const u = new URL(url, window.location.origin);
            const page = u.searchParams.get('page') || 1;
            loadList({ page: page });
        });
    }

    function toggleClearBtn() {
        let operatorId = $operatorFilter.val() || '';
        if ($operatorFilter.prop('disabled')) {
            operatorId = '';
        }
        
        const hasValue = operatorId !== '' || 
                        $generationUnitFilter.val() !== '' ||
                        $generatorFilter.val() !== '' ||
                        $statusFilter.val() !== '';
        $clearBtn.toggleClass('d-none', !hasValue);
    }

    $searchBtn.on('click', function () {
        loadList({ page: 1 });
    });

    $clearBtn.on('click', function () {
        $operatorFilter.val('').trigger('change');
        $generationUnitFilter.val('').trigger('change');
        $generatorFilter.val('').trigger('change');
        $statusFilter.val('').trigger('change');
        loadList({ page: 1 });
    });

    $statusFilter.on('change', function() {
        toggleClearBtn();
    });

    // تحديث حالة زر المسح عند تحميل الصفحة
    toggleClearBtn();
})();
    </script>
@endpush
