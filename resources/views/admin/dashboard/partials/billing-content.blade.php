@php
    $user       = auth()->user();
    $isGlobal   = $user->hasGlobalAccountingAccess();
    $p          = $billingDashboard['period'];
    $trend      = $billingDashboard['trend'];
    $aging      = $billingDashboard['aging'];
    $topDebtors = $billingDashboard['top_debtors'];
    $byOperator = $billingDashboard['by_operator'];

    $range = $billingRange ?? 'month';
    $from  = $billingFrom ?? null;
    $to    = $billingTo ?? null;
    $periodLabel = match ($range) {
        'month'   => 'هذا الشهر — ' . now()->translatedFormat('F Y'),
        'quarter' => 'آخر 3 أشهر',
        'half'    => 'آخر 6 أشهر',
        'year'    => 'هذه السنة — ' . now()->year,
        'all'     => 'كل الفترات',
        'custom'  => ($from ? \Illuminate\Support\Carbon::parse($from)->format('Y/m/d') : '…') . ' ← ' . ($to ? \Illuminate\Support\Carbon::parse($to)->format('Y/m/d') : '…'),
        default   => '',
    };

    $rateColor = $p['collection_rate'] >= 80 ? '#047857' : ($p['collection_rate'] >= 50 ? '#B45309' : '#B91C1C');
@endphp

<div class="text-muted small mb-3"><i class="bi bi-calendar-range me-1"></i> الفترة: <strong>{{ $periodLabel }}</strong></div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:#EEF2FF;color:#24308F;"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="dash-kpi-value">{{ number_format($p['billed'], 0) }} ₪</div>
                <div class="dash-kpi-label">إجمالي الفوترة</div>
                <div class="small text-muted">{{ number_format($p['invoice_count']) }} فاتورة / {{ number_format($p['subscriber_count']) }} مشترك</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:#ECFDF5;color:#047857;"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="dash-kpi-value">{{ number_format($p['collected'], 0) }} ₪</div>
                <div class="dash-kpi-label">المحصّل</div>
                <div class="small text-muted">متبقي: {{ number_format($p['remaining'], 0) }} ₪</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:rgba({{ $p['collection_rate'] >= 80 ? '4,120,87' : ($p['collection_rate'] >= 50 ? '180,83,9' : '185,28,28') }}, .1);color:{{ $rateColor }};">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <div class="dash-kpi-value" style="color:{{ $rateColor }};">{{ $p['collection_rate'] }}%</div>
                <div class="dash-kpi-label">نسبة التحصيل</div>
                <div class="progress mt-1" style="height:4px;">
                    <div class="progress-bar" role="progressbar" style="width:{{ min($p['collection_rate'], 100) }}%; background:{{ $rateColor }};"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-kpi">
            <div class="dash-kpi-icon" style="background:#FEF2F2;color:#B91C1C;"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="dash-kpi-value">{{ number_format($p['overdue_amount'], 0) }} ₪</div>
                <div class="dash-kpi-label">إجمالي المتأخرات</div>
                <div class="small text-muted">{{ number_format($p['overdue_count']) }} فاتورة متأخرة — حتى الآن</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Trend Chart (last 6 months) --}}
    <div class="col-12 col-lg-8">
        <div class="border rounded p-3 h-100" style="background:#FAFCFF;">
            <strong class="d-block mb-2">اتجاه الفوترة والتحصيل (آخر 6 شهور)</strong>
            <div style="height:240px;">
                <canvas id="billingTrendChart"
                        data-labels='@json($trend["labels"])'
                        data-billed='@json($trend["billed"])'
                        data-collected='@json($trend["collected"])'></canvas>
            </div>
        </div>
    </div>

    {{-- Aging Buckets (as of now) --}}
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

{{-- Role-specific section --}}
@php $singleOp = $isGlobal && ! empty($billingSelectedOperator ?? null); @endphp
<div class="row g-3 mt-1">
    @if($isGlobal && ! $singleOp)
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
                                        @if($noBilling)<span class="badge bg-secondary ms-2">لا فواتير في الفترة</span>@endif
                                    </td>
                                    <td class="text-center">{{ number_format($op['invoice_count']) }}</td>
                                    <td class="text-end">{{ number_format($op['billed'], 0) }} ₪</td>
                                    <td class="text-end text-success">{{ number_format($op['collected'], 0) }} ₪</td>
                                    <td class="text-end {{ $op['remaining'] > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($op['remaining'], 0) }} ₪</td>
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
                                <tr><td colspan="6" class="text-center text-muted py-3">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="col-12">
            <div class="border rounded p-3" style="background:#FAFCFF;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <strong>{{ $singleOp ? 'أكبر المتأخرين — هذا المشغّل' : 'أكبر المتأخرين عليك' }}</strong>
                    <a href="{{ route('admin.invoice-reports.index') }}" class="small text-decoration-none">كل المتأخرات <i class="bi bi-arrow-left"></i></a>
                </div>
                @if(count($topDebtors) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th><th>المشترك</th><th>رقم الاشتراك</th>
                                    <th class="text-center">عدد الفواتير</th><th class="text-end">المتبقي</th>
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
