@extends('layouts.admin')

@section('title', 'قراءات العدادات')

@php
    $breadcrumbTitle = 'قراءات العدادات';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

@section('content')
    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <div class="general-page" id="meterReadingsPage">
        <div class="row g-3">
            <div class="col-12">
                <div class="general-card">
                    <div class="general-card-header">
                        <div>
                            <h5 class="general-title">
                                <i class="bi bi-speedometer2 me-2"></i>
                                قراءات العدادات
                            </h5>
                            <div class="general-subtitle">
                                البحث والفلترة وإدارة قراءات العدادات. العدد: <span id="meterReadingsCount">{{ $meterReadings->total() }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            @can('create', App\Models\MeterReading::class)
                                <button type="button" id="bulkApproveBtn" class="btn btn-success d-none">
                                    <i class="bi bi-check-circle me-1"></i>
                                    اعتماد المحدد
                                    <span id="bulkApproveCount" class="badge bg-white text-success ms-1">0</span>
                                </button>
                            @endcan
                            @can('create', App\Models\MeterReading::class)
                                <a href="{{ route('admin.meter-readings.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    إضافة قراءة جديدة
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body pb-4">
                        <div class="filter-card">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <i class="bi bi-funnel me-2"></i>
                                    فلاتر البحث
                                </h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $user = auth()->user();
                                    $isCompanyOwner = $user->isCompanyOwner();
                                    $isEmployeeOrTechnician = $user->isEmployee() || $user->isTechnician();
                                    $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
                                    $readingStatuses = \App\Helpers\ConstantsHelper::get(28);
                                    $readingActions  = \App\Helpers\ConstantsHelper::get(29);
                                @endphp
                                <div class="row g-3">

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
                                            <select id="operatorFilter" class="form-select" disabled>
                                                <option value="{{ $currentOperator->id }}" selected>{{ $currentOperator->name }}</option>
                                            </select>
                                            <input type="hidden" id="operatorFilterHidden" value="{{ $currentOperator->id }}">
                                        @else
                                            <select id="operatorFilter" class="form-select">
                                                <option value="">كل المشغلين</option>
                                            </select>
                                        @endif
                                    </div>

                                    {{-- فلتر المشترك (Select2 مع بحث) --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-person me-1"></i>
                                            المشترك
                                        </label>
                                        <select id="subscriberFilter" class="form-select" style="width:100%">
                                            <option value="">كل المشتركين</option>
                                            @if(isset($subscribers) && $subscribers->count() > 0)
                                                @foreach($subscribers as $sub)
                                                    <option value="{{ $sub->id }}" {{ request('subscriber_id') == $sub->id ? 'selected' : '' }}>
                                                        {{ $sub->subscription_number }} - {{ $sub->subscriber_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    {{-- فلتر حالة القراءة --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-activity me-1"></i>
                                            حالة القراءة
                                        </label>
                                        <select id="readingStatusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            @foreach($readingStatuses as $item)
                                                <option value="{{ $item->value }}" {{ request('reading_status') == $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- فلتر الإجراء --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-check2-circle me-1"></i>
                                            إجراء القراءة
                                        </label>
                                        <select id="actionStatusFilter" class="form-select">
                                            <option value="">الكل</option>
                                            @foreach($readingActions as $item)
                                                <option value="{{ $item->value }}" {{ request('action_status') === $item->value ? 'selected' : '' }}>{{ $item->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- من تاريخ --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            من تاريخ
                                        </label>
                                        <input type="date" id="dateFromFilter" class="form-control" value="{{ request('date_from') }}">
                                    </div>

                                    {{-- إلى تاريخ --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            إلى تاريخ
                                        </label>
                                        <input type="date" id="dateToFilter" class="form-control" value="{{ request('date_to') }}">
                                    </div>

                                    {{-- البحث --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-search me-1"></i>
                                            البحث
                                        </label>
                                        <input type="text" id="searchInput" class="form-control" placeholder="ابحث برقم القراءة، رقم العداد، رقم الاشتراك، أو اسم المشترك..." value="{{ request('search') }}">
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-12 d-flex justify-content-center gap-2">
                                        <button class="btn btn-primary" type="button" id="searchBtn">
                                            <i class="bi bi-search me-1"></i>
                                            بحث
                                        </button>
                                        <button class="btn btn-outline-secondary d-none" type="button" id="clearBtn" title="تفريغ الحقول">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            تفريغ الحقول
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div id="meterReadingsListWrap" class="position-relative">
                            <div id="mrLoading" class="data-table-loading d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                                </div>
                            </div>

                            @include('admin.meter-readings.partials.list', ['meterReadings' => $meterReadings])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: اعتماد قراءة غير طبيعية --}}
    <div class="modal fade" id="approveAbnormalModal" tabindex="-1" aria-labelledby="approveAbnormalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="approveAbnormalModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        اعتماد قراءة غير طبيعية
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>تنبيه:</strong> هذه القراءة صُنِّفت تلقائياً كغير طبيعية بسبب استهلاك صفري أو سالب أو يتجاوز الحد الأقصى المسموح به.
                            يجب ذكر سبب موثق لاعتمادها.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">رقم القراءة</label>
                        <input type="text" class="form-control" id="abnormalReadingNumber" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            سبب الاعتماد <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="abnormalReason" rows="4"
                            placeholder="اذكر سبباً موثقاً لاعتماد هذه القراءة (مثل: تم التحقق الميداني، عطل في العداد، فترة توقف مسجلة...)"
                            maxlength="1000"></textarea>
                        <div class="invalid-feedback" id="abnormalReasonError"></div>
                        <div class="form-text text-muted"><span id="abnormalReasonCount">0</span>/1000 حرف</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        إلغاء
                    </button>
                    <button type="button" class="btn btn-warning text-dark" id="confirmAbnormalApprove">
                        <i class="bi bi-check-circle me-1"></i>
                        اعتماد القراءة
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script>
(function() {
    const listUrl       = @json(route('admin.meter-readings.index'));
    const bulkApproveUrl= @json(route('admin.meter-readings.bulk-approve'));
    const subscribersUrl= @json(route('admin.meter-readings.subscribers-by-operator'));
    const csrfToken     = document.getElementById('csrfToken').value;

    const $wrap              = $('#meterReadingsListWrap');
    const $operatorFilter    = $('#operatorFilter');
    const $subscriberFilter  = $('#subscriberFilter');
    const $readingStatusFilter = $('#readingStatusFilter');
    const $actionStatusFilter  = $('#actionStatusFilter');
    const $dateFromFilter    = $('#dateFromFilter');
    const $dateToFilter      = $('#dateToFilter');
    const $searchInput       = $('#searchInput');
    const $searchBtn         = $('#searchBtn');
    const $clearBtn          = $('#clearBtn');
    const $bulkApproveBtn    = $('#bulkApproveBtn');
    const $bulkApproveCount  = $('#bulkApproveCount');

    // تهيئة Select2 لفلتر المشترك
    $subscriberFilter.select2({
        placeholder: 'اختر أو ابحث باسم/رقم المشترك...',
        allowClear: true,
        dir: 'rtl',
        language: {
            noResults: function() { return 'لا توجد نتائج'; },
            searching: function() { return 'جاري البحث...'; }
        }
    });

    // عند تغيير المشغل: تحميل مشتركيه
    $operatorFilter.on('change', function() {
        loadSubscribersByOperator($(this).val());
        loadList();
    });

    function loadSubscribersByOperator(operatorId) {
        // إعادة تعيين المشتركين
        $subscriberFilter.val(null).trigger('change.select2');
        $subscriberFilter.find('option:not(:first)').remove();

        if (!operatorId) {
            return;
        }

        $.ajax({
            url: subscribersUrl,
            method: 'GET',
            data: { operator_id: operatorId },
            success: function(res) {
                if (res.success && res.subscribers) {
                    res.subscribers.forEach(function(s) {
                        $subscriberFilter.append(
                            new Option(s.subscription_number + ' - ' + s.subscriber_name, s.id, false, false)
                        );
                    });
                    $subscriberFilter.trigger('change.select2');
                }
            }
        });
    }

    function getLoading() { return $('#mrLoading'); }

    function setLoading(on) {
        const $load = getLoading();
        if (on) {
            $load.removeClass('d-none');
            $wrap.find('.table, .pagination, .empty-state').css('visibility', 'hidden');
        } else {
            $load.addClass('d-none');
            $wrap.find('.table, .pagination, .empty-state').css('visibility', 'visible');
        }
    }

    function currentParams(extra) {
        let operatorId = $operatorFilter.prop('disabled')
            ? ($('#operatorFilterHidden').val() || '')
            : ($operatorFilter.val() || '');

        // action_status: نرسل القيمة كما هي (حتى لو كانت "0")
        const actionVal = $actionStatusFilter.val();

        const p = {
            operator_id:    operatorId,
            subscriber_id:  $subscriberFilter.val() || '',
            reading_status: $readingStatusFilter.val() || '',
            action_status:  (actionVal !== null && actionVal !== undefined) ? actionVal : '',
            date_from:      $dateFromFilter.val() || '',
            date_to:        $dateToFilter.val() || '',
            search:         $searchInput.val() || '',
        };
        return Object.assign(p, extra || {});
    }

    function hasActiveFilter(p) {
        return !!(p.operator_id || p.subscriber_id || p.reading_status ||
                  p.action_status !== '' || p.date_from || p.date_to || p.search);
    }

    function updateClearBtn() {
        $clearBtn.toggleClass('d-none', !hasActiveFilter(currentParams()));
    }

    function loadList(extra) {
        const params = currentParams(extra);
        setLoading(true);

        $.ajax({
            url: listUrl,
            method: 'GET',
            data: params,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    $wrap.html(res.html);
                    $('#meterReadingsCount').text(res.count);
                    updateClearBtn();
                    updateBulkApproveBtn();
                    bindCheckboxEvents();
                    bindAbnormalBtns();
                }
                setLoading(false);
            },
            error: function() { setLoading(false); }
        });
    }

    // --- Bulk approve ---
    function getSelectedIds() {
        return $wrap.find('.reading-checkbox:checked').map(function() { return $(this).val(); }).get();
    }

    function updateBulkApproveBtn() {
        const ids = getSelectedIds();
        $bulkApproveBtn.toggleClass('d-none', ids.length === 0);
        $('#bulkApproveCount').text(ids.length);
    }

    function bindCheckboxEvents() {
        $wrap.find('#selectAllReadings').off('change').on('change', function() {
            $wrap.find('.reading-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkApproveBtn();
        });
        $wrap.find('.reading-checkbox').off('change').on('change', function() {
            const total = $wrap.find('.reading-checkbox').length;
            const checked = $wrap.find('.reading-checkbox:checked').length;
            $wrap.find('#selectAllReadings')
                .prop('indeterminate', checked > 0 && checked < total)
                .prop('checked', checked === total && total > 0);
            updateBulkApproveBtn();
        });
    }

    $bulkApproveBtn.on('click', function() {
        const ids = getSelectedIds();
        if (!ids.length) return;

        Swal.fire({
            title: 'اعتماد القراءات',
            html: 'هل تريد اعتماد <strong>' + ids.length + '</strong> قراءة وترحيلها لشاشة الفوترة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i>نعم، اعتماد',
            cancelButtonText: 'إلغاء',
            reverseButtons: true,
        }).then((result) => {
            if (!result.isConfirmed) return;

            $bulkApproveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> جاري الاعتماد...');

            $.ajax({
                url: bulkApproveUrl,
                method: 'POST',
                data: { ids: ids, _token: csrfToken },
                success: function(res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'تم بنجاح', text: res.message, timer: 3000, showConfirmButton: false });
                        loadList();
                    } else {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: res.message });
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'حدث خطأ.';
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                },
                complete: function() {
                    $bulkApproveBtn.prop('disabled', false)
                        .html('<i class="bi bi-check-circle me-1"></i> اعتماد المحدد <span class="badge bg-white text-success ms-1" id="bulkApproveCount">0</span>');
                }
            });
        });
    });

    // --- أحداث الفلاتر ---
    $searchBtn.on('click', function() { loadList(); });
    $searchInput.on('keypress', function(e) { if (e.which === 13) loadList(); });
    $subscriberFilter.on('change', function() { loadList(); });
    $readingStatusFilter.on('change', function() { updateClearBtn(); });
    $actionStatusFilter.on('change', function() { updateClearBtn(); });
    $dateFromFilter.on('change', function() { updateClearBtn(); });
    $dateToFilter.on('change', function() { updateClearBtn(); });

    $clearBtn.on('click', function() {
        $operatorFilter.val('');
        $subscriberFilter.val(null).trigger('change.select2');
        $subscriberFilter.find('option:not(:first)').remove();
        $readingStatusFilter.val('');
        $actionStatusFilter.val('');
        $dateFromFilter.val('');
        $dateToFilter.val('');
        $searchInput.val('');
        loadList();
    });

    // Pagination AJAX
    $(document).on('click', '#meterReadingsListWrap .pagination a', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href && href !== '#') {
            const url = new URL(href, window.location.origin);
            loadList({ page: url.searchParams.get('page') });
        }
    });

    bindCheckboxEvents();
    updateClearBtn();

    // --- اعتماد القراءات غير الطبيعية ---
    let currentAbnormalUrl = '';

    function bindAbnormalBtns() {
        $wrap.find('.approve-abnormal-btn').off('click').on('click', function() {
            const $btn = $(this);
            currentAbnormalUrl = $btn.data('url');
            $('#abnormalReadingNumber').val($btn.data('number'));
            $('#abnormalReason').val('').removeClass('is-invalid');
            $('#abnormalReasonError').text('');
            $('#abnormalReasonCount').text('0');
            const modal = new bootstrap.Modal(document.getElementById('approveAbnormalModal'));
            modal.show();
        });
    }

    $('#abnormalReason').on('input', function() {
        $('#abnormalReasonCount').text($(this).val().length);
        $(this).removeClass('is-invalid');
    });

    $('#confirmAbnormalApprove').on('click', function() {
        const reason = $('#abnormalReason').val().trim();
        if (!reason || reason.length < 5) {
            $('#abnormalReason').addClass('is-invalid');
            $('#abnormalReasonError').text('يجب إدخال سبب لا يقل عن 5 أحرف.');
            return;
        }
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> جاري الاعتماد...');

        $.ajax({
            url: currentAbnormalUrl,
            method: 'POST',
            data: { _token: csrfToken, abnormal_reason: reason },
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('approveAbnormalModal')).hide();
                    Swal.fire({ icon: 'success', title: 'تم بنجاح', text: res.message, timer: 3000, showConfirmButton: false });
                    loadList();
                } else {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: res.message });
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors?.abnormal_reason) {
                    $('#abnormalReason').addClass('is-invalid');
                    $('#abnormalReasonError').text(errors.abnormal_reason[0]);
                } else {
                    const msg = xhr.responseJSON?.message || 'حدث خطأ.';
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> اعتماد القراءة');
            }
        });
    });

    bindAbnormalBtns();
})();
</script>
@endpush