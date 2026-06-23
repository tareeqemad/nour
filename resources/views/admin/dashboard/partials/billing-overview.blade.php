{{-- Billing & Collection Overview — role-scoped (DashboardService::getBillingDashboard).
     Global roles get an operator picker + a period filter; the section refreshes
     over AJAX. Self-hides when $billingDashboard is null. --}}
@if($billingDashboard)
@php $isGlobal = $billingIsGlobal ?? auth()->user()->hasGlobalAccountingAccess(); @endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/libs/select2/select2.min.css') }}">
@endpush

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="d-section" id="billingSection" data-url="{{ route('admin.dashboard.billing') }}">
            <div class="d-section-head" style="flex-wrap:wrap; gap:.6rem;">
                <span class="d-section-title"><i class="bi bi-cash-coin me-1"></i> الفوترة والتحصيل</span>
                <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                    @if($isGlobal && $billingOperators->count())
                        <select id="billingOperatorSelect" class="form-select form-select-sm" style="min-width:210px;" title="اختر مشغّلاً">
                            <option value="">كل المشغّلين</option>
                            @foreach($billingOperators as $op)
                                <option value="{{ $op->id }}" {{ ($billingSelectedOperator ?? null) == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select id="billingRangeSelect" class="form-select form-select-sm" style="min-width:135px;" title="الفترة">
                        <option value="month"   {{ ($billingRange ?? 'month')=='month'   ? 'selected' : '' }}>هذا الشهر</option>
                        <option value="quarter" {{ ($billingRange ?? '')=='quarter' ? 'selected' : '' }}>آخر 3 أشهر</option>
                        <option value="half"    {{ ($billingRange ?? '')=='half'    ? 'selected' : '' }}>آخر 6 أشهر</option>
                        <option value="year"    {{ ($billingRange ?? '')=='year'    ? 'selected' : '' }}>هذه السنة</option>
                        <option value="all"     {{ ($billingRange ?? '')=='all'     ? 'selected' : '' }}>كل الفترات</option>
                        <option value="custom"  {{ ($billingRange ?? '')=='custom'  ? 'selected' : '' }}>مخصّص</option>
                    </select>
                    <div id="billingCustomDates" class="d-flex align-items-center gap-1 {{ ($billingRange ?? '')=='custom' ? '' : 'd-none' }}">
                        <input type="date" id="billingFromInput" class="form-control form-control-sm" value="{{ $billingFrom }}" style="width:145px;">
                        <span class="text-muted">—</span>
                        <input type="date" id="billingToInput" class="form-control form-control-sm" value="{{ $billingTo }}" style="width:145px;">
                        <button type="button" id="billingApplyBtn" class="btn btn-sm btn-primary">تطبيق</button>
                    </div>
                    <a href="{{ route('admin.invoice-reports.index') }}" class="d-section-link">التقرير التفصيلي <i class="bi bi-arrow-left"></i></a>
                </div>
            </div>
            <div class="d-section-body position-relative">
                <div id="billingLoading" class="d-none" style="position:absolute;inset:0;background:rgba(255,255,255,.65);z-index:5;display:flex;align-items:center;justify-content:center;border-radius:8px;">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="billingContent">
                    @include('admin.dashboard.partials.billing-content')
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/admin/libs/select2/select2.min.js') }}"></script>
<script>
(function () {
    const section = document.getElementById('billingSection');
    if (!section) return;
    const url      = section.dataset.url;
    const content  = document.getElementById('billingContent');
    const loading  = document.getElementById('billingLoading');
    const opSel    = document.getElementById('billingOperatorSelect');
    const rangeSel = document.getElementById('billingRangeSelect');
    const customW  = document.getElementById('billingCustomDates');
    const fromInp  = document.getElementById('billingFromInput');
    const toInp    = document.getElementById('billingToInput');
    const applyBtn = document.getElementById('billingApplyBtn');

    let chart = null;
    function initTrendChart() {
        const el = document.getElementById('billingTrendChart');
        if (!el || typeof Chart === 'undefined') return;
        if (chart) { chart.destroy(); chart = null; }
        const labels    = JSON.parse(el.dataset.labels    || '[]');
        const billed    = JSON.parse(el.dataset.billed    || '[]');
        const collected = JSON.parse(el.dataset.collected || '[]');
        chart = new Chart(el.getContext('2d'), {
            type: 'bar',
            data: { labels, datasets: [
                { label: 'إجمالي الفوترة', data: billed,    backgroundColor: 'rgba(36,48,143,.7)', borderRadius: 4 },
                { label: 'المحصّل',        data: collected, backgroundColor: 'rgba(4,120,87,.7)',  borderRadius: 4 },
            ]},
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { family: 'Tajawal, sans-serif' } } },
                    tooltip: { callbacks: { label: c => c.dataset.label + ': ' + Number(c.parsed.y).toLocaleString() + ' ₪' } }
                },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v >= 1000 ? (v / 1000) + 'K' : v } } }
            }
        });
    }

    function params() {
        const p = new URLSearchParams();
        if (opSel && opSel.value) p.set('bill_operator', opSel.value);
        const r = rangeSel ? rangeSel.value : 'month';
        p.set('bill_range', r);
        if (r === 'custom') {
            if (fromInp && fromInp.value) p.set('bill_from', fromInp.value);
            if (toInp && toInp.value)     p.set('bill_to', toInp.value);
        }
        return p.toString();
    }

    function reload() {
        loading.classList.remove('d-none');
        fetch(url + '?' + params(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => { content.innerHTML = d.html || ''; initTrendChart(); })
            .catch(() => {})
            .finally(() => loading.classList.add('d-none'));
    }

    if (opSel) {
        if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            jQuery(opSel).select2({ dir: 'rtl', width: '210px', language: 'ar' }).on('change', reload);
        } else {
            opSel.addEventListener('change', reload);
        }
    }
    if (rangeSel) {
        rangeSel.addEventListener('change', function () {
            if (this.value === 'custom') { customW.classList.remove('d-none'); }
            else { customW.classList.add('d-none'); reload(); }
        });
    }
    if (applyBtn) applyBtn.addEventListener('click', reload);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrendChart);
    } else {
        initTrendChart();
    }
})();
</script>
@endpush
@endif
