@extends('layouts.admin')

@section('title', 'تقارير الفوترة والتحصيل')
@php
    $breadcrumbTitle = 'تقارير الفوترة';

    // ===== حسابات مشتركة =====
    $totalIssuedAmount  = (float)($issuedInPeriod->sum_invoice ?? 0);
    $totalBilledAll     = $debitBalances->sum('total_billed') + $creditBalances->sum('total_billed');
    $totalPaidAll       = $debitBalances->sum('total_paid')   + $creditBalances->sum('total_paid');
    $collectionRate     = $totalBilledAll > 0 ? round(($totalPaidAll / $totalBilledAll) * 100, 1) : 0;
    $netBalance         = round($totalBilledAll - $totalPaidAll, 2);

    // تصنيف المتأخرات حسب العمر
    $aging0_30  = $overdueInvoices->filter(fn($i) => $i->days_overdue <= 30);
    $aging31_60 = $overdueInvoices->filter(fn($i) => $i->days_overdue > 30 && $i->days_overdue <= 60);
    $aging60p   = $overdueInvoices->filter(fn($i) => $i->days_overdue > 60);
@endphp

@section('content')
<div class="general-page">

    {{-- ===== فلاتر ===== --}}
    <x-admin.card class="mb-3 no-print">
        <x-admin.card-header title="تقارير الفوترة والتحصيل" icon="bi-bar-chart-line">
            <x-slot:actions>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-printer me-1"></i>طباعة التقرير
                </button>
            </x-slot:actions>
        </x-admin.card-header>
        <div class="card-body pb-3">
            <form method="GET" action="{{ route('admin.invoice-reports.index') }}" id="reportForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">من تاريخ</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">إلى تاريخ</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    @if($canSelectOperator)
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">المشغل</label>
                        <select name="operator_id" class="form-select">
                            <option value="0">جميع المشغلين</option>
                            @foreach($operators as $op)
                                <option value="{{ $op->id }}" {{ $selectedOpId == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>عرض التقرير
                        </button>
                    </div>
                </div>
                {{-- اختصارات الفترة --}}
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <span class="text-muted small align-self-center">فترة سريعة:</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-range" data-range="this_month">هذا الشهر</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-range" data-range="last_month">الشهر الماضي</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-range" data-range="last_3months">آخر 3 أشهر</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-range" data-range="this_year">هذا العام</button>
                    <a href="{{ route('admin.invoice-reports.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>تفريغ
                    </a>
                </div>
            </form>
        </div>
    </x-admin.card>

    {{-- ===== رأس التقرير للطباعة ===== --}}
    <div class="print-header d-none">
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-1">تقرير الفوترة والتحصيل</h4>
            <p class="text-muted mb-0">
                الفترة: {{ $dateFrom }} ← {{ $dateTo }}
                @if($canSelectOperator && $selectedOpId > 0)
                    &nbsp;|&nbsp; المشغل: {{ $operators->firstWhere('id', $selectedOpId)?->name ?? 'غير محدد' }}
                @endif
                &nbsp;|&nbsp; تاريخ الإصدار: {{ now()->format('Y-m-d') }}
            </p>
        </div>
        <hr>
    </div>

    {{-- ===== بطاقات المؤشرات الرئيسية ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-primary"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($issuedInPeriod->count ?? 0) }}</div>
                    <div class="dash-kpi-label">الفواتير الصادرة</div>
                    <div style="font-size:0.75rem;font-weight:600;color:var(--color-primary,#24308F);margin-top:0.1rem;">{{ number_format($totalIssuedAmount, 2) }} ₪</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($unpaidSummary['count']) }}</div>
                    <div class="dash-kpi-label">غير مسددة</div>
                    <div style="font-size:0.75rem;font-weight:600;color:var(--color-warning-text,#B45309);margin-top:0.1rem;">{{ number_format($unpaidSummary['total'], 2) }} ₪</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-danger"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($overdueSummary['count']) }}</div>
                    <div class="dash-kpi-label">متأخرة</div>
                    <div style="font-size:0.75rem;font-weight:600;color:var(--color-danger-text,#B91C1C);margin-top:0.1rem;">{{ number_format($overdueSummary['total'], 2) }} ₪</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-success"><i class="bi bi-lightning-charge"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($consumptionPeriod->total_kwh ?? 0, 0) }}</div>
                    <div class="dash-kpi-label">الاستهلاك kWh</div>
                    <div style="font-size:0.75rem;color:var(--color-text-muted,#5B6780);margin-top:0.1rem;">متوسط {{ number_format($consumptionPeriod->avg_kwh ?? 0, 1) }}/فاتورة</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#0284C7); color:#fff; border-radius:10px; width:40px; height:40px; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="dash-kpi-value">{{ $billedSubscribers->count() }}</div>
                    <div class="dash-kpi-label">مشترك مفوتر</div>
                    <div style="font-size:0.75rem;color:var(--color-text-muted,#5B6780);margin-top:0.1rem;">{{ $discountSummary['count'] }} بخصم</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="dash-kpi">
                @php $rateColor = $collectionRate >= 80 ? '#047857' : ($collectionRate >= 50 ? '#B45309' : '#B91C1C'); @endphp
                <div class="dash-kpi-icon" style="background: linear-gradient(135deg,{{ $rateColor }},{{ $rateColor }}cc); color:#fff; border-radius:10px; width:40px; height:40px; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="dash-kpi-value" style="color:{{ $rateColor }}">{{ $collectionRate }}%</div>
                    <div class="dash-kpi-label">نسبة التحصيل</div>
                    <div style="font-size:0.75rem;color:var(--color-text-muted,#5B6780);margin-top:0.1rem;">صافي {{ $netBalance > 0 ? '+' : '' }}{{ number_format($netBalance, 0) }} ₪</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== شريط نسبة التحصيل ===== --}}
    @if($totalBilledAll > 0)
    <x-admin.card class="mb-3">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-semibold small">نسبة التحصيل الإجمالية</span>
                <span class="fw-bold small" style="color:{{ $rateColor }}">{{ $collectionRate }}%</span>
            </div>
            <div class="progress" style="height:10px; border-radius:6px;">
                <div class="progress-bar" role="progressbar"
                    style="width:{{ min($collectionRate, 100) }}%; background:{{ $rateColor }}; border-radius:6px;"
                    aria-valuenow="{{ $collectionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between mt-1" style="font-size:0.75rem; color:var(--color-text-muted,#5B6780);">
                <span>محصّل: {{ number_format($totalPaidAll, 2) }} ₪</span>
                <span>متبقي: {{ number_format(max($totalBilledAll - $totalPaidAll, 0), 2) }} ₪</span>
                <span>إجمالي الفوترة: {{ number_format($totalBilledAll, 2) }} ₪</span>
            </div>
        </div>
    </x-admin.card>
    @endif

    <div class="row g-3">

        {{-- ===== القسم 1: ملخص الفترة ===== --}}
        <div class="col-12">
            <x-admin.card>
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-receipt me-2" style="color:var(--color-primary,#24308F);opacity:.75;"></i>
                        ملخص الفوترة في الفترة
                        <span class="badge bg-primary ms-2">{{ $dateFrom }}</span>
                        <span class="text-muted small fw-normal mx-1">←</span>
                        <span class="badge bg-primary">{{ $dateTo }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        @php
                            $statItems = [
                                ['val' => number_format($issuedInPeriod->count ?? 0),          'label' => 'عدد الفواتير الصادرة',   'color' => 'var(--color-primary,#24308F)'],
                                ['val' => number_format($totalIssuedAmount, 2) . ' ₪',         'label' => 'إجمالي قيمة الفواتير',   'color' => 'var(--color-primary,#24308F)'],
                                ['val' => number_format($issuedInPeriod->sum_total ?? 0, 2) . ' ₪', 'label' => 'إجمالي المبالغ المطلوبة', 'color' => 'var(--color-primary,#24308F)'],
                                ['val' => number_format($issuedInPeriod->sum_kwh ?? 0, 1) . ' kWh', 'label' => 'إجمالي الاستهلاك',  'color' => 'var(--color-success-text,#047857)'],
                                ['val' => $billedSubscribers->count(),                          'label' => 'مشترك مفوتر',            'color' => '#0284C7'],
                                ['val' => $discountSummary['count'],                            'label' => 'فاتورة بخصم',            'color' => 'var(--color-success-text,#047857)'],
                            ];
                        @endphp
                        @foreach($statItems as $stat)
                        <div class="col-md-2 col-6">
                            <div style="background:#FAFCFF;border:1px solid var(--color-border-soft,#EDF1F5);border-radius:8px;padding:0.85rem 0.5rem;">
                                <div style="font-size:1.1rem;font-weight:700;color:{{ $stat['color'] }};">{{ $stat['val'] }}</div>
                                <div class="text-muted" style="font-size:0.78rem;margin-top:0.1rem;">{{ $stat['label'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- ===== القسم 2: المشتركون المفوترون والخصومات ===== --}}
        <div class="col-md-6">
            <x-admin.card class="h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-people me-2" style="color:#0EA5E9;opacity:.75;"></i>
                        المشتركون المفوترون في الفترة
                        <span class="badge ms-2" style="background:#0EA5E9;">{{ $billedSubscribers->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($billedSubscribers->isEmpty())
                        <p class="text-muted text-center py-4 mb-0"><i class="bi bi-inbox me-1"></i>لا توجد بيانات في هذه الفترة</p>
                    @else
                    <div class="table-responsive" style="max-height:340px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="sticky-top" style="background:var(--color-bg-muted,#F7F9FC);">
                                <tr>
                                    <th>#</th>
                                    <th>المشترك</th>
                                    <th class="text-center">فواتير</th>
                                    <th class="text-end">المبلغ الكلي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($billedSubscribers->sortByDesc('total_billed')->values() as $i => $item)
                                <tr>
                                    <td class="text-muted small">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold small">{{ $item['subscriber']?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.72rem">{{ $item['subscriber']?->subscription_number ?? '' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $item['invoice_count'] }}</span>
                                    </td>
                                    <td class="text-end fw-semibold small" style="color:var(--color-primary,#24308F);">{{ number_format($item['total_billed'], 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:var(--color-bg-muted,#F7F9FC);font-weight:700;font-size:0.82rem;">
                                <tr>
                                    <td colspan="2" class="text-end">الإجمالي</td>
                                    <td class="text-center">{{ $billedSubscribers->sum('invoice_count') }}</td>
                                    <td class="text-end" style="color:var(--color-primary,#24308F);">{{ number_format($billedSubscribers->sum('total_billed'), 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        <div class="col-md-6">
            <x-admin.card class="h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-percent me-2" style="color:var(--color-success,#10B981);opacity:.75;"></i>
                        الخصومات الممنوحة في الفترة
                        <span class="badge bg-success ms-2">{{ $discountSummary['count'] }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center mb-3">
                        <div class="col-6">
                            <div style="background:#F0FDF4;border:1px solid #A7F3D0;border-radius:8px;padding:0.75rem;">
                                <div style="font-size:1.1rem;font-weight:700;color:var(--color-success-text,#047857);">{{ $discountSummary['count'] }}</div>
                                <div class="text-muted small">عدد الفواتير المخصومة</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#F0FDF4;border:1px solid #A7F3D0;border-radius:8px;padding:0.75rem;">
                                <div style="font-size:1.1rem;font-weight:700;color:var(--color-success-text,#047857);">{{ number_format($discountSummary['total_amount'], 2) }} ₪</div>
                                <div class="text-muted small">إجمالي مبالغ الخصومات</div>
                            </div>
                        </div>
                    </div>
                    @if($discounts->isNotEmpty())
                    <div class="table-responsive" style="max-height:220px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="sticky-top" style="background:var(--color-bg-muted,#F7F9FC);">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-center">نسبة الخصم</th>
                                    <th class="text-end">مبلغ الخصم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($discounts->sortByDesc('discount_amount') as $inv)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $inv->subscriber?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.72rem">{{ $inv->invoice_date?->format('Y-m-d') }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($inv->discount_rate, 0) }}%</span>
                                    </td>
                                    <td class="text-end fw-semibold small" style="color:var(--color-success-text,#047857);">{{ number_format($inv->discount_amount, 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="text-muted text-center py-2 mb-0 small"><i class="bi bi-inbox me-1"></i>لا توجد خصومات في هذه الفترة</p>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== القسم 3: الفواتير غير المسددة ===== --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="الفواتير غير المسددة" icon="bi-hourglass-split">
                    <x-slot:actions>
                        <span style="font-size:0.82rem;background:#FEF3C7;color:#92400E;padding:2px 10px;border-radius:99px;font-weight:600;">
                            {{ $unpaidSummary['count'] }} فاتورة &mdash; {{ number_format($unpaidSummary['total'], 2) }} ₪
                        </span>
                    </x-slot:actions>
                </x-admin.card-header>
                <div class="card-body p-0">
                    @if($unpaidInvoices->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="bi bi-check-circle me-1" style="color:var(--color-success,#10B981);"></i>
                            لا توجد فواتير غير مسددة
                        </p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead style="background:var(--color-bg-muted,#F7F9FC);">
                                <tr>
                                    <th>#</th>
                                    <th>رقم الفاتورة</th>
                                    <th>المشترك</th>
                                    <th class="text-center">التاريخ</th>
                                    <th class="text-center">الحالة</th>
                                    <th class="text-end">المبلغ الكلي</th>
                                    <th class="text-end">المسدد</th>
                                    <th class="text-end">المتبقي</th>
                                    <th class="no-print"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidInvoices->sortByDesc('remaining')->values() as $i => $inv)
                                <tr>
                                    <td class="text-muted small">{{ $i + 1 }}</td>
                                    <td class="fw-semibold small">{{ $inv->invoice_number ?? '—' }}</td>
                                    <td>
                                        <div class="small fw-semibold">{{ $inv->subscriber?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.72rem">{{ $inv->subscriber?->subscription_number }}</div>
                                    </td>
                                    <td class="text-center small">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $inv->status_badge_class }}">{{ $inv->status_name }}</span>
                                    </td>
                                    <td class="text-end small">{{ number_format($inv->total_amount, 2) }} ₪</td>
                                    <td class="text-end small" style="color:var(--color-success-text,#047857);">{{ number_format($inv->payments_sum_amount_paid ?? 0, 2) }} ₪</td>
                                    <td class="text-end fw-bold small" style="color:var(--color-warning-text,#B45309);">{{ number_format($inv->remaining, 2) }} ₪</td>
                                    <td class="no-print">
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:var(--color-bg-muted,#F7F9FC);font-weight:700;font-size:0.82rem;">
                                <tr>
                                    <td colspan="7" class="text-end">الإجمالي المتبقي:</td>
                                    <td class="text-end" style="color:var(--color-warning-text,#B45309);">{{ number_format($unpaidSummary['total'], 2) }} ₪</td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== القسم 4: الفواتير المتأخرة مع تحليل العمر ===== --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="الفواتير المتأخرة" icon="bi-exclamation-triangle">
                    <x-slot:actions>
                        <span style="font-size:0.82rem;background:#FEE2E2;color:#991B1B;padding:2px 10px;border-radius:99px;font-weight:600;">
                            {{ $overdueSummary['count'] }} فاتورة &mdash; {{ number_format($overdueSummary['total'], 2) }} ₪
                        </span>
                    </x-slot:actions>
                </x-admin.card-header>
                <div class="card-body">
                    {{-- تحليل عمر الديون --}}
                    @if($overdueInvoices->isNotEmpty())
                    <div class="row g-2 mb-3">
                        @php
                            $agingBuckets = [
                                ['label' => '0 – 30 يوم',  'items' => $aging0_30,  'color' => '#F59E0B', 'bg' => '#FFFBEB', 'border' => '#FCD34D'],
                                ['label' => '31 – 60 يوم', 'items' => $aging31_60, 'color' => '#EF4444', 'bg' => '#FFF1F2', 'border' => '#FCA5A5'],
                                ['label' => 'أكثر من 60 يوم', 'items' => $aging60p, 'color' => '#991B1B', 'bg' => '#FEE2E2', 'border' => '#F87171'],
                            ];
                        @endphp
                        @foreach($agingBuckets as $bucket)
                        <div class="col-md-4">
                            <div style="background:{{ $bucket['bg'] }};border:1px solid {{ $bucket['border'] }};border-radius:8px;padding:0.75rem;text-align:center;">
                                <div style="font-size:0.75rem;font-weight:600;color:{{ $bucket['color'] }};margin-bottom:0.3rem;">{{ $bucket['label'] }}</div>
                                <div style="font-size:1.1rem;font-weight:700;color:{{ $bucket['color'] }};">{{ $bucket['items']->count() }} فاتورة</div>
                                <div style="font-size:0.78rem;color:{{ $bucket['color'] }};">{{ number_format($bucket['items']->sum('remaining'), 2) }} ₪</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($overdueInvoices->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">
                            <i class="bi bi-check-circle me-1" style="color:var(--color-success,#10B981);"></i>
                            لا توجد فواتير متأخرة
                        </p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead style="background:var(--color-bg-muted,#F7F9FC);">
                                <tr>
                                    <th>#</th>
                                    <th>رقم الفاتورة</th>
                                    <th>المشترك</th>
                                    <th class="text-center">تاريخ الفاتورة</th>
                                    <th class="text-center">تاريخ الاستحقاق</th>
                                    <th class="text-center" style="color:var(--color-danger-text,#B91C1C);">أيام التأخر</th>
                                    <th class="text-end">المتبقي</th>
                                    <th class="no-print"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueInvoices->sortByDesc('days_overdue')->values() as $i => $inv)
                                @php
                                    $rowBg = $inv->days_overdue > 60 ? '#FEE2E2' : ($inv->days_overdue > 30 ? '#FFF1F2' : '');
                                @endphp
                                <tr style="{{ $rowBg ? 'background:'.$rowBg.';' : '' }}">
                                    <td class="text-muted small">{{ $i + 1 }}</td>
                                    <td class="fw-semibold small">{{ $inv->invoice_number ?? '—' }}</td>
                                    <td>
                                        <div class="small fw-semibold">{{ $inv->subscriber?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.72rem">{{ $inv->subscriber?->subscription_number }}</div>
                                    </td>
                                    <td class="text-center small">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                                    <td class="text-center small" style="color:var(--color-danger-text,#B91C1C);">{{ $inv->due_date?->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        <span class="badge" style="background:{{ $inv->days_overdue > 60 ? '#991B1B' : ($inv->days_overdue > 30 ? '#EF4444' : '#F59E0B') }};">
                                            {{ $inv->days_overdue }} يوم
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold small" style="color:var(--color-danger-text,#B91C1C);">{{ number_format($inv->remaining, 2) }} ₪</td>
                                    <td class="no-print">
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-outline-danger btn-sm py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:var(--color-bg-muted,#F7F9FC);font-weight:700;font-size:0.82rem;">
                                <tr>
                                    <td colspan="6" class="text-end">الإجمالي المتأخر:</td>
                                    <td class="text-end" style="color:var(--color-danger-text,#B91C1C);">{{ number_format($overdueSummary['total'], 2) }} ₪</td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== القسم 5: الأرصدة ===== --}}
        <div class="col-md-6">
            <x-admin.card class="h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-arrow-up-circle me-2" style="color:var(--color-danger,#EF4444);opacity:.75;"></i>
                        الأرصدة المدينة
                        <span class="ms-1 text-muted small fw-normal">(مديون على المشترك)</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($debitBalances->isEmpty())
                        <p class="text-muted text-center py-4 mb-0"><i class="bi bi-inbox me-1"></i>لا توجد بيانات</p>
                    @else
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="sticky-top" style="background:var(--color-bg-muted,#F7F9FC);">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-end">الفوترة</th>
                                    <th class="text-end">المسدد</th>
                                    <th class="text-end" style="color:var(--color-danger-text,#B91C1C);">المديونية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($debitBalances as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $item['subscriber']?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.72rem">{{ $item['subscriber']?->subscription_number }}</div>
                                    </td>
                                    <td class="text-end small">{{ number_format($item['total_billed'], 2) }} ₪</td>
                                    <td class="text-end small" style="color:var(--color-success-text,#047857);">{{ number_format($item['total_paid'], 2) }} ₪</td>
                                    <td class="text-end fw-bold small" style="color:var(--color-danger-text,#B91C1C);">{{ number_format($item['balance'], 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:var(--color-bg-muted,#F7F9FC);font-weight:700;font-size:0.82rem;">
                                <tr>
                                    <td colspan="3" class="text-end">إجمالي المديونيات:</td>
                                    <td class="text-end" style="color:var(--color-danger-text,#B91C1C);">{{ number_format($debitBalances->sum('balance'), 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        <div class="col-md-6">
            <x-admin.card class="h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-arrow-down-circle me-2" style="color:var(--color-success,#10B981);opacity:.75;"></i>
                        الأرصدة الدائنة
                        <span class="ms-1 text-muted small fw-normal">(دائن لصالح المشترك)</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($creditBalances->isEmpty())
                        <p class="text-muted text-center py-4 mb-0"><i class="bi bi-inbox me-1"></i>لا توجد أرصدة دائنة</p>
                    @else
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="sticky-top" style="background:var(--color-bg-muted,#F7F9FC);">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-end">الفوترة</th>
                                    <th class="text-end">المسدد</th>
                                    <th class="text-end" style="color:var(--color-success-text,#047857);">الرصيد الدائن</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($creditBalances as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $item['subscriber']?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.72rem">{{ $item['subscriber']?->subscription_number }}</div>
                                    </td>
                                    <td class="text-end small">{{ number_format($item['total_billed'], 2) }} ₪</td>
                                    <td class="text-end small" style="color:var(--color-success-text,#047857);">{{ number_format($item['total_paid'], 2) }} ₪</td>
                                    <td class="text-end fw-bold small" style="color:var(--color-success-text,#047857);">{{ number_format(abs($item['balance']), 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background:var(--color-bg-muted,#F7F9FC);font-weight:700;font-size:0.82rem;">
                                <tr>
                                    <td colspan="3" class="text-end">إجمالي الأرصدة الدائنة:</td>
                                    <td class="text-end" style="color:var(--color-success-text,#047857);">{{ number_format(abs($creditBalances->sum('balance')), 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

    </div>{{-- end row --}}
</div>

@push('styles')
<style>
/* ===== طباعة ===== */
@media print {
    .no-print, nav, aside, .sidebar, .topbar, header, footer,
    .breadcrumb, .alert, form { display: none !important; }
    .print-header { display: block !important; }
    .general-page { padding: 0 !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 1rem !important; break-inside: avoid; }
    .table-responsive { max-height: none !important; overflow: visible !important; }
    .progress { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    body { font-size: 11px !important; }
    .dash-kpi { border: 1px solid #ddd !important; }
}
</style>
@endpush

@push('scripts')
<script>
// اختصارات الفترة الزمنية
document.querySelectorAll('.quick-range').forEach(btn => {
    btn.addEventListener('click', function () {
        const range  = this.dataset.range;
        const today  = new Date();
        let from, to = today.toISOString().slice(0, 10);

        if (range === 'this_month') {
            from = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);
            to   = today.toISOString().slice(0, 10);
        } else if (range === 'last_month') {
            const first  = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const last   = new Date(today.getFullYear(), today.getMonth(), 0);
            from = first.toISOString().slice(0, 10);
            to   = last.toISOString().slice(0, 10);
        } else if (range === 'last_3months') {
            from = new Date(today.getFullYear(), today.getMonth() - 3, 1).toISOString().slice(0, 10);
        } else if (range === 'this_year') {
            from = new Date(today.getFullYear(), 0, 1).toISOString().slice(0, 10);
        }

        document.getElementById('date_from').value = from;
        document.getElementById('date_to').value   = to;
        document.getElementById('reportForm').submit();
    });
});
</script>
@endpush
@endsection
