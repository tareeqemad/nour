<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

/**
 * إعادة توزيع دفعات كل مشترك على فواتيره بترتيب FIFO (الأقدم أولاً).
 *
 * المنطق المحاسبي: إجمالي مدفوعات المشترك يغطي فواتيره بالتسلسل الزمني،
 * فلو دفع مبلغاً متراكماً على فاتورة لاحقة فإن السابقة تُغلق محاسبياً.
 *
 * يُحدّث invoice_status لكل فاتورة → PAID/PARTIAL/ISSUED حسب التغطية.
 * المسودات والملغاة لا تُمسّ.
 *
 * الاستخدام:
 *   php artisan invoices:reconcile               # كل المشتركين
 *   php artisan invoices:reconcile --subscriber=123  # مشترك واحد
 *   php artisan invoices:reconcile --dry-run     # تقرير فقط دون تحديث
 */
class ReconcileInvoices extends Command
{
    protected $signature = 'invoices:reconcile
                            {--subscriber= : معرّف المشترك (اختياري — افتراضياً الكل)}
                            {--dry-run : عرض الفرق دون كتابة شيء}';

    protected $description = 'توزيع دفعات المشتركين على فواتيرهم FIFO وتحديث حالة كل فاتورة';

    public function handle(): int
    {
        $subscriberId = $this->option('subscriber');
        $dryRun       = (bool) $this->option('dry-run');

        $query = Invoice::whereNotIn('invoice_status', [
            Invoice::STATUS_DRAFT,
            Invoice::STATUS_CANCELLED,
        ]);

        if ($subscriberId) {
            $query->where('subscriber_id', $subscriberId);
        }

        $subscriberIds = $query->distinct()->pluck('subscriber_id');

        if ($subscriberIds->isEmpty()) {
            $this->warn('لا يوجد مشتركون لإعادة تسويتهم.');
            return self::SUCCESS;
        }

        $this->info('عدد المشتركين: ' . $subscriberIds->count() . ($dryRun ? '  (وضع المعاينة)' : ''));
        $bar = $this->output->createProgressBar($subscriberIds->count());
        $bar->start();

        $totals = [
            'subscribers_touched' => 0,
            'invoices_updated'    => 0,
            'now_paid'            => 0,
            'now_partial'         => 0,
            'now_issued'          => 0,
        ];

        foreach ($subscriberIds as $sid) {
            if ($dryRun) {
                $preview = $this->previewSubscriber((int) $sid);
                if ($preview['updated'] > 0) {
                    $totals['subscribers_touched']++;
                }
                $totals['invoices_updated'] += $preview['updated'];
                $totals['now_paid']         += $preview['paid'];
                $totals['now_partial']      += $preview['partial'];
                $totals['now_issued']       += $preview['issued'];
            } else {
                $stats = Invoice::reconcileSubscriber((int) $sid);
                if ($stats['updated'] > 0) {
                    $totals['subscribers_touched']++;
                }
                $totals['invoices_updated'] += $stats['updated'];
                $totals['now_paid']         += $stats['paid'];
                $totals['now_partial']      += $stats['partial'];
                $totals['now_issued']       += $stats['issued'];
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['البيان', 'القيمة'],
            [
                ['عدد المشتركين الذين تغيّرت حالة فواتيرهم', $totals['subscribers_touched']],
                ['عدد الفواتير التي تغيّرت حالتها',          $totals['invoices_updated']],
                ['إجمالي الفواتير بحالة مسددة (بعد)',        $totals['now_paid']],
                ['إجمالي الفواتير بحالة مسددة جزئياً (بعد)', $totals['now_partial']],
                ['إجمالي الفواتير الصادرة وغير المسددة (بعد)', $totals['now_issued']],
            ]
        );

        if ($dryRun) {
            $this->warn('وضع المعاينة فقط — لم تُحفظ أي تغييرات. أعد التشغيل بدون --dry-run للتنفيذ.');
        } else {
            $this->info('اكتملت عملية إعادة التسوية بنجاح.');
        }

        return self::SUCCESS;
    }

    /**
     * نسخة معاينة من reconcileSubscriber لا تحفظ أي تغييرات.
     */
    private function previewSubscriber(int $subscriberId): array
    {
        $invoices = Invoice::where('subscriber_id', $subscriberId)
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $stats = ['updated' => 0, 'paid' => 0, 'partial' => 0, 'issued' => 0];

        if ($invoices->isEmpty()) {
            return $stats;
        }

        $invoiceIds = $invoices->pluck('id')->all();
        $pool = (float) \App\Models\Payment::whereIn('invoice_id', $invoiceIds)->sum('amount_paid');

        foreach ($invoices as $invoice) {
            $charge = (float) $invoice->invoice_amount;

            if ($charge <= 0) {
                $newStatus = Invoice::STATUS_PAID;
            } elseif ($pool >= $charge) {
                $newStatus = Invoice::STATUS_PAID;
                $pool -= $charge;
            } elseif ($pool > 0) {
                $newStatus = Invoice::STATUS_PARTIAL;
                $pool = 0;
            } else {
                $newStatus = Invoice::STATUS_ISSUED;
            }

            $stats[match ($newStatus) {
                Invoice::STATUS_PAID    => 'paid',
                Invoice::STATUS_PARTIAL => 'partial',
                Invoice::STATUS_ISSUED  => 'issued',
            }]++;

            if ((int) $invoice->invoice_status !== $newStatus) {
                $stats['updated']++;
            }
        }

        return $stats;
    }
}
