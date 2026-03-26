@extends('layouts.admin')

@section('title', 'تقارير الفوترة والتحصيل')
@php $breadcrumbTitle = 'تقارير الفوترة'; @endphp

@section('content')
<div class="general-page">

    {{-- ===== فلاتر ===== --}}
    <x-admin.card class="mb-3">
        <x-admin.card-header title="تقارير الفوترة والتحصيل" icon="bi-bar-chart-line" />
        <div class="card-body pb-3">
            <form method="GET" action="{{ route('admin.invoice-reports.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
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
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>عرض التقارير
                    </button>
                    <a href="{{ route('admin.invoice-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>تفريغ
                    </a>
                </div>
            </form>
        </div>
    </x-admin.card>

    {{-- ===== بطاقات الملخص ===== --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-primary"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($issuedInPeriod->count ?? 0) }}</div>
                    <div class="dash-kpi-label">الفواتير الصادرة</div>
                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--color-primary, #24308F); margin-top: 0.15rem;">{{ number_format($issuedInPeriod->sum_invoice ?? 0, 2) }} ₪</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($unpaidSummary['count']) }}</div>
                    <div class="dash-kpi-label">غير مسددة</div>
                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--color-warning-text, #B45309); margin-top: 0.15rem;">{{ number_format($unpaidSummary['total'], 2) }} ₪</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-danger"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($overdueSummary['count']) }}</div>
                    <div class="dash-kpi-label">متأخرة</div>
                    <div style="font-size: 0.78rem; font-weight: 600; color: var(--color-danger-text, #B91C1C); margin-top: 0.15rem;">{{ number_format($overdueSummary['total'], 2) }} ₪</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="dash-kpi">
                <div class="dash-kpi-icon kpi-success"><i class="bi bi-lightning-charge"></i></div>
                <div>
                    <div class="dash-kpi-value">{{ number_format($consumptionPeriod->total_kwh ?? 0, 1) }}</div>
                    <div class="dash-kpi-label">الاستهلاك kWh</div>
                    <div style="font-size: 0.78rem; color: var(--color-text-muted, #5B6780); margin-top: 0.15rem;">متوسط {{ number_format($consumptionPeriod->avg_kwh ?? 0, 1) }}/فاتورة</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ===== تقرير 1: الفواتير الصادرة في الفترة ===== --}}
        <div class="col-12">
            <x-admin.card>
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-receipt me-2" style="color: var(--color-primary, #24308F); opacity: .75;"></i>
                        1. الفواتير الصادرة خلال الفترة
                        <span class="badge bg-primary ms-2">{{ $dateFrom }}</span>
                        <span class="text-muted small fw-normal mx-1">←</span>
                        <span class="badge bg-primary">{{ $dateTo }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-md-3 col-6">
                            <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; padding: 0.75rem;">
                                <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary, #24308F);">{{ number_format($issuedInPeriod->count ?? 0) }}</div>
                                <div class="text-muted small">عدد الفواتير</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; padding: 0.75rem;">
                                <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary, #24308F);">{{ number_format($issuedInPeriod->sum_invoice ?? 0, 2) }} ₪</div>
                                <div class="text-muted small">إجمالي قيمة الفواتير</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; padding: 0.75rem;">
                                <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary, #24308F);">{{ number_format($issuedInPeriod->sum_total ?? 0, 2) }} ₪</div>
                                <div class="text-muted small">إجمالي المبالغ المطلوبة</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; padding: 0.75rem;">
                                <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-success-text, #047857);">{{ number_format($issuedInPeriod->sum_kwh ?? 0, 1) }} kWh</div>
                                <div class="text-muted small">إجمالي الاستهلاك</div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- ===== تقرير 6: المشتركون المفوترون ===== --}}
        <div class="col-md-6">
            <x-admin.card class="h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-people me-2" style="color: var(--color-info, #0EA5E9); opacity: .75;"></i>
                        6. المشتركون المفوترون في الفترة
                        <span class="badge bg-secondary ms-2">{{ $billedSubscribers->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($billedSubscribers->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="bi bi-inbox me-1"></i>لا توجد بيانات</p>
                    @else
                    <div class="table-responsive" style="max-height:340px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class=" sticky-top">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-center">عدد الفواتير</th>
                                    <th class="text-end">المبلغ الكلي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($billedSubscribers->sortByDesc('total_billed') as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $item['subscriber']?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem">{{ $item['subscriber']?->subscription_number ?? '' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $item['invoice_count'] }}</span>
                                    </td>
                                    <td class="text-end fw-semibold" style="color: var(--color-primary, #24308F);">{{ number_format($item['total_billed'], 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class=" fw-bold">
                                <tr>
                                    <td>الإجمالي</td>
                                    <td class="text-center">{{ $billedSubscribers->sum('invoice_count') }}</td>
                                    <td class="text-end" style="color: var(--color-primary, #24308F);">{{ number_format($billedSubscribers->sum('total_billed'), 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== تقرير 5: الخصومات ===== --}}
        <div class="col-md-6">
            <x-admin.card class="h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-percent me-2" style="color: var(--color-success, #10B981); opacity: .75;"></i>
                        5. الخصومات الممنوحة في الفترة
                        <span class="badge bg-success ms-2">{{ $discountSummary['count'] }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center mb-3">
                        <div class="col-6">
                            <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; padding: 0.75rem;">
                                <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-success-text, #047857);">{{ $discountSummary['count'] }}</div>
                                <div class="text-muted small">عدد الفواتير المخصومة</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background: #FAFCFF; border: 1px solid var(--color-border-soft, #EDF1F5); border-radius: 8px; padding: 0.75rem;">
                                <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-success-text, #047857);">{{ number_format($discountSummary['total_amount'], 2) }} ₪</div>
                                <div class="text-muted small">إجمالي مبالغ الخصومات</div>
                            </div>
                        </div>
                    </div>
                    @if($discounts->isNotEmpty())
                    <div class="table-responsive" style="max-height:220px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class=" sticky-top">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-center">النسبة</th>
                                    <th class="text-end">مبلغ الخصم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($discounts->sortByDesc('discount_amount') as $inv)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $inv->subscriber?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem">{{ $inv->invoice_date?->format('Y-m-d') }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ number_format($inv->discount_rate, 0) }}%</span>
                                    </td>
                                    <td class="text-end fw-semibold" style="color: var(--color-success-text, #047857);">{{ number_format($inv->discount_amount, 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== تقرير 2: الفواتير غير المسددة ===== --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="2. الفواتير غير المسددة" icon="bi-hourglass-split">
                    <x-slot:actions>
                        <span class="badge-warning" style="font-size: 0.82rem;">
                            {{ $unpaidSummary['count'] }} فاتورة — {{ number_format($unpaidSummary['total'], 2) }} ₪
                        </span>
                    </x-slot:actions>
                </x-admin.card-header>
                <div class="card-body p-0">
                    @if($unpaidInvoices->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="bi bi-check-circle me-1" style="color: var(--color-success, #10B981);"></i>لا توجد فواتير غير مسددة</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>المشترك</th>
                                    <th class="text-center">التاريخ</th>
                                    <th class="text-center">الحالة</th>
                                    <th class="text-end">المبلغ الكلي</th>
                                    <th class="text-end">المسدد</th>
                                    <th class="text-end">المتبقي</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidInvoices->sortByDesc('remaining') as $inv)
                                <tr>
                                    <td class="fw-semibold small">{{ $inv->invoice_number }}</td>
                                    <td>
                                        <div class="small fw-semibold">{{ $inv->subscriber?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem">{{ $inv->subscriber?->subscription_number }}</div>
                                    </td>
                                    <td class="text-center small">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $inv->status_badge_class }}">{{ $inv->status_name }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($inv->total_amount, 2) }} ₪</td>
                                    <td class="text-end" style="color: var(--color-success-text, #047857);">{{ number_format($inv->payments_sum_amount_paid ?? 0, 2) }} ₪</td>
                                    <td class="text-end fw-bold" style="color: var(--color-warning-text, #B45309);">{{ number_format($inv->remaining, 2) }} ₪</td>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class=" fw-bold">
                                <tr>
                                    <td colspan="6" class="text-end">الإجمالي المتبقي:</td>
                                    <td class="text-end" style="color: var(--color-warning-text, #B45309);">{{ number_format($unpaidSummary['total'], 2) }} ₪</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== تقرير 3: الفواتير المتأخرة ===== --}}
        <div class="col-12">
            <x-admin.card>
                <x-admin.card-header title="3. الفواتير المتأخرة" icon="bi-exclamation-triangle">
                    <x-slot:actions>
                        <span class="badge-danger" style="font-size: 0.82rem;">
                            {{ $overdueSummary['count'] }} فاتورة — {{ number_format($overdueSummary['total'], 2) }} ₪
                        </span>
                    </x-slot:actions>
                </x-admin.card-header>
                <div class="card-body p-0">
                    @if($overdueInvoices->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="bi bi-check-circle me-1" style="color: var(--color-success, #10B981);"></i>لا توجد فواتير متأخرة</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>المشترك</th>
                                    <th class="text-center">تاريخ الفاتورة</th>
                                    <th class="text-center">تاريخ الاستحقاق</th>
                                    <th class="text-center" style="color: var(--color-danger-text, #B91C1C);">أيام التأخر</th>
                                    <th class="text-end">المتبقي</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueInvoices->sortByDesc('days_overdue') as $inv)
                                <tr class="{{ $inv->days_overdue > 30 ? 'table-danger' : '' }}">
                                    <td class="fw-semibold small">{{ $inv->invoice_number }}</td>
                                    <td>
                                        <div class="small fw-semibold">{{ $inv->subscriber?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem">{{ $inv->subscriber?->subscription_number }}</div>
                                    </td>
                                    <td class="text-center small">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                                    <td class="text-center small" style="color: var(--color-danger-text, #B91C1C);">{{ $inv->due_date?->format('Y-m-d') }}</td>
                                    <td class="text-center fw-bold" style="color: var(--color-danger-text, #B91C1C);">{{ $inv->days_overdue }} يوم</td>
                                    <td class="text-end fw-bold" style="color: var(--color-danger-text, #B91C1C);">{{ number_format($inv->remaining, 2) }} ₪</td>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-outline-danger btn-sm py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class=" fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">الإجمالي المتأخر:</td>
                                    <td class="text-end" style="color: var(--color-danger-text, #B91C1C);">{{ number_format($overdueSummary['total'], 2) }} ₪</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- ===== تقرير 4: الأرصدة ===== --}}
        <div class="col-md-6">
            <x-admin.card class="h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-arrow-up-circle me-2" style="color: var(--color-danger, #EF4444); opacity: .75;"></i>
                        4أ. الأرصدة المدينة (مديون على المشترك)
                        <span class="badge bg-danger ms-2">{{ $debitBalances->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($debitBalances->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="bi bi-inbox me-1"></i>لا توجد بيانات</p>
                    @else
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class=" sticky-top">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-end">إجمالي الفوترة</th>
                                    <th class="text-end">إجمالي السداد</th>
                                    <th class="text-end" style="color: var(--color-danger-text, #B91C1C);">المديونية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($debitBalances as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $item['subscriber']?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem">{{ $item['subscriber']?->subscription_number }}</div>
                                    </td>
                                    <td class="text-end small">{{ number_format($item['total_billed'], 2) }} ₪</td>
                                    <td class="text-end small" style="color: var(--color-success-text, #047857);">{{ number_format($item['total_paid'], 2) }} ₪</td>
                                    <td class="text-end fw-bold" style="color: var(--color-danger-text, #B91C1C);">{{ number_format($item['balance'], 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class=" fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">إجمالي المديونيات:</td>
                                    <td class="text-end" style="color: var(--color-danger-text, #B91C1C);">{{ number_format($debitBalances->sum('balance'), 2) }} ₪</td>
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
                        <i class="bi bi-arrow-down-circle me-2" style="color: var(--color-success, #10B981); opacity: .75;"></i>
                        4ب. الأرصدة الدائنة (دائن لصالح المشترك)
                        <span class="badge bg-success ms-2">{{ $creditBalances->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($creditBalances->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="bi bi-inbox me-1"></i>لا توجد أرصدة دائنة</p>
                    @else
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class=" sticky-top">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-end">إجمالي الفوترة</th>
                                    <th class="text-end">إجمالي السداد</th>
                                    <th class="text-end" style="color: var(--color-success-text, #047857);">الرصيد الدائن</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($creditBalances as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $item['subscriber']?->subscriber_name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem">{{ $item['subscriber']?->subscription_number }}</div>
                                    </td>
                                    <td class="text-end small">{{ number_format($item['total_billed'], 2) }} ₪</td>
                                    <td class="text-end small" style="color: var(--color-success-text, #047857);">{{ number_format($item['total_paid'], 2) }} ₪</td>
                                    <td class="text-end fw-bold" style="color: var(--color-success-text, #047857);">{{ number_format(abs($item['balance']), 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class=" fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">إجمالي الأرصدة الدائنة:</td>
                                    <td class="text-end" style="color: var(--color-success-text, #047857);">{{ number_format(abs($creditBalances->sum('balance')), 2) }} ₪</td>
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
@endsection
