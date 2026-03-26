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
                        @can('create', App\Models\Invoice::class)
                            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>
                                إنشاء فاتورة جديدة
                            </a>
                        @endcan
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
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script>
(function () {
    const listUrl       = @json(route('admin.invoices.index'));
    const subsByOpUrl   = @json(route('admin.invoices.subscribers-by-operator'));

    const $wrap      = $('#invoicesListWrap');
    const $opFilter  = $('#operatorFilter');
    const $subFilter = $('#subscriberFilter');
    const $stFilter  = $('#statusFilter');
    const $dfFilter  = $('#dateFromFilter');
    const $dtFilter  = $('#dateToFilter');
    const $search    = $('#searchInput');
    const $searchBtn = $('#searchBtn');
    const $clearBtn  = $('#clearBtn');

    $subFilter.select2({
        placeholder: 'اختر أو ابحث...', allowClear: true, dir: 'rtl',
        language: { noResults: () => 'لا توجد نتائج', searching: () => 'جاري البحث...' }
    });

    $opFilter.on('change', function () {
        loadSubscribersByOperator($(this).val());
        loadList();
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

    function loadList(extra) {
        const p = params(extra);
        $('#invLoading').removeClass('d-none');
        $wrap.find('.table,.pagination,.empty-state').css('visibility','hidden');

        $.ajax({
            url: listUrl, method: 'GET', data: p,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success(res) {
                if (res.success) {
                    $wrap.html(res.html);
                    $('#invoicesCount').text(res.count);
                    $clearBtn.toggleClass('d-none', !hasFilter(p));
                    // تفعيل tooltips على العناصر الجديدة
                    $wrap[0].querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                        new bootstrap.Tooltip(el, { trigger: 'hover' });
                    });
                }
            },
            complete() {
                $('#invLoading').addClass('d-none');
                $wrap.find('.table,.pagination,.empty-state').css('visibility','visible');
            }
        });
    }

    $searchBtn.on('click', () => loadList());
    $search.on('keypress', e => { if (e.which === 13) loadList(); });
    $subFilter.on('change', () => loadList());
    $stFilter.on('change', () => { $clearBtn.toggleClass('d-none', !hasFilter(params())); });
    $dfFilter.on('change', () => { $clearBtn.toggleClass('d-none', !hasFilter(params())); });
    $dtFilter.on('change', () => { $clearBtn.toggleClass('d-none', !hasFilter(params())); });

    $clearBtn.on('click', function () {
        $opFilter.val('');
        $subFilter.val(null).trigger('change.select2');
        $subFilter.find('option:not(:first)').remove();
        $stFilter.val(''); $dfFilter.val(''); $dtFilter.val(''); $search.val('');
        loadList();
    });

    $(document).on('click', '#invoicesListWrap .pagination a', function (e) {
        e.preventDefault();
        const url = new URL($(this).attr('href'), window.location.origin);
        loadList({ page: url.searchParams.get('page') });
    });
})();
</script>
<script>
// تفعيل tooltips في القائمة بعد كل تحميل AJAX
$(document).on('htmx:afterSwap ajaxSuccess', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
// تفعيل أولي عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
// إعادة تفعيل بعد كل تحميل AJAX للقائمة
$(document).on('ajaxComplete', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]:not(.tooltip-init)').forEach(function (el) {
        el.classList.add('tooltip-init');
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
</script>
@endpush
