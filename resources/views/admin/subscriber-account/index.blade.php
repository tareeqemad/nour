@extends('layouts.admin')

@section('title', 'حساب المشترك')

@php
    $breadcrumbTitle = 'حساب المشترك';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/data-table-loading.css') }}">
@endpush

@section('content')
<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

<div class="general-page">
    <div class="row g-3">
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="حساب المشترك" icon="bi-person-lines-fill" />

                <div class="card-body pb-4">
                    <div class="row g-3">

                        {{-- المشغل --}}
                        @if($canSelectOperator && $operators->count() > 0)
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1"></i>
                                    المشغل
                                </label>
                                <select id="operatorFilter" class="form-select">
                                    <option value="">كل المشغلين</option>
                                    @foreach($operators as $op)
                                        <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>
                                            {{ $op->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif(isset($currentOperator))
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building me-1"></i>
                                    المشغل
                                </label>
                                <select id="operatorFilter" class="form-select" disabled>
                                    <option value="{{ $currentOperator->id }}" selected>{{ $currentOperator->name }}</option>
                                </select>
                                <input type="hidden" id="operatorFilterHidden" value="{{ $currentOperator->id }}">
                            </div>
                        @endif

                        {{-- المشترك (Select2 مع بحث) --}}
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>
                                المشترك
                                <small class="fw-normal" style="color: var(--color-text-muted, #5B6780);">— اختر للانتقال لحسابه</small>
                            </label>
                            <select id="subscriberSelect" class="form-select" style="width:100%">
                                <option value="">ابحث باسم أو رقم اشتراك ...</option>
                                @foreach($subscribers as $sub)
                                    <option value="{{ $sub->id }}"
                                        data-url="{{ route('admin.subscriber-account.show', $sub) }}">
                                        {{ $sub->subscription_number }} — {{ $sub->subscriber_name }}
                                        @if($sub->phone) &lrm;({{ $sub->phone }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- بحث نصي --}}
                        <div class="col">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1"></i>
                                بحث في القائمة
                            </label>
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control"
                                       placeholder="اسم، رقم اشتراك، جوال، رقم عداد..."
                                       value="{{ $search }}">
                                <button class="btn btn-primary" id="searchBtn" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-outline-secondary" type="button" id="clearBtn">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            تفريغ
                        </button>
                    </div>


                    <div id="subscribersListWrap" class="position-relative">
                        <div id="subLoading" class="data-table-loading d-none">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 text-muted fw-semibold">جاري التحميل...</div>
                            </div>
                        </div>

                        @include('admin.subscriber-account.partials.list', ['subscribers' => $subscribers, 'search' => $search])
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script>
(function () {
    const listUrl     = @json(route('admin.subscriber-account.index'));
    const subsByOpUrl = @json(route('admin.invoices.subscribers-by-operator'));

    const $wrap        = $('#subscribersListWrap');
    const $opFilter    = $('#operatorFilter');
    const $subSelect   = $('#subscriberSelect');
    const $searchInput = $('#searchInput');
    const $searchBtn   = $('#searchBtn');
    const $clearBtn    = $('#clearBtn');

    // ===== Select2 للمشترك =====
    $subSelect.select2({
        placeholder: 'ابحث باسم أو رقم اشتراك أو جوال...',
        allowClear: true,
        dir: 'rtl',
        language: {
            noResults:  () => 'لا توجد نتائج',
            searching:  () => 'جاري البحث...',
        },
    });

    // اختيار مشترك → انتقل مباشرةً لصفحة حسابه
    $subSelect.on('select2:select', function () {
        const url = $(this).find('option:selected').data('url');
        if (url) window.location.href = url;
    });

    // تغيير المشغل → أعد تحميل المشتركين
    $opFilter.on('change', function () {
        loadSubscribersByOperator($(this).val());
        loadList();
    });

    function loadSubscribersByOperator(opId) {
        $subSelect.val(null).trigger('change.select2');
        $subSelect.find('option:not(:first)').remove();
        if (!opId) return;
        $.get(subsByOpUrl, { operator_id: opId }, function (res) {
            if (res.success) {
                res.subscribers.forEach(function (s) {
                    const showUrl = listUrl.replace(/\/subscriber-account$/, '/subscriber-account/' + s.id);
                    const opt = new Option(
                        s.subscription_number + ' — ' + s.subscriber_name,
                        s.id, false, false
                    );
                    $(opt).attr('data-url', showUrl);
                    $subSelect.append(opt);
                });
                $subSelect.trigger('change.select2');
            }
        });
    }

    // ===== AJAX للقائمة =====
    function getOpId() {
        return $opFilter.prop('disabled')
            ? ($('#operatorFilterHidden').val() || '')
            : ($opFilter.val() || '');
    }

    function currentParams(extra) {
        return Object.assign({
            operator_id: getOpId(),
            search:      $searchInput.val().trim(),
        }, extra || {});
    }

    function hasFilter(p) {
        return !!(p.operator_id || p.search);
    }

    function loadList(extra) {
        const p = currentParams(extra);
        $('#subLoading').removeClass('d-none');
        $wrap.find('.table, .pagination, .empty-state').css('visibility', 'hidden');

        $.ajax({
            url: listUrl,
            method: 'GET',
            data: p,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                if (res.success) {
                    $wrap.html(res.html);
                    $clearBtn.toggleClass('d-none', !hasFilter(p));
                }
            },
            complete: function () {
                $('#subLoading').addClass('d-none');
                $wrap.find('.table, .pagination, .empty-state').css('visibility', 'visible');
            }
        });
    }

    $searchBtn.on('click', () => loadList());
    $searchInput.on('keypress', function (e) { if (e.which === 13) loadList(); });

    $clearBtn.on('click', function () {
        if (!$opFilter.prop('disabled')) $opFilter.val('');
        $subSelect.val(null).trigger('change.select2');
        $subSelect.find('option:not(:first)').remove();
        $searchInput.val('');
        loadList();
    });

    // Pagination AJAX
    $(document).on('click', '#subscribersListWrap .pagination a', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href && href !== '#') {
            const url = new URL(href, window.location.origin);
            loadList({ page: url.searchParams.get('page') });
        }
    });

    $clearBtn.toggleClass('d-none', !hasFilter(currentParams()));
})();
</script>
@endpush
