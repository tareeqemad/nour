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
        $overpayment = 0;

        try {
            DB::transaction(function () use ($request, $invoice, $amountPaid, &$overpayment) {
                // Lock the invoice to prevent concurrent payments
                $invoice = Invoice::lockForUpdate()->find($invoice->id);
                $remaining = $invoice->remainingAmount();

                // Validate payment doesn't grossly exceed remaining
                if ($amountPaid > $remaining * 2 && $remaining > 0) {
                    throw new \RuntimeException('مبلغ الدفعة يتجاوز الحد المسموح.');
                }

                // احسب الزيادة إن وُجدت
                $overpayment = max($amountPaid - $remaining, 0);

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

                // تحديث حالة الفاتورة (هذه الفاتورة تحديداً)
                $invoice->refresh();
                $invoice->updateStatusFromPayments();

                // توزيع دفعات المشترك FIFO على جميع فواتيره
                // (يُغلق الفواتير السابقة المغطاة بدفعات لاحقة)
                Invoice::reconcileSubscriber($invoice->subscriber_id);

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
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

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
    public function destroy(Request $request, Invoice $invoice, Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $request->validate([
            'delete_reason' => 'required|string|min:5|max:1000',
        ], [
            'delete_reason.required' => 'يجب ذكر سبب حذف الدفعة.',
            'delete_reason.min' => 'السبب يجب أن يكون 5 أحرف على الأقل.',
        ]);

        DB::transaction(function () use ($request, $invoice, $payment) {
            AuditLog::log(
                'delete',
                $payment,
                auth()->user(),
                $payment->toArray(),
                ['delete_reason' => $request->delete_reason],
                'حذف دفعة للفاتورة: ' . ($invoice->invoice_number ?? '#' . $invoice->id) . ' - السبب: ' . $request->delete_reason
            );

            // Create reverse financial entry for the full payment amount
            \App\Models\SubscriberAccountEntry::create([
                'subscriber_id' => $payment->subscriber_id,
                'invoice_id' => $payment->invoice_id,
                'entry_type' => \App\Models\SubscriberAccountEntry::TYPE_REVERSE,
                'amount' => -$payment->amount_paid,
                'description' => "حذف دفعة بقيمة " . number_format($payment->amount_paid, 2) . " - إيصال رقم: {$payment->receipt_number} - السبب: {$request->delete_reason}",
                'created_by' => auth()->id(),
            ]);

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

            // إعادة توزيع FIFO على فواتير المشترك بعد حذف الدفعة
            Invoice::reconcileSubscriber($invoice->subscriber_id);

            // إعادة احتساب الرصيد السابق لكل مسودة لاحقة
            Invoice::refreshDraftsForSubscriber($invoice->subscriber_id);
        });

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'تم حذف الدفعة وإعادة حالة الفاتورة.');
    }

    /**
     * Print payment receipt
     */
    public function receipt(Invoice $invoice, Payment $payment)
    {
        $payment->load(['invoice.subscriber', 'creator']);
        $siteName = \App\Models\Setting::get('site_name', 'نور');
        $logoUrl = asset(\App\Models\Setting::get('site_logo', 'assets/admin/images/brand-logos/nour_logo.png'));

        return view('admin.invoices.payment-receipt', compact('payment', 'invoice', 'siteName', 'logoUrl'));
    }
}
