@extends('layouts.admin')

@section('title', 'إدارة وحدات التوليد')

@php
    $breadcrumbTitle = 'إدارة وحدات التوليد';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-lightning-charge me-2"></i>
                                إدارة وحدات التوليد
                            </h5>
                            <div class="general-subtitle">
                                البحث والفلترة وإدارة وحدات التوليد. العدد: <span id="generationUnitsCount">{{ $generationUnits->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\GenerationUnit::class)
                                <a href="{{ route('admin.generation-units.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة وحدة توليد جديدة
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
                                    <div class="col-md-4">
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
                                        <div class="col-md-4">
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
                                        <div class="col-md-4" id="generationUnitFilterWrapper" style="display: none;">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-lightning-charge me-1"></i>
                                                وحدة التوليد
                                            </label>
                                            <select id="generationUnitFilter" class="form-select">
                                                <option value="">كل الوحدات</option>
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

                        <div id="generationUnitsListWrap" class="position-relative">
                            {{-- Loading overlay --}}
                            <div id="genLoading" class="data-table-loading d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>
                            
                            @include('admin.generation-units.partials.list', ['generationUnits' => $generationUnits])
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
    const listUrl = @json(route('admin.generation-units.index'));
    const $wrap = $('#generationUnitsListWrap');
    let $loading = $('#genLoading');
    const $operatorFilter = $('#operatorFilter');
    const $statusFilter = $('#statusFilter');
    const $searchBtn = $('#searchBtn');
    const $clearBtn = $('#clearBtn');

    function setLoading(on) {
        if (on) {
            // حفظ محتوى الجدول الحالي وعرض loading مكانه
            $loading.removeClass('d-none');
            $wrap.find('.table, .pagination, .card').hide();
        } else {
            // إخفاء loading وإظهار الجدول
            $loading.addClass('d-none');
            $wrap.find('.table, .pagination, .card').show();
        }
    }

    const $generationUnitFilter = $('#generationUnitFilter');
    const $generationUnitFilterWrapper = $('#generationUnitFilterWrapper');

    function currentParams(extra = {}) {
        // إذا كان select المشغل معطل، استخدم hidden input
        let operatorId = $operatorFilter.val() || '';
        if ($operatorFilter.prop('disabled')) {
            operatorId = $('input[name="operator_id"]').val() || '';
        }
        
        return Object.assign({
            operator_id: operatorId,
            generation_unit_id: $generationUnitFilter.val() || '',
            status: $statusFilter.val() || '',
        }, extra);
    }

    // ====== جلب وحدات التوليد عند تغيير المشغل ======
    function loadGenerationUnits(operatorId) {
        if (!operatorId || !$generationUnitFilterWrapper.length) return;

        $generationUnitFilterWrapper.show();
        $generationUnitFilter.prop('disabled', true);
        $generationUnitFilter.html('<option value="">جاري التحميل...</option>');

        const status = $statusFilter.val() || '';
        const params = { status: status };
        if (status) {
            params.status_id = status;
        }

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

    // عند تغيير المشغل، جلب وحدات التوليد فقط (بدون بحث تلقائي)
    $operatorFilter.on('change', function() {
        if ($(this).prop('disabled')) {
            return; // لا تفعل شيئاً إذا كان معطلاً
        }
        
        const operatorId = $(this).val();
        if (operatorId) {
            loadGenerationUnits(operatorId);
        } else {
            $generationUnitFilterWrapper.hide();
            $generationUnitFilter.val('').html('<option value="">كل الوحدات</option>');
        }
        toggleClearBtn();
        // لا نستدعي loadList هنا - فقط عند الضغط على زر البحث
    });

    // تحميل وحدات التوليد عند تحميل الصفحة إذا كان المشغل محدداً
    @if($canSelectOperator && request('operator_id'))
        loadGenerationUnits({{ request('operator_id') }});
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
                    // استبدال محتوى الجدول بالنتائج الجديدة (مع الحفاظ على loading overlay)
                    const loadingHtml = $loading[0].outerHTML;
                    $wrap.html(res.html);
                    // إعادة إضافة loading overlay
                    $wrap.prepend(loadingHtml);
                    $loading = $('#genLoading');
                    $('#generationUnitsCount').text(res.count || 0);
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
        // إذا كان select المشغل معطل، لا تأخذ قيمته في الاعتبار
        let operatorId = $operatorFilter.val() || '';
        if ($operatorFilter.prop('disabled')) {
            operatorId = ''; // لا تعتبر select المشغل كقيمة للفلترة لأنه معطل
        }
        
        const hasValue = operatorId !== '' || 
                        $generationUnitFilter.val() !== '' ||
                        $statusFilter.val() !== '';
        $clearBtn.toggleClass('d-none', !hasValue);
    }

    $searchBtn.on('click', function () {
        loadList({ page: 1 });
    });

    $clearBtn.on('click', function () {
        $operatorFilter.val('').trigger('change');
        $generationUnitFilter.val('').trigger('change');
        $statusFilter.val('').trigger('change');
        loadList({ page: 1 });
    });

    // تحديث حالة زر المسح فقط عند تغيير الفلاتر (بدون بحث تلقائي)
    $operatorFilter.on('change', function() {
        toggleClearBtn();
        // لا نستدعي loadList هنا - فقط عند الضغط على زر البحث
    });
    $generationUnitFilter.on('change', function() {
        toggleClearBtn();
        // لا نستدعي loadList هنا - فقط عند الضغط على زر البحث
    });
    $statusFilter.on('change', function() {
        toggleClearBtn();
        // عند تغيير الحالة، إعادة جلب وحدات التوليد إذا كان المشغل محدداً (بدون بحث تلقائي)
        const operatorId = $operatorFilter.val() || ($operatorFilter.prop('disabled') ? $('input[name="operator_id"]').val() : '');
        if (operatorId && !$operatorFilter.prop('disabled')) {
            loadGenerationUnits(operatorId);
        }
        // لا نستدعي loadList هنا - فقط عند الضغط على زر البحث
    });

    wireListEvents();
    toggleClearBtn();

    // ====== تحديث الجدول عند طباعة QR Code ======
    let qrWindowOpen = false;
    
    $(document).on('click', 'a[href*="qr-code"]', function(e) {
        qrWindowOpen = true;
        
        // مراقبة إغلاق النافذة
        const checkWindow = setInterval(function() {
            // محاولة الوصول للنافذة المفتوحة
            try {
                const href = $(e.target).closest('a').attr('href');
                if (href) {
                    // التحقق من إغلاق النافذة من خلال محاولة الوصول لها
                    // لكن هذا قد لا يعمل بسبب same-origin policy
                }
            } catch(err) {
                // ignore
            }
        }, 500);
        
        // تنظيف interval بعد 5 دقائق
        setTimeout(function() {
            clearInterval(checkWindow);
            qrWindowOpen = false;
        }, 300000);
    });
    
    // تحديث الجدول عند العودة للصفحة (visibilitychange event)
    let isDocumentVisible = !document.hidden;
    document.addEventListener('visibilitychange', function() {
        const wasVisible = isDocumentVisible;
        isDocumentVisible = !document.hidden;
        
        // إذا كانت الصفحة مخفية وأصبحت ظاهرة (عاد المستخدم للصفحة)
        if (!wasVisible && isDocumentVisible && qrWindowOpen) {
            setTimeout(function() {
                const currentPage = $wrap.find('.pagination .page-item.active .page-link').text() || 1;
                loadList({ page: parseInt(currentPage) || 1 });
                qrWindowOpen = false;
            }, 1000);
        }
    });
    
    // تحديث الجدول عند العودة للصفحة (focus event كـ backup)
    $(window).on('focus', function() {
        if (qrWindowOpen) {
            setTimeout(function() {
                const currentPage = $wrap.find('.pagination .page-item.active .page-link').text() || 1;
                loadList({ page: parseInt(currentPage) || 1 });
                qrWindowOpen = false;
            }, 1500);
        }
    });

    // ====== حذف وحدة التوليد ======
    $(document).on('click', '.delete-generation-unit-btn', function() {
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');

        if (!confirm(`هل أنت متأكد من حذف وحدة التوليد "${name}"؟\n\nملاحظة: لا يمكن حذف وحدة التوليد إذا كانت تحتوي على مولدات أو سجلات مرتبطة.\n\nهذا الإجراء لا يمكن التراجع عنه!`)) {
            return;
        }

        setLoading(true);
        btn.prop('disabled', true);

        $.ajax({
            url: `{{ url('admin/generation-units') }}/${id}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('#csrfToken').val(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(res) {
                if (res && res.success) {
                    if (window.adminNotifications && typeof window.adminNotifications.success === 'function') {
                        window.adminNotifications.success(res.message || 'تم الحذف بنجاح');
                    } else {
                        alert(res.message || 'تم الحذف بنجاح');
                    }
                    // إعادة تحميل القائمة
                    loadList({ page: 1 });
                } else {
                    if (window.adminNotifications && typeof window.adminNotifications.error === 'function') {
                        window.adminNotifications.error(res.message || 'فشل الحذف');
                    } else {
                        alert(res.message || 'فشل الحذف');
                    }
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'حدث خطأ أثناء الحذف';
                if (xhr.status === 422) {
                    message = xhr.responseJSON?.message || 'لا يمكن حذف وحدة التوليد لأنها تحتوي على مولدات أو سجلات مرتبطة';
                }
                if (window.adminNotifications && typeof window.adminNotifications.error === 'function') {
                    window.adminNotifications.error(message);
                } else {
                    alert(message);
                }
                btn.prop('disabled', false);
            },
            complete: function() {
                setLoading(false);
            }
        });
    });
})();
</script>
@endpush
