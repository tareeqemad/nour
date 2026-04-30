@extends('layouts.admin')

@section('title', 'إدارة بيانات المشتركين')

@php
    $breadcrumbTitle = 'إدارة بيانات المشتركين';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <style>
        #importModal .display-6 { font-size: 2rem; }
        #importModal .nav-tabs .nav-link { border-radius: 0; padding: 0.6rem 1.25rem; font-weight: 600; font-size: 0.85rem; }
        #importModal .nav-tabs .nav-link.active { border-bottom: 2.5px solid var(--color-primary, #24308F); color: var(--color-primary, #24308F); }
        #importModal .table thead.sticky-top { z-index: 1; }
        #previewTabsContent { min-height: 200px; }
    </style>
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page" id="subscribersPage">
        <div class="row g-3">
            <div class="col-12">
                <x-admin.card>
                    <x-admin.card-header title="إدارة بيانات المشتركين" icon="bi-people">
                        <x-slot:actions>
                            @can('create', App\Models\Subscriber::class)
                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    استيراد Excel
                                </button>
                                <a href="{{ route('admin.subscribers.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة مشترك
                                </a>
                            @endcan
                        </x-slot:actions>
                    </x-admin.card-header>

                    <div class="card-body pb-4">
                        @php
                            $user = auth()->user();
                            $isCompanyOwner = $user->isCompanyOwner();
                            $isEmployeeOrTechnician = $user->isEmployee() || $user->isTechnician();
                            $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
                            $subscriptionStatuses   = \App\Helpers\ConstantsHelper::get(25);
                            $subscriptionCategories = \App\Helpers\ConstantsHelper::get(23);
                            $phaseTypes             = \App\Helpers\ConstantsHelper::get(24);
                            $serviceTypes           = \App\Helpers\ConstantsHelper::get(26);
                            $subscriberTypes        = \App\Helpers\ConstantsHelper::get(27);
                            $ampereOptions          = \App\Helpers\ConstantsHelper::get(31);
                        @endphp

                        {{-- الفلاتر - صف 1 --}}
                        <div class="row g-3">
                            {{-- المشغل --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1"></i>المشغل
                                </label>
                                @if($canSelectOperator && isset($operators) && $operators->count() > 0)
                                    <select id="operatorFilter" class="form-select">
                                        <option value="">كل المشغلين</option>
                                        @foreach($operators as $op)
                                            <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                                        @endforeach
                                    </select>
                                @elseif(isset($currentOperator) && $currentOperator)
                                    <select id="operatorFilter" class="form-select" disabled style="background-color:#f8f9fa;cursor:not-allowed;">
                                        <option value="{{ $currentOperator->id }}" selected>{{ $currentOperator->name }}</option>
                                    </select>
                                    <input type="hidden" id="operatorFilterHidden" name="operator_id" value="{{ $currentOperator->id }}">
                                @endif
                            </div>

                            {{-- وحدة التوليد --}}
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-lightning-charge me-1"></i>وحدة التوليد
                                </label>
                                @if($canSelectOperator)
                                    {{-- للسوبر أدمن: تبدأ فارغة ومعطلة حتى يختار المشغل --}}
                                    <select id="generationUnitFilter" class="form-select" disabled>
                                        <option value="">اختر المشغل أولاً</option>
                                    </select>
                                @else
                                    {{-- للمشغل/الموظف: الوحدات مبنية مسبقاً --}}
                                    <select id="generationUnitFilter" class="form-select">
                                        <option value="">الكل</option>
                                        @foreach($generationUnits ?? [] as $unit)
                                            <option value="{{ $unit->id }}" data-operator-id="{{ $unit->operator_id }}">{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-funnel me-1"></i>حالة الاشتراك
                                </label>
                                <select id="subscriptionStatusFilter" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($subscriptionStatuses as $item)
                                        <option value="{{ $item->value }}">{{ $item->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tags me-1"></i>تصنيف الاشتراك
                                </label>
                                <select id="subscriptionCategoryFilter" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($subscriptionCategories as $item)
                                        <option value="{{ $item->value }}">{{ $item->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- الفلاتر - صف 2 --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-diagram-3 me-1"></i>نوع الفاز
                                </label>
                                <select id="phaseTypeFilter" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($phaseTypes as $item)
                                        <option value="{{ $item->value }}">{{ $item->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-gear me-1"></i>نوع الخدمة
                                </label>
                                <select id="serviceTypeFilter" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($serviceTypes as $item)
                                        <option value="{{ $item->value }}">{{ $item->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-speedometer2 me-1"></i>الأمبير
                                </label>
                                <select id="ampereFilter" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($ampereOptions as $item)
                                        <option value="{{ $item->value }}">{{ $item->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-badge me-1"></i>نوع المشترك
                                </label>
                                <select id="isEmployeeFilter" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach($subscriberTypes as $item)
                                        <option value="{{ $item->value }}">{{ $item->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- الفلاتر - صف 3 --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-upc-scan me-1"></i>رقم الاشتراك
                                </label>
                                <input type="text" id="subscriptionNumberFilter" class="form-control" placeholder="012010001...">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-mailbox me-1"></i>رقم الصندوق
                                </label>
                                <input type="text" id="boxNumberFilter" class="form-control" placeholder="0000" maxlength="4">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-search me-1"></i>بحث عام
                                </label>
                                <input type="text" id="searchInput" class="form-control" placeholder="الاسم، الهوية، الجوال، العداد، العنوان..." value="{{ request('search') }}">
                            </div>
                        </div>

                        {{-- أزرار --}}
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-center gap-2">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="bi bi-search me-1"></i>استعلام
                                </button>
                                <a href="#" id="exportExcelBtn" class="btn btn-outline-success">
                                    <i class="bi bi-file-earmark-excel me-1"></i>تصدير Excel
                                </a>
                                <button class="btn btn-outline-secondary d-none" type="button" id="clearBtn">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>تفريغ
                                </button>
                            </div>
                        </div>

                        {{-- الجدول --}}
                        <div id="subscribersListWrap" class="position-relative mt-3">
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
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection

{{-- Import Modal --}}
@can('create', App\Models\Subscriber::class)
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" style="display:none">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    استيراد المشتركين من Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Step 1: File Upload --}}
                <div id="importStep1">
                    {{-- رفع الملف --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>المشغل <span class="text-danger">*</span>
                            </label>
                            @if($canSelectOperator)
                                <select id="importOperator" class="form-select" required>
                                    <option value="">-- اختر المشغل --</option>
                                    @foreach($operators as $op)
                                        <option value="{{ $op->id }}">{{ $op->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select id="importOperator" class="form-select" disabled style="background-color:#f8f9fa;cursor:not-allowed;">
                                    <option value="{{ $currentOperator->id ?? '' }}" selected>{{ $currentOperator->name ?? '' }}</option>
                                </select>
                                <input type="hidden" id="importOperatorHidden" value="{{ $currentOperator->id ?? '' }}">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-lightning-charge me-1"></i>وحدة التوليد <span class="text-danger">*</span>
                            </label>
                            @if($canSelectOperator)
                                <select id="importGenerationUnit" class="form-select" required disabled>
                                    <option value="">-- اختر المشغل أولاً --</option>
                                </select>
                            @else
                                <select id="importGenerationUnit" class="form-select" required>
                                    <option value="">-- اختر وحدة التوليد --</option>
                                    @foreach($generationUnits ?? [] as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->unit_code }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>ملف Excel <span class="text-danger">*</span>
                            </label>
                            <input type="file" id="importFile" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">xlsx, xls, csv (الحد الأقصى: 5 ميجابايت)</div>
                        </div>
                    </div>

                    {{-- زر تحميل النموذج --}}
                    <div class="mb-3">
                        <a href="{{ route('admin.subscribers.import.template') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-download me-1"></i>تحميل نموذج Excel
                        </a>
                    </div>

                    {{-- التعليمات --}}
                    <div style="background:#f8f9fa;border:1px solid var(--color-border,#E5E7EB);border-radius:8px;padding:1rem;">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2" style="font-size:0.85rem;">
                                    <i class="bi bi-check-circle text-success me-1"></i>الأعمدة المطلوبة:
                                </h6>
                                <ul class="list-unstyled small mb-0">
                                    <li>- رقم_الهوية (فريد)</li>
                                    <li>- اسم_المشترك</li>
                                    <li>- رقم_الموبايل (059/056)</li>
                                    <li>- العنوان</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2" style="font-size:0.85rem;">
                                    <i class="bi bi-dash-circle text-secondary me-1"></i>الأعمدة الاختيارية:
                                </h6>
                                <ul class="list-unstyled small mb-0">
                                    <li>- رقم_جوال_بديل، رقم_الصندوق، رقم_العداد</li>
                                    <li>- الأمبير، القراءة_الافتتاحية</li>
                                    <li>- تصنيف_الاشتراك، نوع_الفاز، نوع_الخدمة</li>
                                    <li>- اشتراك_موظف، تاريخ_الاشتراك، تاريخ_الطلب، ملاحظات</li>
                                </ul>
                                <div class="form-text mt-1" style="font-size:0.78rem;">
                                    <i class="bi bi-info-circle me-1"></i>النموذج يحتوي على قوائم منسدلة للقيم المتاحة
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
                            <div class="dash-kpi justify-content-center text-center" style="flex-direction: column; gap: 0.25rem;">
                                <div class="display-6 fw-bold" id="totalRowsCount" style="color: var(--color-primary, #24308F); font-size: 2rem;">0</div>
                                <div class="dash-kpi-label">إجمالي الصفوف</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dash-kpi justify-content-center text-center" style="flex-direction: column; gap: 0.25rem; border-color: var(--color-success-border, #A7F3D0);">
                                <div class="display-6 fw-bold" id="validRowsCount" style="color: var(--color-success, #10B981); font-size: 2rem;">0</div>
                                <div class="dash-kpi-label">صالحة للاستيراد</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dash-kpi justify-content-center text-center" style="flex-direction: column; gap: 0.25rem; border-color: var(--color-danger-border, #FECACA);">
                                <div class="display-6 fw-bold" id="errorRowsCount" style="color: var(--color-danger, #EF4444); font-size: 2rem;">0</div>
                                <div class="dash-kpi-label">بها أخطاء</div>
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
                                    <thead class="sticky-top" style="background: var(--color-success-bg, #ECFDF5);">
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">رقم الهوية</th>
                                            <th class="text-center">الاسم</th>
                                            <th class="text-center">الموبايل</th>
                                            <th class="text-center">العنوان</th>
                                            <th class="text-center">الصندوق</th>
                                            <th class="text-center">العداد</th>
                                            <th class="text-center">الأمبير</th>
                                            <th class="text-center">القراءة الافتتاحية</th>
                                            <th class="text-center">التصنيف</th>
                                            <th class="text-center">الفاز</th>
                                            <th class="text-center">الخدمة</th>
                                            <th class="text-center">موظف</th>
                                            <th class="text-center">تاريخ الاشتراك</th>
                                        </tr>
                                    </thead>
                                    <tbody id="validRowsBody">
                                        <tr><td colspan="14" class="text-center text-muted py-4">لا توجد بيانات</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade p-3" id="errors-pane" role="tabpanel">
                            <div class="table-responsive" style="max-height: 350px;">
                                <table class="table table-sm table-hover mb-0 text-center">
                                    <thead class="sticky-top" style="background: var(--color-danger-bg, #FEF2F2);">
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
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="closeModalBtn">
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
            const $operatorFilter = $('#operatorFilter');
            const $searchInput = $('#searchInput');
            const $searchBtn = $('#searchBtn');
            const $clearBtn = $('#clearBtn');

            const filterIds = [
                'operatorFilter', 'generationUnitFilter', 'subscriptionStatusFilter',
                'subscriptionCategoryFilter', 'phaseTypeFilter', 'serviceTypeFilter',
                'ampereFilter', 'isEmployeeFilter', 'subscriptionNumberFilter', 'boxNumberFilter'
            ];

            let nextPage = {{ $subscribers->hasMorePages() ? 2 : 'null' }};
            let isLoading = false;
            let hasMore = {{ $subscribers->hasMorePages() ? 'true' : 'false' }};

            function currentParams(extra = {}) {
                let operatorId = $operatorFilter.val() || '';
                if ($operatorFilter.prop('disabled')) {
                    operatorId = $('#operatorFilterHidden').val() || $('input[name="operator_id"]').val() || '';
                }
                return Object.assign({
                    operator_id: operatorId,
                    generation_unit_id: $('#generationUnitFilter').val() || '',
                    subscription_status: $('#subscriptionStatusFilter').val() || '',
                    subscription_category: $('#subscriptionCategoryFilter').val() || '',
                    phase_type: $('#phaseTypeFilter').val() || '',
                    service_type: $('#serviceTypeFilter').val() || '',
                    ampere: $('#ampereFilter').val() || '',
                    is_employee: $('#isEmployeeFilter').val() || '',
                    subscription_number: $('#subscriptionNumberFilter').val() || '',
                    box_number: $('#boxNumberFilter').val() || '',
                    search: $searchInput.val() || '',
                }, extra);
            }

            // تحميل الصفحة الأولى (يستبدل كل شي)
            function loadSubscribers() {
                if (isLoading) return;
                isLoading = true;
                nextPage = null;
                hasMore = true;

                $('#subLoading').removeClass('d-none');
                $wrap.find('.table, .d-flex, .text-center').hide();

                $.ajax({
                    url: listUrl,
                    method: 'GET',
                    dataType: 'json',
                    cache: false,
                    data: currentParams({ page: 1 }),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        if (res.success) {
                            $wrap.html(res.html);
                            $('#subscribersCount').text(res.count);
                            hasMore = res.has_more;
                            nextPage = res.next_page;

                            if (!hasMore) $('#scrollLoader').remove();

                            var p = currentParams();
                            var hasAny = Object.values(p).some(v => v !== '' && v !== null && v !== undefined);
                            $clearBtn.toggleClass('d-none', !hasAny);
                        }
                    },
                    error: function(xhr) {
                        console.error('Search error:', xhr.status, xhr.responseText);
                        alert('حدث خطأ أثناء تحميل البيانات');
                    },
                    complete: function() {
                        $('#subLoading').addClass('d-none');
                        $wrap.find('.table, .d-flex, .text-center').show();
                        isLoading = false;
                    }
                });
            }

            // تحميل الصفحة التالية (يلحق بالجدول)
            function loadMore() {
                if (isLoading || !hasMore || !nextPage) return;
                isLoading = true;

                var $loader = $('#scrollLoader');
                $loader.show();

                $.ajax({
                    url: listUrl,
                    method: 'GET',
                    dataType: 'json',
                    data: currentParams({ page: nextPage }),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        if (res.success && res.append) {
                            $('#subscribersBody').append(res.html);
                            hasMore = res.has_more;
                            nextPage = res.next_page;

                            if (!hasMore) $loader.remove();
                        }
                    },
                    complete: function() {
                        isLoading = false;
                    }
                });
            }

            // Infinite scroll
            function checkScroll() {
                if (isLoading || !hasMore || !nextPage) return;
                var el = document.getElementById('scrollLoader');
                if (!el) return;
                var rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight + 300) {
                    loadMore();
                }
            }

            // مراقبة السكرول على window + أي parent scrollable
            $(window).on('scroll resize', checkScroll);
            $('.app-content, .main-content, .main-container').on('scroll', checkScroll);
            // فحص دوري كل 500ms كـ fallback
            setInterval(checkScroll, 500);

            // الأحداث
            $searchBtn.on('click', loadSubscribers);

            @if($canSelectOperator)
            // === للسوبر أدمن/الأدمن/سلطة الطاقة: AJAX cascading (بدون بحث تلقائي) ===
            var unitApiUrl = @json(url('/admin/operators'));

            // عند تغيير المشغل → جلب وحدات التوليد من الـ API (بدون بحث)
            $operatorFilter.on('change', function() {
                var operatorId = $(this).val();
                var $unitSelect = $('#generationUnitFilter');

                // إعادة تهيئة وحدات التوليد
                $unitSelect.empty();

                if (!operatorId) {
                    // لم يُختر مشغل: عطّل قائمة وحدات التوليد
                    $unitSelect.append('<option value="">اختر المشغل أولاً</option>');
                    $unitSelect.prop('disabled', true);
                    return;
                }

                // جلب الوحدات من الـ API
                $unitSelect.append('<option value="">جاري التحميل...</option>');
                $unitSelect.prop('disabled', true);

                $.ajax({
                    url: unitApiUrl + '/' + operatorId + '/generation-units-for-subscribers',
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    success: function(data) {
                        $unitSelect.empty().append('<option value="">الكل</option>');
                        if (data.generation_units && data.generation_units.length > 0) {
                            data.generation_units.forEach(function(unit) {
                                $unitSelect.append(
                                    $('<option>', { value: unit.id, text: unit.label, 'data-operator-id': operatorId })
                                );
                            });
                        }
                        $unitSelect.prop('disabled', false);
                    },
                    error: function() {
                        $unitSelect.empty().append('<option value="">خطأ في التحميل</option>');
                    }
                });
            });

            // === Import Modal: cascade المشغل → وحدة التوليد ===
            $('#importOperator').on('change', function() {
                var operatorId = $(this).val();
                var $unitSelect = $('#importGenerationUnit');

                $unitSelect.empty();

                if (!operatorId) {
                    $unitSelect.append('<option value="">-- اختر المشغل أولاً --</option>');
                    $unitSelect.prop('disabled', true);
                    return;
                }

                $unitSelect.append('<option value="">جاري التحميل...</option>');
                $unitSelect.prop('disabled', true);

                $.ajax({
                    url: unitApiUrl + '/' + operatorId + '/generation-units-for-subscribers',
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    success: function(data) {
                        $unitSelect.empty().append('<option value="">-- اختر وحدة التوليد --</option>');
                        if (data.generation_units && data.generation_units.length > 0) {
                            data.generation_units.forEach(function(unit) {
                                $unitSelect.append(
                                    $('<option>', { value: unit.id, text: unit.label })
                                );
                            });
                        }
                        $unitSelect.prop('disabled', false);
                    },
                    error: function() {
                        $unitSelect.empty().append('<option value="">خطأ في التحميل</option>');
                    }
                });
            });
            @else
            // === للمشغل/الموظف: تصفية محلية (client-side) ===
            function filterUnitsByOperator(operatorId) {
                var $unitSelect = $('#generationUnitFilter');
                var currentUnit = $unitSelect.val();
                $unitSelect.find('option:not(:first)').each(function() {
                    var unitOpId = $(this).data('operator-id');
                    if (!operatorId || String(unitOpId) === String(operatorId)) {
                        $(this).show().prop('disabled', false);
                    } else {
                        $(this).hide().prop('disabled', true);
                        if ($(this).val() === currentUnit) {
                            $unitSelect.val('');
                        }
                    }
                });
            }

            // المشغل مقفل → صفّ الوحدات بناءً على المشغل المحدد
            var lockedOpId = $('#operatorFilterHidden').val() || $operatorFilter.val();
            if (lockedOpId) filterUnitsByOperator(lockedOpId);
            @endif

            // inputs تبحث عند Enter
            $searchInput.add('#subscriptionNumberFilter, #boxNumberFilter').on('keypress', function(e) {
                if (e.which === 13) loadSubscribers();
            });

            $clearBtn.on('click', function() {
                filterIds.forEach(id => $('#' + id).val(''));
                $searchInput.val('');
                @if($canSelectOperator)
                // أعِد تعطيل وحدات التوليد (لازم يختار مشغل أولاً)
                var $unitSelect = $('#generationUnitFilter');
                $unitSelect.empty().append('<option value="">اختر المشغل أولاً</option>');
                $unitSelect.prop('disabled', true);
                @else
                // أعد إظهار كل وحدات التوليد
                $('#generationUnitFilter option').show().prop('disabled', false);
                @endif
                $clearBtn.addClass('d-none');
                loadSubscribers();
            });

            // حذف AJAX
            $(document).on('submit', '.delete-subscriber-form', function(e) {
                e.preventDefault();
                if (!confirm('هل أنت متأكد من حذف هذا المشترك؟')) return;
                var form = $(this), url = form.attr('action');
                $.ajax({
                    url: url, method: 'POST', data: form.serialize(),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    success: function(res) {
                        if (res.success) {
                            if (window.adminNotifications) window.adminNotifications.success(res.message || 'تم حذف المشترك بنجاح');
                            loadSubscribers();
                        } else {
                            if (window.adminNotifications) window.adminNotifications.error(res.message || 'حدث خطأ');
                        }
                    },
                    error: function() {
                        if (window.adminNotifications) window.adminNotifications.error('حدث خطأ أثناء حذف المشترك');
                    }
                });
            });

            // تصدير Excel (بنفس الفلاتر)
            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                window.location.href = @json(route('admin.subscribers.export')) + '?' + $.param(currentParams());
            });

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
                    $tbody.html('<tr><td colspan="14" class="text-center text-muted py-4">لا توجد بيانات صالحة</td></tr>');
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
                        <td>${row.box_number || '-'}</td>
                        <td>${row.meter_number || '-'}</td>
                        <td>${row.ampere_label || '-'}</td>
                        <td>${row.opening_reading != null ? row.opening_reading : '-'}</td>
                        <td><span class="badge bg-secondary">${row.category_label || '-'}</span></td>
                        <td><span class="badge bg-info">${row.phase_label || '-'}</span></td>
                        <td>${row.service_label || '-'}</td>
                        <td>${row.employee_label || 'لا'}</td>
                        <td>${row.subscription_date || '-'}</td>
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

