@extends('layouts.admin')

@section('title', 'قضايا التفتيش والتعدي')

@php
    $breadcrumbTitle = 'قضايا التفتيش والتعدي';
    $breadcrumbParent = 'السجلات';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <div class="general-page" id="inspectionCasesPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                قضايا التفتيش والتعدي
                            </h5>
                            <div class="general-subtitle">
                                الاستعلام باسم مالك الوحدة أو المشغل أو وحدة التوليد والتحقق من حالة القضية. العدد: <span id="casesCount">{{ $cases->total() }}</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('create', App\Models\InspectionViolationCase::class)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    استيراد من Excel
                                </button>
                                <a href="{{ route('admin.inspection-violation-cases.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة قضية
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body pb-4">
                        <div class="filter-card">
                            <div class="card-header">
                                <h6 class="card-title"><i class="bi bi-funnel me-2"></i>فلاتر البحث</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- المشغل ووحدة التوليد (نفس أسلوب سجلات السلامة: مشغل ثم وحدة التوليد) --}}
                                    @if(($operators ?? collect())->isNotEmpty())
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label fw-semibold" for="operatorFilter"><i class="bi bi-building me-1"></i>المشغل</label>
                                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                                                <select id="operatorFilter" class="form-select">
                                                    <option value="">كل المشغلين</option>
                                                    @foreach($operators as $op)
                                                        <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>{{ $op->unit_number ? $op->unit_number . ' - ' : '' }}{{ $op->name }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                @php $operator = $operators->first(); @endphp
                                                <select id="operatorFilter" class="form-select" disabled>
                                                    <option value="{{ $operator->id }}" selected>{{ $operator->unit_number ?? '' }}{{ $operator->unit_number ? ' - ' : '' }}{{ $operator->name }}</option>
                                                </select>
                                                <input type="hidden" id="operatorFilterHidden" value="{{ $operator->id }}">
                                            @endif
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label fw-semibold" for="generationUnitFilter"><i class="bi bi-lightning-charge me-1"></i>وحدة التوليد</label>
                                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isEnergyAuthority())
                                                <select id="generationUnitFilter" class="form-select" {{ !request('operator_id') ? 'disabled' : '' }}>
                                                    <option value="">{{ request('operator_id') ? 'كل الوحدات' : 'اختر المشغل أولاً' }}</option>
                                                    @if(request('operator_id') && ($generationUnits ?? collect())->isNotEmpty())
                                                        @foreach($generationUnits as $gu)
                                                            <option value="{{ $gu->id }}" {{ request('generation_unit_id') == $gu->id ? 'selected' : '' }}>{{ $gu->name }} ({{ $gu->unit_code ?? '' }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @else
                                                <select id="generationUnitFilter" class="form-select">
                                                    <option value="">كل الوحدات</option>
                                                    @foreach($generationUnits ?? [] as $gu)
                                                        <option value="{{ $gu->id }}" {{ request('generation_unit_id') == $gu->id ? 'selected' : '' }}>{{ $gu->name }} ({{ $gu->unit_code ?? '' }})</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="governorateFilter"><i class="bi bi-geo-alt me-1"></i>المحافظة</label>
                                        <select id="governorateFilter" class="form-select">
                                            <option value="">كل المحافظات</option>
                                            @foreach($governorates ?? [] as $g)
                                                <option value="{{ $g->value }}" {{ request('governorate_id') == $g->value ? 'selected' : '' }}>{{ $g->label ?? $g->name ?? $g->id }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="caseDateFromFilter"><i class="bi bi-calendar-event me-1"></i>من تاريخ</label>
                                        <input type="date" id="caseDateFromFilter" class="form-control" value="{{ request('case_date_from', '') }}">
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="caseDateToFilter"><i class="bi bi-calendar-check me-1"></i>إلى تاريخ</label>
                                        <input type="date" id="caseDateToFilter" class="form-control" value="{{ request('case_date_to', '') }}">
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold" for="statusFilter">حالة القضية</label>
                                        <select id="statusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>ادخال</option>
                                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>محكومة</option>
                                            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>منتهية</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label fw-semibold" for="searchInput">البحث</label>
                                        <input type="text" id="searchInput" class="form-control" placeholder="اسم المشترك، المنتفع، رقم الاشتراك، المشغل، وحدة التوليد..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-12 d-flex flex-wrap justify-content-center gap-2 align-items-center">
                                        <button class="btn btn-primary" type="button" id="searchBtn"><i class="bi bi-search me-1"></i>بحث</button>
                                        <button type="button" class="btn btn-outline-success" id="exportBtn">
                                            <i class="bi bi-download me-1"></i>تصدير Excel
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary {{ request('operator_id') || request('generation_unit_id') || request('governorate_id') || request('case_date_from') || request('case_date_to') || request('status') || request('search') ? '' : 'd-none' }}" id="clearBtn"><i class="bi bi-x me-1"></i>تفريغ</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div id="casesListWrap" class="position-relative">
                            <div id="casesLoading" class="data-table-loading d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>
                            @include('admin.inspection-violation-cases.partials.list', ['cases' => $cases])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@can('create', App\Models\InspectionViolationCase::class)
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white bg-primary">
                <h5 class="modal-title text-white"><i class="bi bi-file-earmark-excel me-2 text-white"></i>استيراد قضايا التفتيش والتعدي من Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="importStep1">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="bi bi-building me-1"></i>المشغل <span class="text-danger">*</span></label>
                            <select id="importOperatorId" class="form-select" required>
                                <option value="">-- اختر المشغل --</option>
                                @foreach($operators ?? [] as $op)
                                    <option value="{{ $op->id }}">{{ $op->unit_number ? $op->unit_number . ' - ' : '' }}{{ $op->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="bi bi-lightning-charge me-1"></i>وحدة التوليد</label>
                            <select id="importGenerationUnitId" class="form-select" disabled>
                                <option value="">اختر المشغل أولاً</option>
                            </select>
                        </div>
                    </div>
                    <label class="form-label fw-bold">ملف Excel <span class="text-danger">*</span></label>
                    <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls,.csv">
                    <div class="form-text">الأعمدة: المحافظة، رقم_الاشتراك، اسم_المشترك، اسم_المنتفع، تاريخ_القضية، حالة_القضية (1=ادخال 2=محكومة 3=منتهية)، بيان_القضية</div>
                    <a href="{{ route('admin.inspection-violation-cases.import.template') }}" class="btn btn-outline-success btn-sm mt-2">
                        <i class="bi bi-download me-1"></i>تحميل نموذج Excel
                    </a>
                </div>
                <div id="importStep2" class="d-none">
                    <div id="importPreviewContent"></div>
                    <button type="button" class="btn btn-outline-secondary mt-2" id="backToStep1Btn">العودة</button>
                    <button type="button" class="btn btn-success mt-2 d-none" id="executeImportBtn"><i class="bi bi-check-lg me-1"></i>تنفيذ الاستيراد</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function() {
    const listUrl = @json(route('admin.inspection-violation-cases.index'));
    const $wrap = $('#casesListWrap');
    const $loading = $('#casesLoading');
    const $operatorFilter = $('#operatorFilter');
    const $operatorFilterHidden = $('#operatorFilterHidden');
    const $generationUnitFilter = $('#generationUnitFilter');
    const $governorateFilter = $('#governorateFilter');
    const $caseDateFromFilter = $('#caseDateFromFilter');
    const $caseDateToFilter = $('#caseDateToFilter');
    const $statusFilter = $('#statusFilter');
    const $searchInput = $('#searchInput');
    const $searchBtn = $('#searchBtn');
    const $clearBtn = $('#clearBtn');
    const $exportBtn = $('#exportBtn');
    window.casesGenerationUnits = @json($generationUnitsByOperator ?? []);

    function getOperatorId() {
        if ($operatorFilterHidden.length && $operatorFilterHidden.val()) return $operatorFilterHidden.val();
        return $operatorFilter.length ? $operatorFilter.val() || '' : '';
    }

    function params(extra) {
        extra = extra || {};
        return Object.assign({
            operator_id: getOperatorId(),
            generation_unit_id: $generationUnitFilter.length ? $generationUnitFilter.val() || '' : '',
            governorate_id: $governorateFilter.length ? $governorateFilter.val() || '' : '',
            case_date_from: $caseDateFromFilter.length ? $caseDateFromFilter.val() || '' : '',
            case_date_to: $caseDateToFilter.length ? $caseDateToFilter.val() || '' : '',
            status: $statusFilter.val() || '',
            search: $searchInput.val() || '',
        }, extra);
    }


    function setLoading(on) {
        if (on) {
            $loading.removeClass('d-none');
            $wrap.find('.table, .pagination, .card').hide();
        } else {
            $loading.addClass('d-none');
            $wrap.find('.table, .pagination, .card').show();
        }
    }

    function loadCases() {
        setLoading(true);
        $.ajax({
            url: listUrl,
            method: 'GET',
            data: params(),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(r) {
                if (r.success) {
                    $wrap.html(r.html);
                    $('#casesCount').text(r.count);
                    var p = params();
                    if (p.operator_id || p.generation_unit_id || p.governorate_id || p.case_date_from || p.case_date_to || p.status || p.search) $clearBtn.removeClass('d-none');
                    else $clearBtn.addClass('d-none');
                }
            },
            complete: function() { setLoading(false); }
        });
    }

    $searchBtn.on('click', loadCases);
    $operatorFilter.on('change', function() {
        var opId = $(this).val();
        // للسوبر أدمن والأدمن: تحديث وحدات التوليد بناءً على المشغل المختار
        if (!$operatorFilter.prop('disabled')) {
            if (opId && window.casesGenerationUnits && window.casesGenerationUnits[opId]) {
                var opts = window.casesGenerationUnits[opId].map(function(u) {
                    return '<option value="' + u.id + '">' + u.name + ' (' + (u.unit_code || '') + ')</option>';
                }).join('');
                $generationUnitFilter.html('<option value="">كل الوحدات</option>' + opts).prop('disabled', false);
            } else {
                $generationUnitFilter.html('<option value="">اختر المشغل أولاً</option>').prop('disabled', true);
            }
        }
        loadCases();
    });
    $generationUnitFilter.on('change', loadCases);
    $governorateFilter.on('change', loadCases);
    $caseDateFromFilter.on('change', loadCases);
    $caseDateToFilter.on('change', loadCases);
    $statusFilter.on('change', loadCases);
    $searchInput.on('keypress', function(e) { if (e.which === 13) loadCases(); });

    $clearBtn.on('click', function() {
        if (!$operatorFilter.prop('disabled')) {
            $operatorFilter.val('');
            $generationUnitFilter.html('<option value="">اختر المشغل أولاً</option>').prop('disabled', true);
        } else {
            $generationUnitFilter.val('');
        }
        $governorateFilter.val('');
        $caseDateFromFilter.val('');
        $caseDateToFilter.val('');
        $statusFilter.val('');
        $searchInput.val('');
        loadCases();
    });

    $(document).on('submit', '.delete-case-form', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(r) {
                if (r.success) { loadCases(); if (window.adminNotifications) window.adminNotifications.success(r.message); }
            }
        });
    });

    $exportBtn.on('click', function(e) {
        e.preventDefault();
        var q = params();
        var parts = [];
        $.each(q, function(k, v) { if (v) parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(v)); });
        var baseHref = @json(route('admin.inspection-violation-cases.export'));
        var finalHref = baseHref + (parts.length ? '?' + parts.join('&') : '');
        window.location.href = finalHref;
    });

    window.loadInspectionCases = loadCases;
})();
</script>
@if(auth()->user()->can('create', App\Models\InspectionViolationCase::class))
<script>
(function() {
    const previewUrl = @json(route('admin.inspection-violation-cases.import.preview'));
    const executeUrl = @json(route('admin.inspection-violation-cases.import.execute'));
    const $modal = $('#importModal');
    const $fileInput = $('#importFile');
    const $step1 = $('#importStep1');
    const $step2 = $('#importStep2');
    const $backBtn = $('#backToStep1Btn');
    const $executeBtn = $('#executeImportBtn');
    const $previewContent = $('#importPreviewContent');
    const $importOperator = $('#importOperatorId');
    const $importGenUnit = $('#importGenerationUnitId');
    const importGenUnits = @json($generationUnitsByOperator ?? []);
    let currentFilePath = null;

    // Cascade generation units when operator changes (register handler FIRST)
    $importOperator.on('change', function() {
        var opId = $(this).val();
        if (opId && importGenUnits[opId]) {
            var opts = importGenUnits[opId].map(function(u) {
                return '<option value="' + u.id + '">' + u.name + ' (' + (u.unit_code || '') + ')</option>';
            }).join('');
            $importGenUnit.html('<option value="">-- كل الوحدات --</option>' + opts).prop('disabled', false);
        } else {
            $importGenUnit.html('<option value="">اختر المشغل أولاً</option>').prop('disabled', true);
        }
    });

    // Auto-select operator if only one option (AFTER registering handler)
    var importOpOptions = $importOperator.find('option[value!=""]');
    if (importOpOptions.length === 1) {
        $importOperator.val(importOpOptions.first().val()).trigger('change');
    }

    function step1() {
        $step2.addClass('d-none');
        $step1.removeClass('d-none');
        $fileInput.val('');
        currentFilePath = null;
    }
    function step2(html, filePath) {
        currentFilePath = filePath;
        $step1.addClass('d-none');
        $step2.removeClass('d-none');
        $previewContent.html(html);
        $executeBtn.removeClass('d-none');
    }

    $fileInput.on('change', function() {
        var file = this.files[0];
        if (!file) return;
        var operatorId = $importOperator.val();
        if (!operatorId) {
            alert('يرجى اختيار المشغل أولاً');
            $fileInput.val('');
            return;
        }
        var fd = new FormData();
        fd.append('file', file);
        fd.append('operator_id', operatorId);
        fd.append('generation_unit_id', $importGenUnit.val() || '');
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        $.ajax({
            url: previewUrl,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(r) {
                if (r.success) {
                    var msg = 'صالح: ' + (r.results.valid_count || 0) + '، أخطاء: ' + (r.results.error_count || 0);
                    step2('<p class="text-success">' + msg + '</p>', r.results.file_path);
                } else {
                    alert(r.message || 'خطأ في المعاينة');
                }
            }
        });
    });

    $backBtn.on('click', step1);
    $executeBtn.on('click', function() {
        if (!currentFilePath) return;
        $.ajax({
            url: executeUrl,
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                file_path: currentFilePath
            },
            success: function(r) {
                if (r.success) {
                    $modal.modal('hide');
                    step1();
                    if (window.loadInspectionCases) window.loadInspectionCases();
                    if (window.adminNotifications) window.adminNotifications.success(r.message);
                } else {
                    alert(r.message || 'فشل الاستيراد');
                }
            }
        });
    });

    $modal.on('hidden.bs.modal', step1);
})();
</script>
@endif
@endpush
