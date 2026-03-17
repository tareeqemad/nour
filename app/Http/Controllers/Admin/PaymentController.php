<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * قائمة دفعات فاتورة معينة
     */
    public function index(Request $request, Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $payments = $invoice->payments()
            ->with('creator')
            ->latest('payment_date')
            ->paginate(20);

        return view('admin.invoices.payments.index', compact('invoice', 'payments'));
    }

    /**
     * نموذج تسجيل دفعة جديدة
     */
    public function create(Invoice $invoice): View
    {
        $this->authorize('createForInvoice', [Payment::class, $invoice]);

        $methodLabels    = Payment::methodLabels();
        $remainingAmount = $invoice->remainingAmount();

        return view('admin.invoices.payments.create', compact('invoice', 'methodLabels', 'remainingAmount'));
    }

    /**
     * حفظ دفعة جديدة
     *
     * منطق السداد:
     *  - المبلغ المسدد يُوزَّع أولاً على هذه الفاتورة
     *  - إذا كان المبلغ يتجاوز المتبقي → القيمة الزائدة تُسجَّل as "overpayment"
     *    وتُخصم من previous_balance للفاتورة القادمة (رصيد دائن سالب)
     *  - تُحدَّث حالة الفاتورة: PARTIAL إن سُدد بعض، PAID إن اكتمل أو زاد
     */
    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('createForInvoice', [Payment::class, $invoice]);

        // تحقق إضافي: الفاتورة يجب أن تكون مُصدَرة أو مسددة جزئياً
        if (!in_array($invoice->invoice_status, [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL])) {
            return back()->withErrors(['invoice' => 'لا يمكن تسجيل دفعة على فاتورة غير مُصدَرة.']);
        }

        $amountPaid = (float) $request->amount_paid;
        $remaining  = $invoice->remainingAmount();

        // احسب الزيادة إن وُجدت
        $overpayment = max($amountPaid - $remaining, 0);

        DB::transaction(function () use ($request, $invoice, $amountPaid, $overpayment) {
            $payment = Payment::create([
                'invoice_id'     => $invoice->id,
                'subscriber_id'  => $invoice->subscriber_id,
                'payment_date'   => $request->payment_date,
                'amount_paid'    => $amountPaid,
                'credit_applied' => 0,
                'overpayment'    => round($overpayment, 2),
                'payment_method' => $request->payment_method,
                'receipt_number' => $request->receipt_number,
                'notes'          => $request->notes,
                'created_by'     => auth()->id(),
            ]);

            // تحديث حالة الفاتورة
            $invoice->refresh();
            $invoice->updateStatusFromPayments();

            // إعادة احتساب الرصيد السابق لكل مسودة لاحقة لنفس المشترك
            Invoice::refreshDraftsForSubscriber($invoice->subscriber_id);

            AuditLog::log(
                'create',
                $payment,
                auth()->user(),
                [],
                $payment->toArray(),
                'تسجيل دفعة للفاتورة: ' . ($invoice->invoice_number ?? '#' . $invoice->id)
                    . ' - المبلغ: ' . number_format($amountPaid, 2)
            );
        });

        $msg = 'تم تسجيل الدفعة بنجاح.';
        if ($overpayment > 0) {
            $msg .= ' المبلغ الزائد (' . number_format($overpayment, 2) . ') سيُضاف كرصيد دائن للمشترك في الفاتورة القادمة.';
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', $msg);
    }

    /**
     * عرض تفاصيل دفعة
     */
    public function show(Invoice $invoice, Payment $payment): View
    {
        $this->authorize('view', $payment);

        return view('admin.invoices.payments.show', compact('invoice', 'payment'));
    }

    /**
     * حذف دفعة (Admin فقط) - مع إعادة حالة الفاتورة
     */
    public function destroy(Invoice $invoice, Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        DB::transaction(function () use ($invoice, $payment) {
            AuditLog::log(
                'delete',
                $payment,
                auth()->user(),
                $payment->toArray(),
                [],
                'حذف دفعة للفاتورة: ' . ($invoice->invoice_number ?? '#' . $invoice->id)
            );

            $payment->delete();

            // إعادة احتساب حالة الفاتورة بعد الحذف
            $invoice->refresh();
            $paid = $invoice->paidAmount();

            if ($paid <= 0) {
                $invoice->invoice_status = Invoice::STATUS_ISSUED;
            } elseif ($paid < (float) $invoice->total_amount) {
                $invoice->invoice_status = Invoice::STATUS_PARTIAL;
            }
            $invoice->last_updated_by = auth()->id();
            $invoice->save();

            // إعادة احتساب الرصيد السابق لكل مسودة لاحقة
            Invoice::refreshDraftsForSubscriber($invoice->subscriber_id);
        });

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'تم حذف الدفعة وإعادة حالة الفاتورة.');
    }
}
