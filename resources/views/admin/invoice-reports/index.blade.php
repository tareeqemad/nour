@extends('layouts.admin')

@section('title', 'تقارير الفوترة والتحصيل')
@php $breadcrumbTitle = 'تقارير الفوترة'; @endphp

@section('content')
<div class="general-page">

    {{-- ===== فلاتر ===== --}}
    <div class="general-card mb-3">
        <div class="general-card-header">
            <h5 class="general-title mb-0">
                <i class="bi bi-bar-chart-line me-2"></i>تقارير الفوترة والتحصيل
            </h5>
        </div>
        <div class="card-body pb-3">
            <form method="GET" action="{{ route('admin.invoice-reports.index') }}" class="row g-3 align-items-end">
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
                        <option value="0">— جميع المشغلين —</option>
                        @foreach($operators as $op)
                            <option value="{{ $op->id }}" {{ $selectedOpId == $op->id ? 'selected' : '' }}>
                                {{ $op->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>عرض التقارير
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('admin.invoice-reports.index') }}" class="btn btn-outline-secondary w-100" title="إعادة تعيين">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== بطاقات الملخص ===== --}}
    <div class="row g-3 mb-3">
        {{-- 1. الصادرة في الفترة --}}
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-right:4px solid #3b82f6 !important;">
                <div class="card-body text-center py-3">
                    <i class="bi bi-receipt text-primary fs-3"></i>
                    <div class="fs-4 fw-bold mt-1">{{ number_format($issuedInPeriod->count ?? 0) }}</div>
                    <div class="text-muted small">الفواتير الصادرة</div>
                    <div class="fw-semibold text-primary small mt-1">
                        {{ number_format($issuedInPeriod->sum_invoice ?? 0, 2) }} ₪
                    </div>
                </div>
            </div>
        </div>
        {{-- 2. غير المسددة --}}
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-right:4px solid #f59e0b !important;">
                <div class="card-body text-center py-3">
                    <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    <div class="fs-4 fw-bold mt-1">{{ number_format($unpaidSummary['count']) }}</div>
                    <div class="text-muted small">الفواتير غير المسددة</div>
                    <div class="fw-semibold text-warning small mt-1">
                        {{ number_format($unpaidSummary['total'], 2) }} ₪
                    </div>
                </div>
            </div>
        </div>
        {{-- 3. المتأخرة --}}
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-right:4px solid #ef4444 !important;">
                <div class="card-body text-center py-3">
                    <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                    <div class="fs-4 fw-bold mt-1">{{ number_format($overdueSummary['count']) }}</div>
                    <div class="text-muted small">الفواتير المتأخرة</div>
                    <div class="fw-semibold text-danger small mt-1">
                        {{ number_format($overdueSummary['total'], 2) }} ₪
                    </div>
                </div>
            </div>
        </div>
        {{-- 7. إجمالي الاستهلاك --}}
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-right:4px solid #10b981 !important;">
                <div class="card-body text-center py-3">
                    <i class="bi bi-lightning-charge text-success fs-3"></i>
                    <div class="fs-4 fw-bold mt-1">
                        {{ number_format($consumptionPeriod->total_kwh ?? 0, 1) }}
                    </div>
                    <div class="text-muted small">الاستهلاك (kWh)</div>
                    <div class="text-muted small mt-1">
                        متوسط {{ number_format($consumptionPeriod->avg_kwh ?? 0, 1) }} kWh/فاتورة
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ===== تقرير 1: الفواتير الصادرة في الفترة ===== --}}
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-receipt me-2 text-primary"></i>
                        1. الفواتير الصادرة خلال الفترة
                        <span class="badge bg-primary ms-2">{{ $dateFrom }}</span>
                        <span class="text-muted small fw-normal mx-1">←</span>
                        <span class="badge bg-primary">{{ $dateTo }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-md-3 col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-5 fw-bold text-primary">{{ number_format($issuedInPeriod->count ?? 0) }}</div>
                                <div class="text-muted small">عدد الفواتير</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-5 fw-bold text-primary">{{ number_format($issuedInPeriod->sum_invoice ?? 0, 2) }} ₪</div>
                                <div class="text-muted small">إجمالي قيمة الفواتير</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-5 fw-bold text-primary">{{ number_format($issuedInPeriod->sum_total ?? 0, 2) }} ₪</div>
                                <div class="text-muted small">إجمالي المبالغ المطلوبة</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-5 fw-bold text-success">{{ number_format($issuedInPeriod->sum_kwh ?? 0, 1) }} kWh</div>
                                <div class="text-muted small">إجمالي الاستهلاك</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== تقرير 6: المشتركون المفوترون ===== --}}
        <div class="col-md-6">
            <div class="general-card h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-people me-2 text-info"></i>
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
                            <thead class="table-light sticky-top">
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
                                    <td class="text-end fw-semibold text-primary">{{ number_format($item['total_billed'], 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>الإجمالي</td>
                                    <td class="text-center">{{ $billedSubscribers->sum('invoice_count') }}</td>
                                    <td class="text-end text-primary">{{ number_format($billedSubscribers->sum('total_billed'), 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== تقرير 5: الخصومات ===== --}}
        <div class="col-md-6">
            <div class="general-card h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-percent me-2 text-success"></i>
                        5. الخصومات الممنوحة في الفترة
                        <span class="badge bg-success ms-2">{{ $discountSummary['count'] }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-5 fw-bold text-success">{{ $discountSummary['count'] }}</div>
                                <div class="text-muted small">عدد الفواتير المخصومة</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-3">
                                <div class="fs-5 fw-bold text-success">{{ number_format($discountSummary['total_amount'], 2) }} ₪</div>
                                <div class="text-muted small">إجمالي مبالغ الخصومات</div>
                            </div>
                        </div>
                    </div>
                    @if($discounts->isNotEmpty())
                    <div class="table-responsive" style="max-height:220px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light sticky-top">
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
                                    <td class="text-end fw-semibold text-success">{{ number_format($inv->discount_amount, 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== تقرير 2: الفواتير غير المسددة ===== --}}
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h6 class="general-title mb-0">
                            <i class="bi bi-hourglass-split me-2 text-warning"></i>
                            2. الفواتير غير المسددة
                        </h6>
                    </div>
                    <span class="badge bg-warning text-dark fs-6">
                        {{ $unpaidSummary['count'] }} فاتورة — {{ number_format($unpaidSummary['total'], 2) }} ₪
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($unpaidInvoices->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="bi bi-check-circle text-success me-1"></i>لا توجد فواتير غير مسددة</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
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
                                    <td class="text-end text-success">{{ number_format($inv->payments_sum_amount_paid ?? 0, 2) }} ₪</td>
                                    <td class="text-end fw-bold text-warning">{{ number_format($inv->remaining, 2) }} ₪</td>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="6" class="text-end">الإجمالي المتبقي:</td>
                                    <td class="text-end text-warning">{{ number_format($unpaidSummary['total'], 2) }} ₪</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== تقرير 3: الفواتير المتأخرة ===== --}}
        <div class="col-12">
            <div class="general-card">
                <div class="general-card-header">
                    <div>
                        <h6 class="general-title mb-0">
                            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                            3. الفواتير المتأخرة
                        </h6>
                    </div>
                    <span class="badge bg-danger fs-6">
                        {{ $overdueSummary['count'] }} فاتورة — {{ number_format($overdueSummary['total'], 2) }} ₪
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($overdueInvoices->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="bi bi-check-circle text-success me-1"></i>لا توجد فواتير متأخرة</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>المشترك</th>
                                    <th class="text-center">تاريخ الفاتورة</th>
                                    <th class="text-center">تاريخ الاستحقاق</th>
                                    <th class="text-center text-danger">أيام التأخر</th>
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
                                    <td class="text-center small text-danger">{{ $inv->due_date?->format('Y-m-d') }}</td>
                                    <td class="text-center fw-bold text-danger">{{ $inv->days_overdue }} يوم</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($inv->remaining, 2) }} ₪</td>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-outline-danger btn-sm py-0 px-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">الإجمالي المتأخر:</td>
                                    <td class="text-end text-danger">{{ number_format($overdueSummary['total'], 2) }} ₪</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== تقرير 4: الأرصدة ===== --}}
        <div class="col-md-6">
            <div class="general-card h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-arrow-up-circle me-2 text-danger"></i>
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
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-end">إجمالي الفوترة</th>
                                    <th class="text-end">إجمالي السداد</th>
                                    <th class="text-end text-danger">المديونية</th>
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
                                    <td class="text-end small text-success">{{ number_format($item['total_paid'], 2) }} ₪</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($item['balance'], 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">إجمالي المديونيات:</td>
                                    <td class="text-end text-danger">{{ number_format($debitBalances->sum('balance'), 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="general-card h-100">
                <div class="general-card-header">
                    <h6 class="general-title mb-0">
                        <i class="bi bi-arrow-down-circle me-2 text-success"></i>
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
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>المشترك</th>
                                    <th class="text-end">إجمالي الفوترة</th>
                                    <th class="text-end">إجمالي السداد</th>
                                    <th class="text-end text-success">الرصيد الدائن</th>
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
                                    <td class="text-end small text-success">{{ number_format($item['total_paid'], 2) }} ₪</td>
                                    <td class="text-end fw-bold text-success">{{ number_format(abs($item['balance']), 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">إجمالي الأرصدة الدائنة:</td>
                                    <td class="text-end text-success">{{ number_format(abs($creditBalances->sum('balance')), 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end row --}}
</div>
@endsection
