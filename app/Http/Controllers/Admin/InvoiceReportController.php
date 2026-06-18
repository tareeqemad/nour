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
        $canSelectOperator = $user->hasGlobalAccountingAccess();

        // ===== نطاق المشغل =====
        $scopedOperatorIds = [];   // فارغة = بدون تقييد (سوبر أدمن / أدمن / سلطة الطاقة)
        if (! $canSelectOperator) {
            $scopedOperatorIds = $user->getScopedOperatorIds();
            if (empty($scopedOperatorIds)) {
                // مستخدم غير مرتبط بأي مشغل:
                // - يملك صلاحية التقارير (مراقب عام) → يرى تقارير كل المشغلين مع إمكانية الفلترة
                // - غير ذلك → لا يرى شيء
                if ($user->hasPermission('invoice_reports.view')) {
                    $canSelectOperator = true;
                    $scopedOperatorIds = [];
                } else {
                    $scopedOperatorIds = [-1];
                }
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

        // ╔══════════════════════════════════════════════════════════════════════╗
        // ║ FIFO-aware paid map: invoice_id => paid_amount (allocated via FIFO)  ║
        // ║                                                                       ║
        // ║ يحاكي منطق reconcileSubscriber: لكل مشترك، تُجمع كل دفعاته في pool،  ║
        // ║ ثم تُصرَف على فواتيره بترتيب التاريخ. هذا التوزيع الفعلي يختلف عن      ║
        // ║ Payment.invoice_id المسجل (الذي قد يشير لفاتورة لكن المبلغ يُصرَف     ║
        // ║ على فاتورة أقدم). كل حسابات "المسدد" و"المتبقي" أدناه تستخدم هذا الـ  ║
        // ║ map لضمان الدقة المحاسبية.                                            ║
        // ╚══════════════════════════════════════════════════════════════════════╝
        $paidByInvoice = $this->computeFifoPaidByInvoice($selectedOpId, $scopedOperatorIds);

        // Helper مختصر لقراءة المسدد من الـ map
        $paid = fn ($inv) => (float) ($paidByInvoice[$inv->id] ?? 0);

        // ===== 1. الفواتير الصادرة خلال الفترة =====
        // sum_invoice  = إجمالي قيمة الفواتير (الرسوم الجديدة فقط)
        // sum_paid     = ما سُدّد على هذه الفواتير (FIFO-aware)
        // sum_remaining = المتبقي من رسومها (sum_invoice − sum_paid)
        $issuedRaw = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->get();

        $sumInvoice = (float) $issuedRaw->sum('invoice_amount');
        $sumPaid    = (float) $issuedRaw->sum(fn ($inv) => $paid($inv));
        $issuedInPeriod = (object) [
            'count'         => $issuedRaw->count(),
            'sum_invoice'   => $sumInvoice,
            'sum_paid'      => $sumPaid,
            'sum_remaining' => max($sumInvoice - $sumPaid, 0),
            'sum_kwh'       => (float) $issuedRaw->sum('consumption_kwh'),
        ];

        // ===== الأرصدة الفعلية لكل مشترك (مرجع للحسابات أدناه) =====
        // محسوبة من الفواتير الصادرة ضمن فترة التقرير (يحترم فلتر التاريخ).
        // الصافي = invoice_amount − FIFO-allocated paid (الدقيق محاسبياً).
        $balances = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL, Invoice::STATUS_PAID])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->get()
            ->groupBy('subscriber_id')
            ->map(function ($invoices) use ($paid) {
                $totalBilled = (float) $invoices->sum('invoice_amount');
                $totalPaid   = (float) $invoices->sum(fn ($inv) => $paid($inv));
                $balance     = $totalBilled - $totalPaid;
                return [
                    'subscriber'    => $invoices->first()->subscriber,
                    'total_billed'  => round($totalBilled, 2),
                    'total_paid'    => round($totalPaid, 2),
                    'balance'       => round($balance, 2),
                ];
            });

        $debitBalances  = $balances->filter(fn($b) => $b['balance'] > 0)->sortByDesc('balance');
        $creditBalances = $balances->filter(fn($b) => $b['balance'] < 0)->sortBy('balance');

        // ===== 2. الفواتير غير المسددة (داخل فترة التقرير) =====
        // المتبقي لكل فاتورة = invoice_amount − FIFO paid (الفعلي).
        $unpaidInvoices = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->orderByDesc('invoice_date')
            ->get()
            ->map(function ($inv) use ($paid) {
                $p = $paid($inv);
                $inv->paid_amount = $p;
                $inv->remaining   = max((float) $inv->invoice_amount - $p, 0);
                return $inv;
            })
            ->filter(fn($inv) => $inv->remaining > 0)
            ->values();

        $unpaidSummary = [
            'count' => $unpaidInvoices->count(),
            'total' => round($unpaidInvoices->sum('remaining'), 2),
        ];

        // ===== 3. الفواتير المتأخرة (داخل فترة التقرير) =====
        // تعريف "متأخرة": صادرة أو مسددة جزئياً + due_date في الماضي + لها متبقٍ فعلي (FIFO).
        $overdueInvoices = $base()
            ->whereIn('invoice_status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with(['subscriber:id,subscriber_name,subscription_number'])
            ->orderBy('due_date')
            ->get()
            ->map(function ($inv) use ($paid) {
                $p = $paid($inv);
                $inv->paid_amount  = $p;
                $inv->remaining    = max((float) $inv->invoice_amount - $p, 0);
                $inv->days_overdue = (int) now()->diffInDays($inv->due_date);
                return $inv;
            })
            ->filter(fn($inv) => $inv->remaining > 0)
            ->values();

        $overdueSummary = [
            'count' => $overdueInvoices->count(),
            'total' => round($overdueInvoices->sum('remaining'), 2),
        ];

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

        // === تقرير الإيرادات حسب المشغل (FIFO-aware) ===
        $operatorsScopeQuery = \App\Models\Operator::select('operators.id', 'operators.name');
        if (! $canSelectOperator && ! empty($scopedOperatorIds)) {
            $operatorsScopeQuery->whereIn('id', $scopedOperatorIds);
        }
        if ($selectedOpId > 0) {
            $operatorsScopeQuery->where('id', $selectedOpId);
        }
        $revenueByOperator = $operatorsScopeQuery
            ->get()
            ->map(function ($operator) use ($dateFrom, $dateTo, $paidByInvoice) {
                $invoices = \App\Models\Invoice::whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $operator->id))
                    ->whereNotIn('invoice_status', [\App\Models\Invoice::STATUS_DRAFT, \App\Models\Invoice::STATUS_CANCELLED])
                    ->whereBetween('invoice_date', [$dateFrom, $dateTo])
                    ->get();

                $totalBilled = (float) $invoices->sum('invoice_amount');
                $totalPaid   = (float) $invoices->sum(fn ($inv) => $paidByInvoice[$inv->id] ?? 0);

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

        // === أكبر المتأخرين (داخل فترة التقرير) — FIFO-aware ===
        // المتبقي لكل مشترك = مجموع متبقي فواتيره المتأخرة وفق التوزيع الفعلي.
        $topDelinquentQuery = \App\Models\Invoice::whereIn('invoice_status', [\App\Models\Invoice::STATUS_ISSUED, \App\Models\Invoice::STATUS_PARTIAL])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->with(['subscriber.generationUnits.operator']);

        if ($selectedOpId) {
            $topDelinquentQuery->whereHas('subscriber.generationUnits', fn($q) => $q->where('operator_id', $selectedOpId));
        } elseif (isset($scopedOperatorIds) && !empty($scopedOperatorIds)) {
            $topDelinquentQuery->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $scopedOperatorIds));
        }

        $topDelinquents = $topDelinquentQuery->get()
            ->groupBy('subscriber_id')
            ->map(function ($invoices) use ($paidByInvoice) {
                $subscriber = $invoices->first()->subscriber;
                $totalRemaining = $invoices->sum(fn($inv) => max(
                    (float) $inv->invoice_amount - (float) ($paidByInvoice[$inv->id] ?? 0),
                    0
                ));
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
            ->filter(fn($d) => $d->total_remaining > 0)
            ->sortByDesc('total_remaining')
            ->take(20)
            ->values();

        // === تقرير الإيرادات الشهري (آخر 12 شهر) — FIFO-aware ===
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
            $monthPaid = $monthInvoices->sum(fn($inv) => $paidByInvoice[$inv->id] ?? 0);

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

    /**
     * Compute the FIFO-allocated paid amount per invoice for all scoped subscribers.
     *
     * Mirrors Invoice::reconcileSubscriber() logic: for each subscriber, sum their
     * payment pool and drain it across invoices in date order. Returns a map of
     * [invoice_id => allocated_paid_amount] usable as a substitute for the
     * (inaccurate) sum of Payment.amount_paid grouped by invoice_id.
     *
     * @return array<int, float>
     */
    private function computeFifoPaidByInvoice(int $selectedOpId, array $scopedOperatorIds): array
    {
        $subscriberQuery = \App\Models\Subscriber::query();
        if ($selectedOpId > 0) {
            $subscriberQuery->whereHas('generationUnits', fn($q) => $q->where('operator_id', $selectedOpId));
        } elseif (!empty($scopedOperatorIds)) {
            $subscriberQuery->whereHas('generationUnits', fn($q) => $q->whereIn('operator_id', $scopedOperatorIds));
        }

        $subscriberIds = $subscriberQuery->pluck('id');
        if ($subscriberIds->isEmpty()) {
            return [];
        }

        $allInvoices = \App\Models\Invoice::whereIn('subscriber_id', $subscriberIds)
            ->whereNotIn('invoice_status', [\App\Models\Invoice::STATUS_DRAFT, \App\Models\Invoice::STATUS_CANCELLED])
            ->orderBy('subscriber_id')->orderBy('invoice_date')->orderBy('id')
            ->get(['id', 'subscriber_id', 'invoice_amount']);

        $payments = \App\Models\Payment::whereIn('invoice_id', $allInvoices->pluck('id'))
            ->select('invoice_id', 'amount_paid')
            ->get();

        // Build subscriber payment pools from invoice_id -> subscriber_id mapping
        $invoiceToSubscriber = $allInvoices->pluck('subscriber_id', 'id');
        $pools = [];
        foreach ($payments as $p) {
            $subId = $invoiceToSubscriber[$p->invoice_id] ?? null;
            if ($subId === null) {
                continue;
            }
            $pools[$subId] = ($pools[$subId] ?? 0) + (float) $p->amount_paid;
        }

        $paidByInvoice = [];
        foreach ($allInvoices->groupBy('subscriber_id') as $subId => $invoices) {
            $pool = (float) ($pools[$subId] ?? 0);
            foreach ($invoices as $inv) {
                $charge = (float) $inv->invoice_amount;
                $allocated = $charge > 0 ? min($pool, $charge) : 0;
                $pool -= $allocated;
                $paidByInvoice[$inv->id] = $allocated;
            }
        }

        return $paidByInvoice;
    }
}
