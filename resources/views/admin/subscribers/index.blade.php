@extends('layouts.admin')

@section('title', 'إدارة بيانات المشتركين')

@php
    $breadcrumbTitle = 'إدارة بيانات المشتركين';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <style>
        /* Import Modal Styles */
        #importModal .modal-header {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.85) 0%, rgba(var(--primary-rgb), 0.65) 100%) !important;
            color: #fff !important;
        }
        #importModal .modal-header .modal-title,
        #importModal .modal-header .modal-title i {
            color: #fff !important;
        }
        #importModal .card-header.bg-primary {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.8) 0%, rgba(var(--primary-rgb), 0.65) 100%) !important;
            color: #fff !important;
        }
        #importModal .card-header.bg-info {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.5) 0%, rgba(var(--primary-rgb), 0.35) 100%) !important;
            color: #fff !important;
        }
        #importModal .display-6 {
            font-size: 2.5rem;
        }
        #importModal .nav-tabs .nav-link {
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        #importModal .nav-tabs .nav-link.active {
            border-bottom: 3px solid rgb(var(--primary-rgb));
        }
        #importModal .table thead.sticky-top {
            z-index: 1;
        }
        #importModal .table thead.table-success {
            background: rgba(var(--primary-rgb), 0.15) !important;
        }
        #importModal .table thead.table-danger {
            background: #f8d7da !important;
        }
        #previewTabsContent {
            min-height: 200px;
        }
        .bg-success-subtle {
            background-color: rgba(var(--primary-rgb), 0.1) !important;
        }
        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
        #importModal .btn-primary {
            background: rgba(var(--primary-rgb), 0.85) !important;
            border-color: rgba(var(--primary-rgb), 0.85) !important;
        }
        #importModal .btn-primary:hover {
            background: rgb(var(--primary-rgb)) !important;
            border-color: rgb(var(--primary-rgb)) !important;
        }
        #importModal .border-primary {
            border-color: rgba(var(--primary-rgb), 0.5) !important;
        }
    </style>
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page" id="subscribersPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-people me-2"></i>
                                إدارة بيانات المشتركين
                            </h5>
                            <div class="general-subtitle">
                                البحث والفلترة وإدارة بيانات المشتركين. العدد: <span id="subscribersCount">{{ $subscribers->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('create', App\Models\Subscriber::class)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    استيراد من Excel
                                </button>
                                <a href="{{ route('admin.subscribers.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة مشترك جديد
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
                                        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
                                        $subscriptionStatuses = \App\Helpers\ConstantsHelper::get(25);
                                        $subscriberTypes      = \App\Helpers\ConstantsHelper::get(27);
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
                                            <select id="operatorFilter" class="form-select" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                                <option value="{{ $currentOperator->id }}" selected>{{ $currentOperator->name }}</option>
                                            </select>
                                            <input type="hidden" name="operator_id" value="{{ $currentOperator->id }}">
                                        @endif
                                    </div>

                                    {{-- فلتر حالة الاشتراك --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-funnel me-1"></i>
                                            حالة الاشتراك
                                        </label>
                                        <select id="subscriptionStatusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            @foreach($subscriptionStatuses as $item)
                                                <option value="{{ $item->value }}" {{ request('subscription_status') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- فلتر نوع المشترك --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person-badge me-1"></i>
                                            نوع المشترك
                                        </label>
                                        <select id="isEmployeeFilter" class="form-select">
                                            <option value="">الكل</option>
                                            @foreach($subscriberTypes as $item)
                                                <option value="{{ $item->value }}" {{ request('is_employee') === $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- البحث --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input type="text" id="searchInput" class="form-control" placeholder="ابحث برقم الاشتراك، رقم الهوية، الاسم، الجوال، أو رقم العداد..." value="{{ request('search') }}">
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
                                            class="btn btn-outline-secondary {{ request('operator_id') || request('subscription_status') || request('is_employee') !== null && request('is_employee') !== '' || request('search') ? '' : 'd-none' }}"
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

                        <div id="subscribersListWrap" class="position-relative">
                            {{-- Loading overlay --}}
                            <div id="subLoading" class="data-table-loading d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>

                            @include('admin.subscribers.partials.list', ['subscribers' => $subscribers])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Import Modal --}}
@can('create', App\Models\Subscriber::class)
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-file-earmark-excel me-2"></i>
                    استيراد المشتركين من Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Step 1: File Upload --}}
                <div id="importStep1">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-upload me-2"></i>
                                    رفع الملف
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-building me-1"></i>
                                            وحدة التوليد <span class="text-danger">*</span>
                                        </label>
                                        <select id="importGenerationUnit" class="form-select" required>
                                            <option value="">-- اختر وحدة التوليد --</option>
                                            @foreach($generationUnits ?? [] as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">سيتم إضافة جميع المشتركين إلى هذه الوحدة</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                                            ملف Excel <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls,.csv" required>
                                        <div class="form-text">الصيغ المدعومة: xlsx, xls, csv (الحد الأقصى: 5 ميجابايت)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-info-circle me-2"></i>
                                    تعليمات الاستيراد
                                </div>
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2">الأعمدة المطلوبة:</h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="bi bi-check-circle text-success me-1"></i> رقم_الهوية (فريد)</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i> اسم_المشترك</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i> رقم_الموبايل (059/056)</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i> العنوان</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i> رقم_العداد (فريد)</li>
                                    </ul>
                                    <h6 class="fw-bold mb-2 mt-3">الأعمدة الاختيارية:</h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="bi bi-dash-circle text-secondary me-1"></i> تصنيف_الاشتراك (منزلي/تجاري/صناعي/زراعي)</li>
                                        <li><i class="bi bi-dash-circle text-secondary me-1"></i> نوع_الفاز (1 فاز / 3 فاز)</li>
                                        <li><i class="bi bi-dash-circle text-secondary me-1"></i> نوع_الخدمة (مولد/شبكة/مختلط)</li>
                                    </ul>
                                    <div class="mt-3">
                                        <a href="{{ route('admin.subscribers.import.template') }}" class="btn btn-outline-success btn-sm w-100">
                                            <i class="bi bi-download me-1"></i>
                                            تحميل نموذج Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Preview --}}
                <div id="importStep2" class="d-none">
                    {{-- Summary Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0 text-center">
                                <div class="card-body py-3">
                                    <div class="display-6 text-primary fw-bold" id="totalRowsCount">0</div>
                                    <small class="text-muted">إجمالي الصفوف</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success-subtle border-0 text-center">
                                <div class="card-body py-3">
                                    <div class="display-6 text-success fw-bold" id="validRowsCount">0</div>
                                    <small class="text-muted">صالحة للاستيراد</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger-subtle border-0 text-center">
                                <div class="card-body py-3">
                                    <div class="display-6 text-danger fw-bold" id="errorRowsCount">0</div>
                                    <small class="text-muted">بها أخطاء</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Tabs --}}
                    <ul class="nav nav-tabs" id="previewTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="valid-tab" data-bs-toggle="tab" data-bs-target="#valid-pane" type="button">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                صالحة (<span id="validTabCount">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="errors-tab" data-bs-toggle="tab" data-bs-target="#errors-pane" type="button">
                                <i class="bi bi-x-circle text-danger me-1"></i>
                                بها أخطاء (<span id="errorsTabCount">0</span>)
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 rounded-bottom" id="previewTabsContent">
                        <div class="tab-pane fade show active p-3" id="valid-pane" role="tabpanel">
                            <div class="table-responsive" style="max-height: 350px;">
                                <table class="table table-sm table-hover table-striped mb-0 text-center">
                                    <thead class="table-success sticky-top">
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">رقم الهوية</th>
                                            <th class="text-center">الاسم</th>
                                            <th class="text-center">الموبايل</th>
                                            <th class="text-center">العنوان</th>
                                            <th class="text-center">رقم العداد</th>
                                            <th class="text-center">التصنيف</th>
                                            <th class="text-center">نوع الفاز</th>
                                        </tr>
                                    </thead>
                                    <tbody id="validRowsBody">
                                        <tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade p-3" id="errors-pane" role="tabpanel">
                            <div class="table-responsive" style="max-height: 350px;">
                                <table class="table table-sm table-hover mb-0 text-center">
                                    <thead class="table-danger sticky-top">
                                        <tr>
                                            <th class="text-center">الصف</th>
                                            <th class="text-center">البيانات</th>
                                            <th class="text-center">الخطأ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="errorRowsBody">
                                        <tr><td colspan="3" class="text-center text-muted py-4">لا توجد أخطاء</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Import Result --}}
                <div id="importStep3" class="d-none">
                    <div class="text-center py-5">
                        <div id="importResultIcon"></div>
                        <h4 id="importResultTitle" class="mt-3"></h4>
                        <p id="importResultMessage" class="text-muted"></p>
                    </div>
                </div>

                {{-- Loading --}}
                <div id="importLoading" class="d-none text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <h5 class="mt-3 text-primary" id="loadingText">جاري معالجة الملف...</h5>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeModalBtn">
                    <i class="bi bi-x-lg me-1"></i>
                    إغلاق
                </button>
                <button type="button" class="btn btn-outline-secondary d-none" id="backToStep1Btn">
                    <i class="bi bi-arrow-right me-1"></i>
                    العودة
                </button>
                <button type="button" class="btn btn-primary" id="previewBtn">
                    <i class="bi bi-eye me-1"></i>
                    معاينة
                </button>
                <button type="button" class="btn btn-success d-none" id="executeImportBtn">
                    <i class="bi bi-check-lg me-1"></i>
                    تنفيذ الاستيراد
                </button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
    <script>
        (function() {
            const listUrl = @json(route('admin.subscribers.index'));
            const $wrap = $('#subscribersListWrap');
            let $loading = $('#subLoading');
            const $operatorFilter = $('#operatorFilter');
            const $subscriptionStatusFilter = $('#subscriptionStatusFilter');
            const $searchInput = $('#searchInput');
            const $searchBtn = $('#searchBtn');
            const $clearBtn = $('#clearBtn');

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
                
                const isEmployee = $('#isEmployeeFilter').val();
                return Object.assign({
                    operator_id: operatorId,
                    subscription_status: $subscriptionStatusFilter.val() || '',
                    is_employee: (isEmployee !== null && isEmployee !== undefined) ? isEmployee : '',
                    search: $searchInput.val() || '',
                }, extra);
            }

            function loadSubscribers() {
                setLoading(true);
                const params = currentParams();
                
                $.ajax({
                    url: listUrl,
                    method: 'GET',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    success: function(response) {
                        if (response.success) {
                            $wrap.html(response.html);
                            $('#subscribersCount').text(response.count);
                            
                            // إظهار/إخفاء زر التفريغ
                            if (params.operator_id || params.subscription_status || params.is_employee !== '' || params.search) {
                                $clearBtn.removeClass('d-none');
                            } else {
                                $clearBtn.addClass('d-none');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading subscribers:', xhr);
                        alert('حدث خطأ أثناء تحميل البيانات');
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            }

            $searchBtn.on('click', loadSubscribers);
            $operatorFilter.on('change', loadSubscribers);
            $subscriptionStatusFilter.on('change', loadSubscribers);
            $('#isEmployeeFilter').on('change', loadSubscribers);
            $searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    loadSubscribers();
                }
            });

            $clearBtn.on('click', function() {
                $operatorFilter.val('').trigger('change');
                $subscriptionStatusFilter.val('').trigger('change');
                $('#isEmployeeFilter').val('');
                $searchInput.val('');
                loadSubscribers();
            });

            // معالجة الحذف بـ AJAX
            $(document).on('submit', '.delete-subscriber-form', function(e) {
                e.preventDefault();
                
                if (!confirm('هل أنت متأكد من حذف هذا المشترك؟')) {
                    return false;
                }
                
                const form = $(this);
                const url = form.attr('action');
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    success: function(response) {
                        if (response.success) {
                            if (window.adminNotifications) {
                                window.adminNotifications.success(response.message || 'تم حذف المشترك بنجاح');
                            }
                            loadSubscribers(); // إعادة تحميل القائمة
                        } else {
                            if (window.adminNotifications) {
                                window.adminNotifications.error(response.message || 'حدث خطأ أثناء الحذف');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error deleting subscriber:', xhr);
                        if (window.adminNotifications) {
                            window.adminNotifications.error('حدث خطأ أثناء حذف المشترك');
                        }
                    }
                });
            });

            // Make loadSubscribers accessible globally
            window.loadSubscribers = loadSubscribers;
        })();

        // Import Modal Logic
        (function() {
            const previewUrl = @json(route('admin.subscribers.import.preview'));
            const executeUrl = @json(route('admin.subscribers.import.execute'));
            
            let currentFilePath = null;
            let validRows = [];
            let errorRows = [];

            const $modal = $('#importModal');
            const $step1 = $('#importStep1');
            const $step2 = $('#importStep2');
            const $step3 = $('#importStep3');
            const $loading = $('#importLoading');
            const $loadingText = $('#loadingText');
            
            const $previewBtn = $('#previewBtn');
            const $backToStep1Btn = $('#backToStep1Btn');
            const $executeImportBtn = $('#executeImportBtn');
            
            const $generationUnit = $('#importGenerationUnit');
            const $fileInput = $('#importFile');

            function showStep(step) {
                $step1.addClass('d-none');
                $step2.addClass('d-none');
                $step3.addClass('d-none');
                $loading.addClass('d-none');
                
                $previewBtn.addClass('d-none');
                $backToStep1Btn.addClass('d-none');
                $executeImportBtn.addClass('d-none');
                
                if (step === 1) {
                    $step1.removeClass('d-none');
                    $previewBtn.removeClass('d-none');
                } else if (step === 2) {
                    $step2.removeClass('d-none');
                    $backToStep1Btn.removeClass('d-none');
                    if (validRows.length > 0) {
                        $executeImportBtn.removeClass('d-none');
                    }
                } else if (step === 3) {
                    $step3.removeClass('d-none');
                } else if (step === 'loading') {
                    $loading.removeClass('d-none');
                }
            }

            function showLoading(text = 'جاري معالجة الملف...') {
                $loadingText.text(text);
                showStep('loading');
            }

            function resetModal() {
                $fileInput.val('');
                $generationUnit.val('');
                validRows = [];
                errorRows = [];
                currentFilePath = null;
                showStep(1);
            }

            function renderValidRows() {
                const $tbody = $('#validRowsBody');
                if (validRows.length === 0) {
                    $tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات صالحة</td></tr>');
                    return;
                }
                
                let html = '';
                validRows.forEach((row, idx) => {
                    html += `<tr>
                        <td>${row.row_number || (idx + 1)}</td>
                        <td>${row.subscriber_id_number || '-'}</td>
                        <td>${row.name || '-'}</td>
                        <td dir="ltr">${row.phone || '-'}</td>
                        <td>${row.address || '-'}</td>
                        <td>${row.meter_number || '-'}</td>
                        <td><span class="badge bg-secondary">${getCategoryLabel(row.category)}</span></td>
                        <td><span class="badge bg-info">${getPhaseLabel(row.phase_type)}</span></td>
                    </tr>`;
                });
                $tbody.html(html);
            }

            function renderErrorRows() {
                const $tbody = $('#errorRowsBody');
                if (errorRows.length === 0) {
                    $tbody.html('<tr><td colspan="3" class="text-center text-muted py-4">لا توجد أخطاء - جميع البيانات صالحة</td></tr>');
                    return;
                }
                
                let html = '';
                errorRows.forEach(row => {
                    const errors = row.errors.join('، ');
                    html += `<tr class="table-danger">
                        <td class="fw-bold">${row.row_number}</td>
                        <td>
                            <small>
                                <strong>الاسم:</strong> ${row.data.name || '-'} | 
                                <strong>الهوية:</strong> ${row.data.subscriber_id_number || '-'} | 
                                <strong>الموبايل:</strong> ${row.data.phone || '-'}
                            </small>
                        </td>
                        <td class="text-danger small">${errors}</td>
                    </tr>`;
                });
                $tbody.html(html);
            }

            function getCategoryLabel(category) {
                const labels = {
                    'residential': 'منزلي',
                    'commercial': 'تجاري',
                    'industrial': 'صناعي',
                    'agricultural': 'زراعي'
                };
                return labels[category] || category || 'منزلي';
            }

            function getPhaseLabel(phase) {
                const labels = {
                    'single': '1 فاز',
                    'three': '3 فاز'
                };
                return labels[phase] || phase || '1 فاز';
            }

            function updateCounts() {
                const total = validRows.length + errorRows.length;
                $('#totalRowsCount').text(total);
                $('#validRowsCount').text(validRows.length);
                $('#errorRowsCount').text(errorRows.length);
                $('#validTabCount').text(validRows.length);
                $('#errorsTabCount').text(errorRows.length);
            }

            function showResult(success, title, message) {
                const iconHtml = success 
                    ? '<i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>'
                    : '<i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>';
                
                $('#importResultIcon').html(iconHtml);
                $('#importResultTitle').text(title).removeClass('text-success text-danger').addClass(success ? 'text-success' : 'text-danger');
                $('#importResultMessage').text(message);
                showStep(3);
            }

            // Preview button click
            $previewBtn.on('click', function() {
                const unitId = $generationUnit.val();
                const file = $fileInput[0].files[0];
                
                if (!unitId) {
                    if (window.adminNotifications) {
                        window.adminNotifications.warning('يرجى اختيار وحدة التوليد');
                    }
                    $generationUnit.focus();
                    return;
                }
                
                if (!file) {
                    if (window.adminNotifications) {
                        window.adminNotifications.warning('يرجى اختيار ملف Excel');
                    }
                    $fileInput.focus();
                    return;
                }
                
                // Validate file size
                if (file.size > 5 * 1024 * 1024) {
                    if (window.adminNotifications) {
                        window.adminNotifications.error('حجم الملف يتجاوز 5 ميجابايت');
                    }
                    return;
                }
                
                showLoading('جاري تحليل الملف...');
                
                const formData = new FormData();
                formData.append('file', file);
                formData.append('generation_unit_id', unitId);
                formData.append('_token', $('#csrfToken').val());
                
                $.ajax({
                    url: previewUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            validRows = response.results.valid || [];
                            errorRows = response.results.errors || [];
                            currentFilePath = response.file_path;
                            
                            updateCounts();
                            renderValidRows();
                            renderErrorRows();
                            showStep(2);
                            
                            if (window.adminNotifications) {
                                if (errorRows.length > 0) {
                                    window.adminNotifications.warning(`تم تحليل الملف: ${validRows.length} صالحة، ${errorRows.length} بها أخطاء`);
                                } else {
                                    window.adminNotifications.success(`تم تحليل الملف بنجاح: ${validRows.length} مشترك جاهز للاستيراد`);
                                }
                            }
                        } else {
                            showStep(1);
                            if (window.adminNotifications) {
                                window.adminNotifications.error(response.message || 'حدث خطأ أثناء تحليل الملف');
                            }
                        }
                    },
                    error: function(xhr) {
                        showStep(1);
                        const msg = xhr.responseJSON?.message || 'حدث خطأ أثناء تحليل الملف';
                        if (window.adminNotifications) {
                            window.adminNotifications.error(msg);
                        }
                    }
                });
            });

            // Back button click
            $backToStep1Btn.on('click', function() {
                showStep(1);
            });

            // Execute import button click
            $executeImportBtn.on('click', function() {
                if (validRows.length === 0) {
                    if (window.adminNotifications) {
                        window.adminNotifications.warning('لا توجد بيانات صالحة للاستيراد');
                    }
                    return;
                }
                
                if (!confirm(`هل أنت متأكد من استيراد ${validRows.length} مشترك؟`)) {
                    return;
                }
                
                showLoading('جاري استيراد المشتركين...');
                
                $.ajax({
                    url: executeUrl,
                    method: 'POST',
                    data: {
                        file_path: currentFilePath,
                        _token: $('#csrfToken').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            showResult(true, 'تم الاستيراد بنجاح!', response.message);
                            
                            if (window.adminNotifications) {
                                window.adminNotifications.success(response.message);
                            }
                            
                            // Reload subscribers list
                            if (window.loadSubscribers) {
                                window.loadSubscribers();
                            }
                        } else {
                            showResult(false, 'فشل الاستيراد', response.message);
                            if (window.adminNotifications) {
                                window.adminNotifications.error(response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'حدث خطأ أثناء الاستيراد';
                        showResult(false, 'حدث خطأ', msg);
                        if (window.adminNotifications) {
                            window.adminNotifications.error(msg);
                        }
                    }
                });
            });

            // Reset modal when closed
            $modal.on('hidden.bs.modal', function() {
                resetModal();
            });
        })();
    </script>
@endpush

