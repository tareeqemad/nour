@extends('layouts.admin')

@section('title', 'الفوترة والتحصيل')

@php $breadcrumbTitle = 'الفوترة والتحصيل'; $invoiceStatuses = \App\Helpers\ConstantsHelper::get(30); @endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="الفوترة والتحصيل" icon="bi-receipt">
                    <x-slot:actions>
                        {{-- Create invoice button hidden
                        @can('create', App\Models\Invoice::class)
                            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                إنشاء فاتورة جديدة
                            </a>
                        @endcan
                        --}}
                        @can('create', App\Models\Invoice::class)
                            <button type="button" class="btn btn-success" id="bulkIssueBtn" disabled>
                                <i class="bi bi-check-all me-1"></i>
                                إصدار الفواتير
                            </button>
                        @endcan
                        <a href="#" id="exportExcelBtn" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            تصدير Excel
                        </a>
                    </x-slot:actions>
                </x-admin.card-header>

                <div class="card-body pb-4">
                    @php
                        $user = auth()->user();
                        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
                    @endphp
                    <div class="row g-3">

                                {{-- المشغل --}}
                                @if($canSelectOperator && $operators->count())
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <select id="operatorFilter" class="form-select">
                                        <option value="">كل المشغلين</option>
                                        @foreach($operators as $op)
                                            <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected':'' }}>{{ $op->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @elseif(isset($currentOperator))
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">المشغل</label>
                                    <select id="operatorFilter" class="form-select" disabled>
                                        <option value="{{ $currentOperator->id }}" selected>{{ $currentOperator->name }}</option>
                                    </select>
                                    <input type="hidden" id="operatorFilterHidden" value="{{ $currentOperator->id }}">
                                </div>
                                @endif

                                {{-- المشترك --}}
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">المشترك</label>
                                    <select id="subscriberFilter" class="form-select" style="width:100%">
                                        <option value="">كل المشتركين</option>
                                        @foreach($subscribers as $sub)
                                            <option value="{{ $sub->id }}" {{ request('subscriber_id') == $sub->id ? 'selected':'' }}>
                                                {{ $sub->subscription_number }} - {{ $sub->subscriber_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- حالة الفاتورة --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">حالة الفاتورة</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">الكل</option>
                                        @foreach($invoiceStatuses as $item)
                                            <option value="{{ $item->value }}" {{ request('invoice_status') == $item->value ? 'selected':'' }}>{{ $item->label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- من تاريخ --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">من تاريخ</label>
                                    <input type="date" id="dateFromFilter" class="form-control" value="{{ request('date_from') }}">
                                </div>

                                {{-- إلى تاريخ --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">إلى تاريخ</label>
                                    <input type="date" id="dateToFilter" class="form-control" value="{{ request('date_to') }}">
                                </div>

                                {{-- بحث --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">البحث</label>
                                    <input type="text" id="searchInput" class="form-control" placeholder="رقم الفاتورة، رقم الاشتراك، اسم المشترك..." value="{{ request('search') }}">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-center gap-2">
                                    <button class="btn btn-primary" id="searchBtn"><i class="bi bi-search me-1"></i>بحث</button>
                                    <button class="btn btn-outline-secondary d-none" id="clearBtn"><i class="bi bi-arrow-counterclockwise me-1"></i>تفريغ</button>
                                </div>
                            </div>


                    <div id="invoicesListWrap" class="position-relative">
                        <div id="invLoading" class="data-table-loading d-none">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                            </div>
                        </div>
                        @include('admin.invoices.partials.list', ['invoices' => $invoices])
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
{{-- Modal: إصدار الفواتير --}}
<div class="modal fade" id="bulkIssueModal" tabindex="-1" aria-hidden="true" style="display:none">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-all me-2"></i>إصدار الفواتير المحددة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="alert alert-info py-2 small" id="bulkSelectionSummary">
                        سيتم إصدار <strong id="bulkSelectedCount">0</strong> فاتورة محددة.
                    </div>
                    <label class="form-label fw-semibold">قيمة التعرفة (سعر الكيلوواط) <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" id="bulkPricePerKwh" class="form-control" placeholder="مثال: 1" value="{{ old('price_per_kwh', $activeTariff ? (int) round((float) $activeTariff->price_per_kwh) : '') }}">
                    <small class="form-text text-muted">
                        القيمة الافتراضية مأخوذة من التعرفة النشطة في شاشة التعرفة، ويمكن تعديلها قبل الإصدار
                    </small>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirmMinCharge">
                    <label class="form-check-label fw-semibold" for="confirmMinCharge">
                        أقر بأن الحد الأدنى للاشتراكات تم إدخاله وتحديثه بشكل صحيح
                    </label>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    سيتم إصدار الفواتير التي حددتها فقط. هذا الإجراء لا يمكن التراجع عنه.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" id="confirmBulkIssue" disabled>
                    <i class="bi bi-check-all me-1"></i>
                    تأكيد الإصدار
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: سبب إلغاء الفاتورة --}}
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1" aria-hidden="true" style="display:none">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>إلغاء فاتورة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">سيتم إلغاء الفاتورة رقم: <strong id="cancelInvoiceNumber"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">سبب الإلغاء <span class="text-danger">*</span></label>
                    <textarea id="cancelReason" class="form-control" rows="3" required minlength="5" placeholder="اذكر سبب إلغاء الفاتورة..."></textarea>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    سيتم إنشاء قيد مالي عكسي في حساب المشترك وتغيير حالة القراءة إلى غير معتمدة.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">تراجع</button>
                <button type="button" class="btn btn-danger" id="confirmCancelInvoice">
                    <i class="bi bi-x-circle me-1"></i>
                    تأكيد الإلغاء
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script>
(function () {
    const listUrl     = @json(route('admin.invoices.index'));
    const subsByOpUrl = @json(route('admin.invoices.subscribers-by-operator'));

    const $wrap      = $('#invoicesListWrap');
    const $opFilter  = $('#operatorFilter');
    const $subFilter = $('#subscriberFilter');
    const $stFilter  = $('#statusFilter');
    const $dfFilter  = $('#dateFromFilter');
    const $dtFilter  = $('#dateToFilter');
    const $search    = $('#searchInput');
    const $searchBtn = $('#searchBtn');
    const $clearBtn  = $('#clearBtn');
    const defaultBulkPricePerKwh = @json($activeTariff ? (string) ((int) round((float) $activeTariff->price_per_kwh)) : '');

    let nextPage = {{ $invoices->hasMorePages() ? 2 : 'null' }};
    let hasMore  = {{ $invoices->hasMorePages() ? 'true' : 'false' }};
    let isLoading = false;
    const selectedInvoiceIds = new Set();

    $subFilter.select2({
        placeholder: 'اختر أو ابحث...', allowClear: true, dir: 'rtl',
        language: { noResults: () => 'لا توجد نتائج', searching: () => 'جاري البحث...' }
    });

    $opFilter.on('change', function () {
        clearSelection();
        loadSubscribersByOperator($(this).val());
        loadInvoices();
    });

    function loadSubscribersByOperator(opId) {
        $subFilter.val(null).trigger('change.select2');
        $subFilter.find('option:not(:first)').remove();
        if (!opId) return;
        $.get(subsByOpUrl, { operator_id: opId }, function (res) {
            if (res.success) {
                res.subscribers.forEach(s => $subFilter.append(new Option(s.subscription_number + ' - ' + s.subscriber_name, s.id)));
                $subFilter.trigger('change.select2');
            }
        });
    }

    function params(extra) {
        const opId = $opFilter.prop('disabled') ? ($('#operatorFilterHidden').val() || '') : ($opFilter.val() || '');
        const st   = $stFilter.val();
        return Object.assign({
            operator_id:    opId,
            subscriber_id:  $subFilter.val() || '',
            invoice_status: (st !== null && st !== '') ? st : '',
            date_from:      $dfFilter.val() || '',
            date_to:        $dtFilter.val() || '',
            search:         $search.val() || '',
        }, extra || {});
    }

    function hasFilter(p) {
        return !!(p.operator_id || p.subscriber_id || p.invoice_status !== '' || p.date_from || p.date_to || p.search);
    }

    function initTooltips(ctx) {
        (ctx || document).querySelectorAll('[data-bs-toggle="tooltip"]:not(.tooltip-init)').forEach(function (el) {
            el.classList.add('tooltip-init');
            new bootstrap.Tooltip(el, { trigger: 'hover' });
        });
    }

    function getSelectableInvoices() {
        return $('.invoice-select-item');
    }

    function syncSelectionUI() {
        const $items = getSelectableInvoices();

        $items.each(function () {
            const id = String($(this).data('invoice-id'));
            $(this).prop('checked', selectedInvoiceIds.has(id));
        });

        const selectedCount = selectedInvoiceIds.size;
        const checkedVisibleCount = $items.filter(':checked').length;
        const allVisibleChecked = $items.length > 0 && checkedVisibleCount === $items.length;

        $('#selectAllDrafts')
            .prop('checked', allVisibleChecked)
            .prop('indeterminate', checkedVisibleCount > 0 && !allVisibleChecked);

        $('#bulkSelectedCount').text(selectedCount);
        $('#selectedInvoicesSummary').text(
            selectedCount > 0
                ? `تم تحديد ${selectedCount} فاتورة للإصدار`
                : 'لم يتم تحديد أي فاتورة للإصدار'
        );
        $('#clearSelectionBtn').toggleClass('d-none', selectedCount === 0);
        $('#bulkIssueBtn').prop('disabled', selectedCount === 0);
    }

    function clearSelection() {
        selectedInvoiceIds.clear();
        syncSelectionUI();
    }

    // تحميل الصفحة الأولى (يستبدل كل شيء)
    function loadInvoices() {
        if (isLoading) return;
        selectedInvoiceIds.clear();
        syncSelectionUI();
        isLoading = true;
        nextPage  = null;
        hasMore   = true;

        $('#invLoading').removeClass('d-none');
        $wrap.find('.table,.empty-state').css('visibility','hidden');

        $.ajax({
            url: listUrl, method: 'GET', data: params({ page: 1 }),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success(res) {
                if (res.success) {
                    $wrap.html(res.html);
                    $('#invoicesCount').text(res.count);
                    hasMore  = res.has_more;
                    nextPage = res.next_page;
                    if (!hasMore) $('#scrollLoader').remove();
                    $clearBtn.toggleClass('d-none', !hasFilter(params()));
                    syncSelectionUI();
                    initTooltips($wrap[0]);
                }
            },
            complete() {
                $('#invLoading').addClass('d-none');
                $wrap.find('.table,.empty-state').css('visibility','visible');
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
            url: listUrl, method: 'GET', data: params({ page: nextPage }),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success(res) {
                if (res.success && res.append) {
                    $('#invoicesBody').append(res.html);
                    hasMore  = res.has_more;
                    nextPage = res.next_page;
                    if (!hasMore) $loader.remove();
                    syncSelectionUI();
                    initTooltips($('#invoicesBody')[0]);
                }
            },
            complete() { isLoading = false; }
        });
    }

    // Infinite scroll
    function checkScroll() {
        if (isLoading || !hasMore || !nextPage) return;
        var el = document.getElementById('scrollLoader');
        if (!el) return;
        var rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight + 300) loadMore();
    }

    $(window).on('scroll resize', checkScroll);
    $('.app-content, .main-content, .main-container').on('scroll', checkScroll);
    setInterval(checkScroll, 500);

    $(document).on('change', '.invoice-select-item', function () {
        const id = String($(this).data('invoice-id'));
        if ($(this).is(':checked')) selectedInvoiceIds.add(id);
        else selectedInvoiceIds.delete(id);
        syncSelectionUI();
    });

    $(document).on('change', '#selectAllDrafts', function () {
        const checked = $(this).is(':checked');
        getSelectableInvoices().each(function () {
            const id = String($(this).data('invoice-id'));
            if (checked) selectedInvoiceIds.add(id);
            else selectedInvoiceIds.delete(id);
        });
        syncSelectionUI();
    });

    $(document).on('click', '#clearSelectionBtn', function () {
        clearSelection();
    });

    // أحداث الفلاتر
    $searchBtn.on('click', () => { clearSelection(); loadInvoices(); });
    $search.on('keypress', e => { if (e.which === 13) { clearSelection(); loadInvoices(); } });
    $subFilter.on('change', () => { clearSelection(); loadInvoices(); });
    $stFilter.on('change', () => { clearSelection(); loadInvoices(); });
    $dfFilter.on('change', () => { clearSelection(); loadInvoices(); });
    $dtFilter.on('change', () => { clearSelection(); loadInvoices(); });

    $clearBtn.on('click', function () {
        clearSelection();
        if (!$opFilter.prop('disabled')) $opFilter.val('');
        $subFilter.val(null).trigger('change.select2');
        $subFilter.find('option:not(:first)').remove();
        $stFilter.val(''); $dfFilter.val(''); $dtFilter.val(''); $search.val('');
        loadInvoices();
    });

    // تفعيل tooltips عند أول تحميل
    initTooltips();
    syncSelectionUI();

    // === Bulk Issue ===
    $('#bulkIssueBtn').on('click', function() {
        if (selectedInvoiceIds.size === 0) {
            if (window.adminNotifications) window.adminNotifications.error('يرجى تحديد فاتورة واحدة على الأقل.');
            else alert('يرجى تحديد فاتورة واحدة على الأقل.');
            return;
        }

        $('#bulkPricePerKwh').val(defaultBulkPricePerKwh);
        $('#confirmMinCharge').prop('checked', false);
        $('#confirmBulkIssue').prop('disabled', true);
        $('#bulkSelectedCount').text(selectedInvoiceIds.size);
        new bootstrap.Modal('#bulkIssueModal').show();
    });

    $('#confirmMinCharge').on('change', function() {
        var priceOk = parseFloat($('#bulkPricePerKwh').val()) > 0;
        $('#confirmBulkIssue').prop('disabled', !($(this).is(':checked') && priceOk));
    });
    $('#bulkPricePerKwh').on('input', function() {
        var priceOk = parseFloat($(this).val()) > 0;
        var checked = $('#confirmMinCharge').is(':checked');
        $('#confirmBulkIssue').prop('disabled', !(checked && priceOk));
    });

    $('#confirmBulkIssue').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>جاري الإصدار...');

        $.ajax({
            url: "{{ route('admin.invoices.bulk-issue') }}",
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val(),
                price_per_kwh: $('#bulkPricePerKwh').val(),
                confirm_minimum_charge: 1,
                invoice_ids: Array.from(selectedInvoiceIds),
            },
            success: function(res) {
                bootstrap.Modal.getInstance('#bulkIssueModal').hide();
                if (res.success) {
                    clearSelection();
                    if (window.adminNotifications) window.adminNotifications.success(res.message);
                    else alert(res.message);
                    if (typeof loadList === 'function') loadList();
                    else loadInvoices();
                } else {
                    if (window.adminNotifications) window.adminNotifications.error(res.message);
                    else alert(res.message);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'حدث خطأ أثناء إصدار الفواتير';
                if (window.adminNotifications) window.adminNotifications.error(msg);
                else alert(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-check-all me-1"></i>تأكيد الإصدار');
            }
        });
    });

    // === Export Excel ===
    $('#exportExcelBtn').on('click', function(e) {
        e.preventDefault();
        var p = params();
        window.location.href = "{{ route('admin.invoices.export') }}" + '?' + $.param(p);
    });

    // === Cancel Invoice (Issued) ===
    var cancelUrl = '';
    $(document).on('click', '.cancel-invoice-btn', function() {
        cancelUrl = $(this).data('url');
        $('#cancelInvoiceNumber').text($(this).data('number'));
        $('#cancelReason').val('');
        new bootstrap.Modal('#cancelInvoiceModal').show();
    });

    $('#confirmCancelInvoice').on('click', function() {
        var reason = $('#cancelReason').val().trim();
        if (reason.length < 5) {
            alert('يجب ذكر سبب الإلغاء (5 أحرف على الأقل)');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>جاري الإلغاء...');

        $.ajax({
            url: cancelUrl,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || $('#csrfToken').val(),
                cancel_reason: reason,
            },
            success: function(res) {
                bootstrap.Modal.getInstance('#cancelInvoiceModal').hide();
                if (res.success) {
                    if (window.adminNotifications) window.adminNotifications.success(res.message);
                    else alert(res.message);
                    if (typeof loadList === 'function') loadList();
                    else loadInvoices();
                } else {
                    if (window.adminNotifications) window.adminNotifications.error(res.message);
                    else alert(res.message);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'حدث خطأ أثناء إلغاء الفاتورة';
                if (window.adminNotifications) window.adminNotifications.error(msg);
                else alert(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>تأكيد الإلغاء');
            }
        });
    });
})();
</script>
@endpush
