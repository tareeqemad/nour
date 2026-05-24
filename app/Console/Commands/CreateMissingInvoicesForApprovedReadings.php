<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\MeterReading;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * إنشاء فواتير مسودة للقراءات المعتمدة التي لا تملك فاتورة فعّالة.
 *
 * السبب: عند إلغاء فاتورة مُصدَرة، الفاتورة تبقى في الجدول بحالة "ملغاة"
 * ومرتبطة بـ meter_reading_id. هذا كان يمنع - قبل الإصلاح - إنشاء فاتورة
 * جديدة عند إعادة اعتماد القراءة، فتعلق القراءة "معتمدة بدون فاتورة".
 *
 * هذا الأمر يكتشف كل القراءات العالقة وينشئ لها فواتير مسودة.
 *
 * الاستخدام:
 *   php artisan invoices:reconcile-approved-readings              # تنفيذ
 *   php artisan invoices:reconcile-approved-readings --dry-run    # معاينة فقط
 *   php artisan invoices:reconcile-approved-readings --subscription=012030001
 */
class CreateMissingInvoicesForApprovedReadings extends Command
{
    protected $signature = 'invoices:reconcile-approved-readings
                            {--subscription= : رقم اشتراك محدد (اختياري)}
                            {--dry-run : عرض ما سيُنشأ دون كتابة شيء}';

    protected $description = 'إنشاء فواتير مسودة للقراءات المعتمدة التي علقت بدون فاتورة بعد إلغاء فاتورتها السابقة';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $subscriptionNumber = $this->option('subscription');

        // القراءات المعتمدة التي ليس لها فاتورة فعّالة (نتجاهل الملغاة والمحذوفة)
        $query = MeterReading::with(['subscriber'])
            ->where('action_status', MeterReading::ACTION_STATUS_APPROVED)
            ->whereDoesntHave('activeInvoice');

        if ($subscriptionNumber) {
            $query->whereHas('subscriber', fn($q) =>
                $q->where('subscription_number', $subscriptionNumber)
            );
        }

        $readings = $query->orderBy('reading_date')->get();

        if ($readings->isEmpty()) {
            $this->info('لا توجد قراءات معتمدة عالقة بدون فاتورة فعّالة.');
            return self::SUCCESS;
        }

        $this->info("عدد القراءات العالقة: {$readings->count()}" . ($dryRun ? '  (وضع المعاينة)' : ''));
        $this->newLine();

        $rows = [];
        foreach ($readings as $r) {
            $rows[] = [
                $r->reading_number,
                $r->subscriber?->subscription_number ?? '—',
                $r->subscriber?->subscriber_name ?? '—',
                $r->reading_date?->format('Y-m-d') ?? '—',
                number_format((float) $r->consumption_kwh, 2),
            ];
        }

        $this->table(
            ['رقم القراءة', 'رقم الاشتراك', 'اسم المشترك', 'التاريخ', 'الاستهلاك'],
            $rows
        );

        if ($dryRun) {
            $this->warn('وضع المعاينة فقط — لم تُنشأ أي فواتير. أعد التشغيل بدون --dry-run للتنفيذ.');
            return self::SUCCESS;
        }

        if (!$this->confirm('هل تريد إنشاء فواتير مسودة لهذه القراءات؟', true)) {
            $this->warn('تم الإلغاء.');
            return self::SUCCESS;
        }

        $createdBy = (int) (\App\Models\User::where('email', 'tareqelbawab94@gmail.com')->value('id')
            ?? \App\Models\User::first()?->id ?? 0);

        $created = 0;
        $errors  = [];

        $bar = $this->output->createProgressBar($readings->count());
        $bar->start();

        foreach ($readings as $reading) {
            try {
                DB::transaction(function () use ($reading, $createdBy) {
                    // قفل مزدوج التحقق لتجنب السباق
                    if ($reading->activeInvoice()->lockForUpdate()->exists()) {
                        return;
                    }
                    Invoice::createFromReading($reading, $createdBy);
                });
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "قراءة {$reading->reading_number}: " . $e->getMessage();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("تم إنشاء {$created} فاتورة مسودة بنجاح.");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('أخطاء (' . count($errors) . '):');
            foreach ($errors as $err) {
                $this->line('  - ' . $err);
            }
        }

        return self::SUCCESS;
    }
}
