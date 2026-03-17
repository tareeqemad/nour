<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

/**
 * أمر لمرة واحدة: إعادة حساب الرصيد السابق التراكمي لجميع الفواتير المُصدَرة.
 * يستخدم أسلوب الرصيد الجاري (Running Balance) لضمان أن كل فاتورة
 * تأخذ الرصيد الصحيح من الفواتير السابقة لها زمنياً فقط.
 */
class RecalcPreviousBalance extends Command
{
    protected $signature = 'invoices:recalc-balance';
    protected $description = 'إعادة حساب الرصيد السابق التراكمي لكل الفواتير غير المسودة وغير الملغاة';

    public function handle(): int
    {
        $this->info('بدء إعادة حساب الرصيد السابق (Running Balance)...');

        // جلب كل المشتركين الذين لديهم فواتير
        $subscriberIds = Invoice::whereNotIn('invoice_status', [
            Invoice::STATUS_DRAFT,
            Invoice::STATUS_CANCELLED,
        ])->distinct()->pluck('subscriber_id');

        $bar = $this->output->createProgressBar($subscriberIds->count());
        $updated = 0;

        foreach ($subscriberIds as $subscriberId) {
            // جلب فواتير المشترك مرتبة زمنياً (الأقدم أولاً)
            $invoices = Invoice::where('subscriber_id', $subscriberId)
                ->whereNotIn('invoice_status', [
                    Invoice::STATUS_DRAFT,
                    Invoice::STATUS_CANCELLED,
                ])
                ->orderBy('invoice_date')
                ->orderBy('id')
                ->get();

            // أسلوب الرصيد الجاري:
            //   runningBalance(N) = runningBalance(N-1) + invoice_amount(N-1) - payments(N-1)
            // الرصيد السابق لأول فاتورة = 0
            $runningBalance = 0.0;

            foreach ($invoices as $invoice) {
                $prevBalance = round($runningBalance, 2);

                // إعادة حساب المبلغ المطلوب
                $amounts = Invoice::calculateAmounts(
                    (float) $invoice->consumption_kwh,
                    (float) $invoice->price_per_kwh,
                    (float) $invoice->discount_rate,
                    (float) $invoice->minimum_charge,
                    $prevBalance
                );

                if (
                    round((float) $invoice->previous_balance, 2) !== $prevBalance ||
                    round((float) $invoice->total_amount, 2) !== round($amounts['total_amount'], 2)
                ) {
                    $old = [
                        'previous_balance' => $invoice->previous_balance,
                        'total_amount'     => $invoice->total_amount,
                    ];

                    $invoice->update([
                        'previous_balance' => $prevBalance,
                        'invoice_amount'   => $amounts['invoice_amount'],
                        'total_amount'     => $amounts['total_amount'],
                    ]);

                    $this->line('');
                    $this->line("  تحديث فاتورة {$invoice->invoice_number} (مشترك {$subscriberId}):");
                    $this->line("    الرصيد السابق: {$old['previous_balance']} → {$prevBalance}");
                    $this->line("    المبلغ المطلوب: {$old['total_amount']} → {$amounts['total_amount']}");
                    $updated++;
                }

                // تحديث الرصيد الجاري بعد هذه الفاتورة:
                //   += قيمة الفاتورة الحالية − الدفعات عليها
                $paidOnThis = (float) $invoice->payments()->sum('amount_paid');
                $runningBalance = $runningBalance + (float) $invoice->invoice_amount - $paidOnThis;
            }

            // تحديث المسودات أيضاً
            Invoice::refreshDraftsForSubscriber($subscriberId);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("تم تحديث {$updated} فاتورة بنجاح.");

        return self::SUCCESS;
    }
}
