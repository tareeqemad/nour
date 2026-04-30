<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewReports', Invoice::class);

        $user              = auth()->user();
        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();

        // ===== نطاق المشغل =====
        $scopedOperatorIds = [];   // فارغة = بدون تقييد (سوبر أدمن / أدمن / سلطة الطاقة)
        if (! $canSelectOperator) {
            $scopedOperatorIds = $user->getScopedOperatorIds();
            if (empty($scopedOperatorIds)) {
                $scopedOperatorIds = [-1]; // مستخدم غير مرتبط بأي مشغل: لا يرى شيء
            }
        }

        // ===== فلاتر الطلب =====
        $dateFrom      = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo        = $request->input('date_to',   now()->format('Y-m-d'));
        $selectedOpId  = $canSelectOperator ? (int) $request->input('operator_id', 0) : 0;

        // قائمة المشغلين للفلتر (للمستخدمين ذوي الصلاحية)
        $operators = $canSelectOperator
            ? Operator::orderBy('name')->get(['id', 'name'])
            : collect();

        // للمستخدم المقيّد بمشغل: نمرّر اسم المشغل للعرض في رأس التقرير
        $currentOperator = null;
        if (! $canSelectOperator && ! empty($scopedOperatorIds) && $scopedOperatorIds !== [-1]) {
            $currentOperator = Operator::find($scopedOperatorIds[0]);
        }

        // ===== closure لبناء الاستعلام الأساسي =====
        $base = function () use ($scopedOperatorIds, $selectedOpId) {
            $q = Invoice::query();
            if (!empty($scopedOperatorIds)) {
                $q->whereHas('subscriber.generationUnits', fn($sq) =>
                    $sq->whereIn('operator_id', $scopedOperatorIds));
            }
            if ($selectedOpId > 0) {
                $q->whereHas('subscriber.generationUnits', fn($sq) =>
                    $sq->where('operator_id', $selectedOpId));
            }
            return $q;
        };

        // ===== 1. الفواتير الصادرة خلال الفترة =====
        $issuedInPeriod = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->selectRaw('
                COUNT(*) as count,
                SUM(invoice_amount) as sum_invoice,
                SUM(total_amount)   as sum_total,
                SUM(consumption_kwh) as sum_kwh
            ')
            ->first();

        // ===== 2. الفواتير غير المسددة =====
        $unpaidInvoices = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL])
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->withSum('payments', 'amount_paid')
            ->orderByDesc('invoice_date')
            ->get()
            ->map(function ($inv) {
                $inv->remaining = max((float)$inv->total_amount - (float)($inv->payments_sum_amount_paid ?? 0), 0);
                return $inv;
            })
            ->filter(fn($inv) => $inv->remaining > 0);

        $unpaidSummary = [
            'count'  => $unpaidInvoices->count(),
            'total'  => $unpaidInvoices->sum('remaining'),
        ];

        // ===== 3. الفواتير المتأخرة =====
        $overdueInvoices = $base()
            ->where('invoice_status', Invoice::STATUS_ISSUED)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->withSum('payments', 'amount_paid')
            ->orderBy('due_date')
            ->get()
            ->map(function ($inv) {
                $inv->remaining = max((float)$inv->total_amount - (float)($inv->payments_sum_amount_paid ?? 0), 0);
                $inv->days_overdue = now()->diffInDays($inv->due_date);
                return $inv;
            });

        $overdueSummary = [
            'count' => $overdueInvoices->count(),
            'total' => $overdueInvoices->sum('remaining'),
        ];

        // ===== 4. الأرصدة الدائنة والمدينة =====
        // نحسب لكل مشترك: مجموع فواتيره - مجموع مدفوعاته
        $balances = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->withSum('payments', 'amount_paid')
            ->get()
            ->groupBy('subscriber_id')
            ->map(function ($invoices) {
                $totalBilled = $invoices->sum('invoice_amount');
                $totalPaid   = $invoices->sum('payments_sum_amount_paid');
                $balance     = (float)$totalBilled - (float)$totalPaid;
                return [
                    'subscriber'    => $invoices->first()->subscriber,
                    'total_billed'  => round((float)$totalBilled, 2),
                    'total_paid'    => round((float)$totalPaid, 2),
                    'balance'       => round($balance, 2),
                ];
            });

        $debitBalances  = $balances->filter(fn($b) => $b['balance'] > 0)->sortByDesc('balance');
        $creditBalances = $balances->filter(fn($b) => $b['balance'] < 0)->sortBy('balance');

        // ===== 5. الخصومات الممنوحة خلال الفترة =====
        $discounts = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->where('discount_rate', '>', 0)
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->get()
            ->map(function ($inv) {
                // مبلغ الخصم = ثمن الاستهلاك الكامل - ثمن الاستهلاك بعد الخصم
                $fullCost     = (float)$inv->consumption_kwh * (float)$inv->price_per_kwh;
                $discountedCost = $fullCost * (1 - ((float)$inv->discount_rate / 100));
                $inv->discount_amount = round($fullCost - $discountedCost, 2);
                return $inv;
            });

        $discountSummary = [
            'count'        => $discounts->count(),
            'total_amount' => $discounts->sum('discount_amount'),
        ];

        // ===== 6. عدد المشتركين المفوترين خلال الفترة =====
        $billedSubscribers = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->get()
            ->groupBy('subscriber_id')
            ->map(function ($invoices) use ($dateFrom, $dateTo) {
                return [
                    'subscriber'     => $invoices->first()->subscriber,
                    'invoice_count'  => $invoices->count(),
                    'total_billed'   => round($invoices->sum('invoice_amount'), 2),
                ];
            });

        // ===== 7. إجمالي الاستهلاك خلال الفترة =====
        $consumptionPeriod = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->selectRaw('SUM(consumption_kwh) as total_kwh, AVG(consumption_kwh) as avg_kwh, COUNT(*) as count')
            ->first();

        // === تقرير الإيرادات حسب المشغل ===
        $operatorsScopeQuery = \App\Models\Operator::select('operators.id', 'operators.name');
        if (! $canSelectOperator && ! empty($scopedOperatorIds)) {
            $operatorsScopeQuery->whereIn('id', $scopedOperatorIds);
        }
        if ($selectedOpId > 0) {
            $operatorsScopeQuery->where('id', $selectedOpId);
        }
        $revenueByOperator = $operatorsScopeQuery
            ->withCount(['generationUnits as subscriber_count' => function ($q) use ($base) {
                // Count distinct subscribers through generation units
            }])
            ->get()
            ->map(function ($operator) use ($dateFrom, $dateTo) {
                $invoices = \App\Models\Invoice::whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $operator->id))
                    ->whereNotIn('invoice_status', [\App\Models\Invoice::STATUS_DRAFT, \App\Models\Invoice::STATUS_CANCELLED])
                    ->whereBetween('invoice_date', [$dateFrom, $dateTo])
                    ->get();

                $totalBilled = $invoices->sum('invoice_amount');
                $invoiceIds = $invoices->pluck('id');
                $totalPaid = \App\Models\Payment::whereIn('invoice_id', $invoiceIds)->sum('amount_paid');

                $subscriberCount = \App\Models\Subscriber::whereHas('generationUnits', fn($q) => $q->where('operator_id', $operator->id))->count();

                return (object)[
                    'name' => $operator->name,
                    'subscriber_count' => $subscriberCount,
                    'invoice_count' => $invoices->count(),
                    'total_kwh' => round($invoices->sum('consumption_kwh'), 2),
                    'total_billed' => round($totalBilled, 2),
                    'total_paid' => round($totalPaid, 2),
                    'collection_rate' => $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 0,
                ];
            })
            ->filter(fn($op) => $op->invoice_count > 0 || $op->subscriber_count > 0)
            ->values();

        // === أكبر المتأخرين ===
        $topDelinquentQuery = \App\Models\Invoice::where('invoice_status', \App\Models\Invoice::STATUS_ISSUED)
            ->where('due_date', '<', now())
            ->with(['subscriber.generationUnits.operator']);

        if ($selectedOpId) {
            $topDelinquentQuery->whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $selectedOpId));
        } elseif (isset($scopedOperatorIds) && !empty($scopedOperatorIds)) {
            $topDelinquentQuery->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $scopedOperatorIds));
        }

        $topDelinquents = $topDelinquentQuery->get()
            ->groupBy('subscriber_id')
            ->map(function ($invoices) {
                $subscriber = $invoices->first()->subscriber;
                $totalRemaining = $invoices->sum(fn($inv) => max($inv->total_amount - $inv->paidAmount(), 0));
                $oldestInvoice = $invoices->sortBy('due_date')->first();
                $daysOverdue = $oldestInvoice->due_date ? (int) now()->diffInDays($oldestInvoice->due_date) : 0;
                $operatorName = $subscriber->generationUnits->first()?->operator?->name ?? '—';

                return (object)[
                    'subscriber_name' => $subscriber->subscriber_name,
                    'subscription_number' => $subscriber->subscription_number,
                    'operator_name' => $operatorName,
                    'overdue_count' => $invoices->count(),
                    'total_remaining' => round($totalRemaining, 2),
                    'oldest_invoice_date' => $oldestInvoice->invoice_date?->format('Y-m-d'),
                    'days_overdue' => $daysOverdue,
                ];
            })
            ->sortByDesc('total_remaining')
            ->take(20)
            ->values();

        // === تقرير الإيرادات الشهري (آخر 12 شهر) ===
        $monthlyRevenue = collect();
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->translatedFormat('M Y');

            $monthInvoicesQuery = \App\Models\Invoice::whereNotIn('invoice_status', [\App\Models\Invoice::STATUS_DRAFT, \App\Models\Invoice::STATUS_CANCELLED])
                ->whereBetween('invoice_date', [$monthStart, $monthEnd]);

            if ($selectedOpId) {
                $monthInvoicesQuery->whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $selectedOpId));
            } elseif (isset($scopedOperatorIds) && !empty($scopedOperatorIds)) {
                $monthInvoicesQuery->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $scopedOperatorIds));
            }

            $monthInvoices = $monthInvoicesQuery->get();
            $monthBilled = $monthInvoices->sum('invoice_amount');
            $monthInvoiceIds = $monthInvoices->pluck('id');
            $monthPaid = \App\Models\Payment::whereIn('invoice_id', $monthInvoiceIds)
                ->whereBetween('payment_date', [$monthStart, $monthEnd])
                ->sum('amount_paid');

            $monthlyRevenue->push((object)[
                'month' => $monthLabel,
                'invoice_count' => $monthInvoices->count(),
                'total_billed' => round($monthBilled, 2),
                'total_paid' => round($monthPaid, 2),
                'collection_rate' => $monthBilled > 0 ? round(($monthPaid / $monthBilled) * 100, 1) : 0,
                'balance' => round($monthBilled - $monthPaid, 2),
            ]);
        }

        return view('admin.invoice-reports.index', compact(
            'dateFrom', 'dateTo',
            'operators', 'selectedOpId', 'canSelectOperator', 'currentOperator',
            'issuedInPeriod',
            'unpaidInvoices', 'unpaidSummary',
            'overdueInvoices', 'overdueSummary',
            'debitBalances', 'creditBalances',
            'discounts', 'discountSummary',
            'billedSubscribers',
            'consumptionPeriod',
            'revenueByOperator', 'topDelinquents', 'monthlyRevenue'
        ));
    }
}
