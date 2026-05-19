{{-- ╔══════════════════════════════════════════════════════════════════╗
     ║  Billing & Collection Overview                                   ║
     ║                                                                  ║
     ║  Visible when $billingDashboard is non-null. Scoping is enforced ║
     ║  in DashboardService::getBillingDashboard():                     ║
     ║    - SuperAdmin/Admin/EnergyAuthority/GeneralAccountant → all    ║
     ║    - CompanyOwner/Employee → owned/linked operators only         ║
     ║    - Technician/CivilDefense → null (section hidden)             ║
     ╚══════════════════════════════════════════════════════════════════╝ --}}
@if($billingDashboard)
@php
    $user           = auth()->user();
    $isGlobal       = $user->hasGlobalAccountingAccess();
    $cm             = $billingDashboard['current_month'];
    $trend          = $billingDashboard['trend'];
    $aging          = $billingDashboard['aging'];
    $topDebtors     = $billingDashboard['top_debtors'];
    $byOperator     = $billingDashboard['by_operator'];

    $rateColor = $cm['collection_rate'] >= 80 ? '#047857' : ($cm['collection_rate'] >= 50 ? '#B45309' : '#B91C1C');
    $monthName = now()->translatedFormat('F Y');
@endphp

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="d-section">
            <div class="d-section-head">
                <span class="d-section-title">
                    <i class="bi bi-cash-coin me-1"></i> الفوترة والتحصيل
                    <small class="text-muted fw-normal ms-2">— {{ $monthName }}</small>
                </span>
                <a href="{{ route('admin.invoice-reports.index') }}" class="d-section-link">
                    التقرير التفصيلي <i class="bi bi-arrow-left"></i>
                </a>
            </div>
            <div class="d-section-body">

                {{-- KPI Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="dash-kpi">
                            <div class="dash-kpi-icon" style="background:#EEF2FF;color:#24308F;"><i class="bi bi-receipt"></i></div>
                            <div>
                                <div class="dash-kpi-value">{{ number_format($cm['billed'], 0) }} ₪</div>
                                <div class="dash-kpi-label">إجمالي الفوترة</div>
                                <div class="small text-muted">{{ $cm['invoice_count'] }} فاتورة / {{ $cm['subscriber_count'] }} مشترك</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dash-kpi">
                            <div class="dash-kpi-icon" style="background:#ECFDF5;color:#047857;"><i class="bi bi-check-circle"></i></div>
                            <div>
                                <div class="dash-kpi-value">{{ number_format($cm['collected'], 0) }} ₪</div>
                                <div class="dash-kpi-label">المحصّل</div>
                                <div class="small text-muted">متبقي: {{ number_format($cm['remaining'], 0) }} ₪</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dash-kpi">
                            <div class="dash-kpi-icon" style="background:rgba({{ $cm['collection_rate'] >= 80 ? '4,120,87' : ($cm['collection_rate'] >= 50 ? '180,83,9' : '185,28,28') }}, .1);color:{{ $rateColor }};">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div>
                                <div class="dash-kpi-value" style="color:{{ $rateColor }};">{{ $cm['collection_rate'] }}%</div>
                                <div class="dash-kpi-label">نسبة التحصيل</div>
                                <div class="progress mt-1" style="height:4px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width:{{ min($cm['collection_rate'], 100) }}%; background:{{ $rateColor }};"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="dash-kpi">
                            <div class="dash-kpi-icon" style="background:#FEF2F2;color:#B91C1C;"><i class="bi bi-exclamation-triangle"></i></div>
                            <div>
                                <div class="dash-kpi-value">{{ number_format($cm['overdue_amount'], 0) }} ₪</div>
                                <div class="dash-kpi-label">إجمالي المتأخرات</div>
                                <div class="small text-muted">{{ number_format($cm['overdue_count']) }} فاتورة متأخرة</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    {{-- Trend Chart (last 6 months) — for everyone --}}
                    <div class="col-12 col-lg-8">
                        <div class="border rounded p-3 h-100" style="background:#FAFCFF;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>اتجاه الفوترة والتحصيل (آخر 6 شهور)</strong>
                            </div>
                            <div style="height:240px;">
                                <canvas id="billingTrendChart"
                                        data-labels='@json($trend["labels"])'
                                        data-billed='@json($trend["billed"])'
                                        data-collected='@json($trend["collected"])'></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Aging Buckets --}}
                    <div class="col-12 col-lg-4">
                        <div class="border rounded p-3 h-100" style="background:#FAFCFF;">
                            <strong class="d-block mb-3">المتأخرات حسب العمر</strong>
                            @php
                                $maxAging = max(1, max(array_column($aging, 'amount')));
                                $agingColors = ['#FBBF24', '#F59E0B', '#EF4444', '#7F1D1D'];
                            @endphp
                            @foreach($aging as $i => $bucket)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-semibold">{{ $bucket['label'] }}</span>
                                        <span class="text-muted">{{ $bucket['count'] }} فاتورة — {{ number_format($bucket['amount'], 0) }} ₪</span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width:{{ $bucket['amount'] > 0 ? max(round($bucket['amount'] / $maxAging * 100, 1), 3) : 0 }}%; background:{{ $agingColors[$i] ?? '#6B7280' }};"></div>
                                    </div>
                                </div>
                            @endforeach
                            @if(collect($aging)->sum('count') === 0)
                                <div class="text-center text-muted py-3">
                                    <i class="bi bi-emoji-smile" style="font-size:1.5rem;"></i>
                                    <div class="mt-2 small">لا توجد متأخرات</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Role-specific bottom section --}}
                <div class="row g-3 mt-1">
                    @if($isGlobal)
                        {{-- ─── Aggregate view: per-operator comparison ─── --}}
                        <div class="col-12">
                            <div class="border rounded p-3" style="background:#FAFCFF;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <strong>أداء المشغلين</strong>
                                    <span class="small text-muted">مرتب حسب الأقل أداءً</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>المشغل</th>
                                                <th class="text-center">الفواتير</th>
                                                <th class="text-end">الفوترة</th>
                                                <th class="text-end">المحصّل</th>
                                                <th class="text-end">المتبقي</th>
                                                <th class="text-center" style="min-width:140px;">نسبة التحصيل</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($byOperator as $op)
                                                @php
                                                    $opColor = $op['collection_rate'] >= 80 ? 'success' : ($op['collection_rate'] >= 50 ? 'warning' : 'danger');
                                                    $noBilling = $op['invoice_count'] === 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span class="fw-semibold">{{ $op['name'] }}</span>
                                                        @if($noBilling)
                                                            <span class="badge bg-secondary ms-2" title="لم يبدأ بإصدار فواتير بعد">لم يبدأ</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ number_format($op['invoice_count']) }}</td>
                                                    <td class="text-end">{{ number_format($op['billed'], 0) }} ₪</td>
                                                    <td class="text-end text-success">{{ number_format($op['collected'], 0) }} ₪</td>
                                                    <td class="text-end {{ $op['remaining'] > 0 ? 'text-danger' : 'text-muted' }}">
                                                        {{ number_format($op['remaining'], 0) }} ₪
                                                    </td>
                                                    <td>
                                                        @if($noBilling)
                                                            <span class="text-muted small">—</span>
                                                        @else
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="progress flex-grow-1" style="height:6px;">
                                                                    <div class="progress-bar bg-{{ $opColor }}" style="width:{{ min($op['collection_rate'], 100) }}%;"></div>
                                                                </div>
                                                                <span class="badge bg-{{ $opColor }}" style="min-width:54px;">{{ $op['collection_rate'] }}%</span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-3">لا توجد بيانات</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- ─── Operator view: top debtors ─── --}}
                        <div class="col-12">
                            <div class="border rounded p-3" style="background:#FAFCFF;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <strong>أكبر المتأخرين عليك</strong>
                                    <a href="{{ route('admin.invoice-reports.index') }}" class="small text-decoration-none">
                                        كل المتأخرات <i class="bi bi-arrow-left"></i>
                                    </a>
                                </div>
                                @if(count($topDebtors) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>المشترك</th>
                                                    <th>رقم الاشتراك</th>
                                                    <th class="text-center">عدد الفواتير</th>
                                                    <th class="text-end">المتبقي</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topDebtors as $i => $d)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td class="fw-semibold">{{ $d['subscriber_name'] }}</td>
                                                        <td class="text-muted">{{ $d['subscription_number'] }}</td>
                                                        <td class="text-center">{{ $d['invoice_count'] }}</td>
                                                        <td class="text-end text-danger fw-bold">{{ number_format($d['remaining'], 0) }} ₪</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="bi bi-emoji-smile" style="font-size:1.5rem;"></i>
                                        <div class="mt-2 small">لا توجد متأخرات على مشتركيك حالياً</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const el = document.getElementById('billingTrendChart');
    if (!el || typeof Chart === 'undefined') return;

    const labels    = JSON.parse(el.dataset.labels    || '[]');
    const billed    = JSON.parse(el.dataset.billed    || '[]');
    const collected = JSON.parse(el.dataset.collected || '[]');

    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'إجمالي الفوترة',
                    data: billed,
                    backgroundColor: 'rgba(36, 48, 143, 0.7)',
                    borderRadius: 4,
                },
                {
                    label: 'المحصّل',
                    data: collected,
                    backgroundColor: 'rgba(4, 120, 87, 0.7)',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Tajawal, sans-serif' } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': ' + Number(ctx.parsed.y).toLocaleString() + ' ₪'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => v >= 1000 ? (v / 1000) + 'K' : v
                    }
                }
            }
        }
    });
})();
</script>
@endpush
@endif
