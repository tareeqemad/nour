<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Operator;
use App\Models\Payment;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberAccountController extends Controller
{
    /**
     * شاشة البحث عن مشترك لعرض حسابه.
     */
    public function index(Request $request): View|JsonResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isEnergyAuthority() && !auth()->user()->hasPermission('subscriber_accounts.view')) {
            abort(403);
        }

        $user  = auth()->user();
        $query = Subscriber::query();

        // تحديد نطاق المشغل
        $currentOperator = null;
        if ($user->isCompanyOwner()) {
            $currentOperator = $user->ownedOperators()->first();
            if ($currentOperator) {
                $query->whereHas('generationUnits', fn($q) =>
                    $q->where('operator_id', $currentOperator->id));
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->isNotEmpty()) {
                $ids = $operators->pluck('id')->toArray();
                $query->whereHas('generationUnits', fn($q) =>
                    $q->whereIn('operator_id', $ids));
                $currentOperator = $operators->first();
            }
        }

        $canSelectOperator = $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
        $operators = $canSelectOperator
            ? Operator::orderBy('name')->get()
            : collect();

        if ($canSelectOperator && ($oid = (int) $request->input('operator_id', 0)) > 0) {
            $query->whereHas('generationUnits', fn($q) => $q->where('operator_id', $oid));
        }

        // فلتر البحث النصي
        $search = trim($request->input('search', ''));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subscription_number', 'like', "%{$search}%")
                  ->orWhere('subscriber_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('meter_number', 'like', "%{$search}%");
            });
        }

        $subscribers = $query->orderBy('subscriber_name')->paginate(20)->withQueryString();

        // رد AJAX
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.subscriber-account.partials.list', compact('subscribers', 'search'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        return view('admin.subscriber-account.index', compact(
            'subscribers', 'operators', 'currentOperator', 'canSelectOperator', 'search'
        ));
    }

    /**
     * حساب مشترك محدد: كشف حساب تفصيلي.
     */
    public function show(Request $request, Subscriber $subscriber): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isEnergyAuthority() && !auth()->user()->hasPermission('subscriber_accounts.view')) {
            abort(403);
        }

        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');

        // ===== الإحصائيات العامة (بدون فلتر تاريخ) =====

        // جميع الفواتير غير الملغاة وغير المسودة
        $allActiveInvoices = Invoice::where('subscriber_id', $subscriber->id)
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->withSum('payments', 'amount_paid')
            ->get();

        // إجمالي الفواتير = مجموع invoice_amount (القيم الفعلية بدون ترحيل الأرصدة)
        // استخدام invoice_amount بدلاً من total_amount لتفادي التكرار التراكمي
        $totalInvoiced    = $allActiveInvoices->sum('invoice_amount');
        $totalPaid        = (float) $allActiveInvoices->sum('payments_sum_amount_paid');
        $currentBalance   = (float) $totalInvoiced - $totalPaid; // موجب = مدين، سالب = دائن

        $unpaidInvoices   = $allActiveInvoices->whereIn('invoice_status', [
            Invoice::STATUS_ISSUED,
            Invoice::STATUS_PARTIAL,
        ]);
        $unpaidCount      = $unpaidInvoices->count();
        // المتبقي الفعلي = الرصيد الحالي الصافي (يأخذ بالحسبان الأرصدة المرحّلة والدفعات الزائدة)
        $unpaidTotal      = max($currentBalance, 0);

        $lastInvoice = Invoice::where('subscriber_id', $subscriber->id)
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->latest('invoice_date')
            ->first();

        $lastPayment = Payment::where('subscriber_id', $subscriber->id)
            ->latest('payment_date')
            ->first();

        // ===== سجل الفواتير (مع فلتر التاريخ إن وُجد) =====
        $invoicesQuery = Invoice::where('subscriber_id', $subscriber->id)
            ->with(['payments', 'meterReading'])
            ->withSum('payments', 'amount_paid');

        if ($dateFrom) {
            $invoicesQuery->whereDate('invoice_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $invoicesQuery->whereDate('invoice_date', '<=', $dateTo);
        }

        $invoices = $invoicesQuery->orderBy('invoice_date', 'desc')->get();

        // ===== سجل الدفعات (مع فلتر التاريخ إن وُجد) =====
        $paymentsQuery = Payment::where('subscriber_id', $subscriber->id)
            ->with('invoice');

        if ($dateFrom) {
            $paymentsQuery->whereDate('payment_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $paymentsQuery->whereDate('payment_date', '<=', $dateTo);
        }

        $payments = $paymentsQuery->orderBy('payment_date', 'desc')->get();

        // ===== إجماليات الكشف المفلتر =====
        $filteredInvoiced = $invoices
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->sum('invoice_amount');

        $filteredPaid = $payments->sum('amount_paid');

        return view('admin.subscriber-account.show', compact(
            'subscriber',
            'totalInvoiced', 'totalPaid', 'currentBalance',
            'unpaidCount', 'unpaidTotal',
            'lastInvoice', 'lastPayment',
            'invoices', 'payments',
            'filteredInvoiced', 'filteredPaid',
            'dateFrom', 'dateTo'
        ));
    }
}
