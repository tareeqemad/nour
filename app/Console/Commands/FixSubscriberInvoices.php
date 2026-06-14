<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * إصلاح محاسبي شامل لمشترك واحد بعد حذف/تعديل دفعات.
 *
 * الخطوات:
 *   1) إعادة تسوية FIFO (حالة الفواتير)
 *   2) إعادة حساب الرصيد السابق التراكمي (Running Balance)
 *   3) تحديث المسودات
 *   4) إعادة تسوية FIFO نهائية
 *   5) (اختياري) فرض فواتير محددة كـ "متأخرة" عبر --set-issued
 *      (تُخزَّن ISSUED وتظهر "متأخرة" تلقائياً لأن تاريخ استحقاقها مضى)
 *
 * الاستخدام:
 *   php artisan invoices:fix-subscriber 024010004 --dry-run
 *   php artisan invoices:fix-subscriber 024010004
 *   php artisan invoices:fix-subscriber --subscriber=123 --dry-run
 *   php artisan invoices:fix-subscriber 024010004 --set-issued=INV-202606-0002255,INV-202606-0002256 --dry-run
 */
class FixSubscriberInvoices extends Command
{
    protected $signature = 'invoices:fix-subscriber
                            {subscription? : رقم الاشتراك (مثل 024010004)}
                            {--subscriber= : معرّف المشترك (بديل عن رقم الاشتراك)}
                            {--set-issued= : فرض فواتير محددة كـ "متأخرة" (تُخزَّن ISSUED + يُرجَّع تاريخ الاستحقاق للماضي) — أرقام مفصولة بفواصل}
                            {--due= : تاريخ الاستحقاق المراد فرضه على فواتير --set-issued (افتراضياً: تاريخ الفاتورة)}
                            {--dry-run : معاينة التغييرات دون حفظ}';

    protected $description = 'إصلاح حالات الفواتير والرصيد السابق لمشترك واحد (FIFO + Running Balance)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $subscriber = $this->resolveSubscriber();
        if (!$subscriber) {
            return self::FAILURE;
        }

        $targetDue = $this->resolveTargetDueDate();
        if ($targetDue === false) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  إصلاح محاسبي لمشترك واحد' . ($dryRun ? '  [معاينة فقط]' : ''));
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $this->displaySubscriberSummary($subscriber);
        $this->displayPaymentsSummary($subscriber->id);
        $this->displayAccountSnapshot($subscriber->id, 'قبل الإصلاح');

        $statusChanges = $this->computeStatusChanges($subscriber->id);
        $balanceChanges = $this->computeBalanceChanges($subscriber->id);

        $this->displayStatusChanges($statusChanges);
        $this->displayBalanceChanges($balanceChanges);

        $setIssued = $this->computeSetIssued($subscriber->id, $targetDue);
        $this->displaySetIssued($setIssued);

        $draftChanges = $this->computeDraftPreview($subscriber->id);
        if ($draftChanges['count'] > 0) {
            $this->newLine();
            $this->comment("مسودات سيتم تحديثها: {$draftChanges['count']}");
        }

        $hasChanges = !empty($statusChanges)
            || !empty($balanceChanges)
            || $draftChanges['count'] > 0
            || !empty($setIssued['changes']);

        if (!$hasChanges) {
            $this->newLine();
            $this->info('✓ لا توجد تغييرات مطلوبة — بيانات المشترك متسقة.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('وضع المعاينة — لم تُحفظ أي تغييرات.');
            $this->line('للتنفيذ: php artisan invoices:fix-subscriber ' . $subscriber->subscription_number
                . ($this->option('set-issued') ? ' --set-issued=' . $this->option('set-issued') : '')
                . ($this->option('due') ? ' --due=' . $this->option('due') : ''));
            return self::SUCCESS;
        }

        if (!$this->confirm('هل تريد تنفيذ الإصلاح الآن؟', false)) {
            $this->warn('تم الإلغاء — لم تُحفظ أي تغييرات.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($subscriber, $setIssued) {
            $this->line('');
            $this->info('① إعادة تسوية FIFO (أولى)...');
            $stats1 = Invoice::reconcileSubscriber($subscriber->id);
            $this->line("   → {$stats1['updated']} فاتورة تغيّرت حالتها");

            $this->info('② إعادة حساب الرصيد السابق (Running Balance)...');
            $balanceUpdated = $this->applyBalanceChanges($subscriber->id);
            $this->line("   → {$balanceUpdated} فاتورة حُدّث رصيدها");

            $this->info('③ تحديث المسودات...');
            Invoice::refreshDraftsForSubscriber($subscriber->id);
            $this->line('   → تم');

            $this->info('④ إعادة تسوية FIFO (نهائية)...');
            $stats2 = Invoice::reconcileSubscriber($subscriber->id);
            $this->line("   → {$stats2['updated']} فاتورة تغيّرت حالتها");

            if (!empty($setIssued['changes'])) {
                $this->info('⑤ فرض الفواتير المحددة كـ "متأخرة"...');
                $forced = $this->applySetIssued($setIssued['changes']);
                $this->line("   → {$forced} فاتورة فُرضت إلى \"متأخرة\" (ISSUED + استحقاق مضى)");
            }
        });

        $this->newLine();
        $this->displayAccountSnapshot($subscriber->id, 'بعد الإصلاح');
        $this->newLine();
        $this->info('✓ اكتمل الإصلاح المحاسبي للمشترك ' . $subscriber->subscription_number);

        return self::SUCCESS;
    }

    private function resolveSubscriber(): ?Subscriber
    {
        $subscriberId = $this->option('subscriber');
        $subscription = $this->argument('subscription');

        if ($subscriberId) {
            $subscriber = Subscriber::find($subscriberId);
            if (!$subscriber) {
                $this->error("لم يُعثر على مشترك بالمعرّف: {$subscriberId}");
                return null;
            }
            return $subscriber;
        }

        if (!$subscription) {
            $this->error('يجب تحديد رقم الاشتراك أو --subscriber=ID');
            $this->line('مثال: php artisan invoices:fix-subscriber 024010004 --dry-run');
            return null;
        }

        $subscriber = Subscriber::where('subscription_number', $subscription)->first();
        if (!$subscriber) {
            $this->error("لم يُعثر على مشترك برقم الاشتراك: {$subscription}");
            return null;
        }

        return $subscriber;
    }

    private function displaySubscriberSummary(Subscriber $subscriber): void
    {
        $this->table(
            ['البيان', 'القيمة'],
            [
                ['رقم الاشتراك', $subscriber->subscription_number],
                ['الاسم', $subscriber->subscriber_name ?? '—'],
                ['معرّف المشترك', $subscriber->id],
            ]
        );
    }

    private function displayPaymentsSummary(int $subscriberId): void
    {
        $activeCount = Payment::where('subscriber_id', $subscriberId)->count();
        $activeTotal = (float) Payment::where('subscriber_id', $subscriberId)->sum('amount_paid');
        $deletedCount = Payment::onlyTrashed()->where('subscriber_id', $subscriberId)->count();

        $this->newLine();
        $this->comment('الدفعات:');
        $this->line("  • نشطة: {$activeCount} دفعة — المجموع: " . number_format($activeTotal, 2));
        if ($deletedCount > 0) {
            $this->line("  • محذوفة (soft delete): {$deletedCount} دفعة");
        }
    }

    private function displayAccountSnapshot(int $subscriberId, string $label): void
    {
        $invoices = Invoice::where('subscriber_id', $subscriberId)
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->get();

        $totalInvoiced = (float) $invoices->sum('invoice_amount');
        $totalPaid = (float) Payment::where('subscriber_id', $subscriberId)->sum('amount_paid');
        $balance = round($totalInvoiced - $totalPaid, 2);

        $unpaidCount = $invoices->filter(function ($inv) {
            return in_array((int) $inv->invoice_status, [Invoice::STATUS_ISSUED, Invoice::STATUS_PARTIAL], true);
        })->count();

        $this->newLine();
        $this->comment("ملخص الحساب — {$label}:");
        $this->table(
            ['البيان', 'القيمة'],
            [
                ['إجمالي الفواتير (invoice_amount)', number_format($totalInvoiced, 2)],
                ['إجمالي المسدد', number_format($totalPaid, 2)],
                ['الرصيد (مدين إذا موجب)', number_format($balance, 2)],
                ['فواتير غير مسددة / جزئية', $unpaidCount],
            ]
        );
    }

    /**
     * @return array<int, array{invoice_number: string, old_status: string, new_status: string}>
     */
    private function computeStatusChanges(int $subscriberId): array
    {
        $invoices = Invoice::where('subscriber_id', $subscriberId)
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        if ($invoices->isEmpty()) {
            return [];
        }

        $invoiceIds = $invoices->pluck('id')->all();
        $pool = (float) Payment::whereIn('invoice_id', $invoiceIds)->sum('amount_paid');
        $changes = [];

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

            if ((int) $invoice->invoice_status !== $newStatus) {
                $changes[] = [
                    'invoice_number' => $invoice->invoice_number ?? '#' . $invoice->id,
                    'old_status'     => $this->statusLabel((int) $invoice->invoice_status),
                    'new_status'     => $this->statusLabel($newStatus),
                ];
            }
        }

        return $changes;
    }

    /**
     * @param array<int, array{invoice_number: string, old_status: string, new_status: string}> $changes
     */
    private function displayStatusChanges(array $changes): void
    {
        $this->newLine();
        $this->comment('① تغييرات حالة الفواتير (FIFO):');

        if (empty($changes)) {
            $this->line('  ✓ لا تغيير — الحالات متوافقة مع الدفعات الحالية');
            return;
        }

        $this->table(
            ['رقم الفاتورة', 'الحالة الحالية', 'الحالة بعد الإصلاح'],
            array_map(fn ($row) => [$row['invoice_number'], $row['old_status'], $row['new_status']], $changes)
        );
    }

    /**
     * @return array<int, array{
     *     invoice_number: string,
     *     old_prev: string,
     *     new_prev: string,
     *     old_total: string,
     *     new_total: string
     * }>
     */
    private function computeBalanceChanges(int $subscriberId): array
    {
        $invoices = Invoice::where('subscriber_id', $subscriberId)
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $runningBalance = 0.0;
        $changes = [];

        foreach ($invoices as $invoice) {
            $prevBalance = round($runningBalance, 2);

            $amounts = Invoice::calculateAmounts(
                (float) $invoice->consumption_kwh,
                (float) $invoice->price_per_kwh,
                (float) $invoice->discount_rate,
                (float) $invoice->minimum_charge,
                $prevBalance
            );

            $oldPrev = round((float) $invoice->previous_balance, 2);
            $oldTotal = round((float) $invoice->total_amount, 2);
            $newTotal = round($amounts['total_amount'], 2);

            if ($oldPrev !== $prevBalance || $oldTotal !== $newTotal) {
                $changes[] = [
                    'invoice_number' => $invoice->invoice_number ?? '#' . $invoice->id,
                    'old_prev'       => number_format($oldPrev, 2),
                    'new_prev'       => number_format($prevBalance, 2),
                    'old_total'      => number_format($oldTotal, 2),
                    'new_total'      => number_format($newTotal, 2),
                ];
            }

            $paidOnThis = (float) $invoice->payments()->sum('amount_paid');
            $runningBalance = $runningBalance + (float) $invoice->invoice_amount - $paidOnThis;
        }

        return $changes;
    }

    /**
     * @param array<int, array{invoice_number: string, old_prev: string, new_prev: string, old_total: string, new_total: string}> $changes
     */
    private function displayBalanceChanges(array $changes): void
    {
        $this->newLine();
        $this->comment('② تغييرات الرصيد السابق والمبلغ المطلوب (Running Balance):');

        if (empty($changes)) {
            $this->line('  ✓ لا تغيير — الأرصدة السابقة متسقة');
            return;
        }

        $this->table(
            ['رقم الفاتورة', 'رصيد سابق (قبل)', 'رصيد سابق (بعد)', 'المطلوب (قبل)', 'المطلوب (بعد)'],
            array_map(
                fn ($row) => [
                    $row['invoice_number'],
                    $row['old_prev'],
                    $row['new_prev'],
                    $row['old_total'],
                    $row['new_total'],
                ],
                $changes
            )
        );
    }

    /**
     * تحليل خيار --set-issued إلى قائمة أرقام فواتير منظّفة.
     *
     * @return array<int, string>
     */
    private function parseSetIssuedNumbers(): array
    {
        $raw = (string) $this->option('set-issued');
        if (trim($raw) === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($n) => trim($n))
            ->filter(fn ($n) => $n !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * حساب الفواتير التي ستُفرض حالتها إلى "جديدة" عبر --set-issued.
     *
     * تاريخ الاستحقاق الهدف من خيار --due.
     *   - Carbon : تاريخ صريح يُطبَّق على كل فواتير --set-issued
     *   - null   : لم يُحدَّد (تلقائي: يُستخدم تاريخ الفاتورة لجعلها متأخرة)
     *   - false  : قيمة غير صالحة (إيقاف الأمر)
     */
    private function resolveTargetDueDate(): Carbon|false|null
    {
        $raw = $this->option('due');
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        try {
            return Carbon::parse(trim((string) $raw))->startOfDay();
        } catch (\Throwable $e) {
            $this->error("تاريخ غير صالح في --due: {$raw} (استخدم صيغة مثل 2026-05-31)");
            return false;
        }
    }

    /**
     * @return array{
     *     requested: array<int, string>,
     *     changes: array<int, array{id: int, invoice_number: string, old_status: string, new_status: string, old_due: string, new_due: string, due_changed: bool, target_due: ?Carbon}>,
     *     missing: array<int, string>,
     *     skipped: array<int, array{invoice_number: string, status: string}>,
     *     future_due: array<int, string>
     * }
     */
    private function computeSetIssued(int $subscriberId, ?Carbon $targetDue = null): array
    {
        $requested = $this->parseSetIssuedNumbers();
        $result = ['requested' => $requested, 'changes' => [], 'missing' => [], 'skipped' => [], 'future_due' => []];

        if (empty($requested)) {
            return $result;
        }

        /** @var Collection<int, Invoice> $invoices */
        $invoices = Invoice::where('subscriber_id', $subscriberId)
            ->whereIn('invoice_number', $requested)
            ->get()
            ->keyBy('invoice_number');

        foreach ($requested as $number) {
            $invoice = $invoices->get($number);

            if (!$invoice) {
                $result['missing'][] = $number;
                continue;
            }

            // لا يُسمح بفرض "متأخرة" على مسودة أو فاتورة ملغاة
            if (in_array((int) $invoice->invoice_status, [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED], true)) {
                $result['skipped'][] = [
                    'invoice_number' => $number,
                    'status'         => $this->statusLabel((int) $invoice->invoice_status),
                ];
                continue;
            }

            $currentDue = $invoice->due_date; // Carbon|null

            // تحديد تاريخ الاستحقاق الهدف
            if ($targetDue !== null) {
                $newDue = $targetDue->copy();
            } elseif ($currentDue === null || !$currentDue->isPast()) {
                // تلقائي: استخدم تاريخ الفاتورة (في الماضي) لجعلها متأخرة
                $newDue = $invoice->invoice_date ? $invoice->invoice_date->copy()->startOfDay() : null;
            } else {
                // تاريخ الاستحقاق مضى أصلاً → أبقِه كما هو
                $newDue = $currentDue->copy();
            }

            $willBeOverdue = $newDue !== null && $newDue->isPast();
            $newLabel = $willBeOverdue ? 'متأخرة' : 'جديدة';

            $oldDueStr = $currentDue ? $currentDue->format('Y-m-d') : '—';
            $newDueStr = $newDue ? $newDue->format('Y-m-d') : '—';

            $statusChanged = (int) $invoice->invoice_status !== Invoice::STATUS_ISSUED;
            $dueChanged = $oldDueStr !== $newDueStr;

            // لا شيء يتغيّر (issued أصلاً + نفس الاستحقاق)
            if (!$statusChanged && !$dueChanged) {
                if (!$willBeOverdue) {
                    $result['future_due'][] = $number;
                }
                continue;
            }

            if (!$willBeOverdue) {
                $result['future_due'][] = $number;
            }

            $result['changes'][] = [
                'id'             => (int) $invoice->id,
                'invoice_number' => $number,
                'old_status'     => $this->statusLabel((int) $invoice->invoice_status),
                'new_status'     => $newLabel,
                'old_due'        => $oldDueStr,
                'new_due'        => $newDueStr,
                'due_changed'    => $dueChanged,
                'target_due'     => $newDue,
            ];
        }

        return $result;
    }

    /**
     * @param array{requested: array, changes: array, missing: array, skipped: array, future_due: array} $setIssued
     */
    private function displaySetIssued(array $setIssued): void
    {
        if (empty($setIssued['requested'])) {
            return;
        }

        $this->newLine();
        $this->comment('③ فرض الفواتير المحددة كـ "متأخرة" (--set-issued):');

        if (!empty($setIssued['changes'])) {
            $this->table(
                ['رقم الفاتورة', 'الحالة الحالية', 'استحقاق (حالي)', 'استحقاق (بعد)', 'الناتج'],
                array_map(
                    fn ($row) => [
                        $row['invoice_number'],
                        $row['old_status'],
                        $row['old_due'],
                        $row['new_due'],
                        $row['new_status'],
                    ],
                    $setIssued['changes']
                )
            );
            $this->line('  ℹ تُخزَّن الحالة "جديدة" (ISSUED) مع إرجاع تاريخ الاستحقاق للماضي، فتظهر "متأخرة" تلقائياً.');
        } else {
            $this->line('  ✓ لا تغيير — الفواتير المطلوبة بحالتها الصحيحة بالفعل');
        }

        foreach ($setIssued['future_due'] as $number) {
            $this->warn("  ⚠ {$number}: تاريخ الاستحقاق الناتج لم يمضِ بعد — ستظهر \"جديدة\" لا \"متأخرة\". مرّر --due=YYYY-MM-DD بتاريخ ماضٍ.");
        }

        foreach ($setIssued['skipped'] as $row) {
            $this->warn("  ⚠ تخطّي {$row['invoice_number']} — حالتها \"{$row['status']}\" (لا يمكن فرضها متأخرة)");
        }

        foreach ($setIssued['missing'] as $number) {
            $this->error("  ✗ لم يُعثر على فاتورة بالرقم {$number} لهذا المشترك");
        }
    }

    /**
     * @param array<int, array{id: int, target_due: ?Carbon}> $changes
     */
    private function applySetIssued(array $changes): int
    {
        if (empty($changes)) {
            return 0;
        }

        $targetById = [];
        foreach ($changes as $c) {
            $targetById[$c['id']] = $c['target_due'];
        }

        $invoices = Invoice::whereIn('id', array_keys($targetById))
            ->lockForUpdate()
            ->get();

        $forced = 0;
        foreach ($invoices as $invoice) {
            $changed = false;

            if ((int) $invoice->invoice_status !== Invoice::STATUS_ISSUED) {
                $invoice->invoice_status = Invoice::STATUS_ISSUED;
                $changed = true;
            }

            $target = $targetById[$invoice->id] ?? null;
            if ($target !== null) {
                $currentStr = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null;
                if ($currentStr !== $target->format('Y-m-d')) {
                    $invoice->due_date = $target->copy();
                    $changed = true;
                }
            }

            if ($changed) {
                $invoice->saveQuietly();
                $forced++;
            }
        }

        return $forced;
    }

    /**
     * @return array{count: int}
     */
    private function computeDraftPreview(int $subscriberId): array
    {
        $count = Invoice::where('subscriber_id', $subscriberId)
            ->where('invoice_status', Invoice::STATUS_DRAFT)
            ->count();

        return ['count' => $count];
    }

    private function applyBalanceChanges(int $subscriberId): int
    {
        $invoices = Invoice::where('subscriber_id', $subscriberId)
            ->whereNotIn('invoice_status', [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $runningBalance = 0.0;
        $updated = 0;

        foreach ($invoices as $invoice) {
            $prevBalance = round($runningBalance, 2);

            $amounts = Invoice::calculateAmounts(
                (float) $invoice->consumption_kwh,
                (float) $invoice->price_per_kwh,
                (float) $invoice->discount_rate,
                (float) $invoice->minimum_charge,
                $prevBalance
            );

            $oldPrev = round((float) $invoice->previous_balance, 2);
            $oldTotal = round((float) $invoice->total_amount, 2);
            $newTotal = round($amounts['total_amount'], 2);

            if ($oldPrev !== $prevBalance || $oldTotal !== $newTotal) {
                $invoice->update([
                    'previous_balance' => $prevBalance,
                    'invoice_amount'   => $amounts['invoice_amount'],
                    'total_amount'     => $amounts['total_amount'],
                ]);
                $updated++;
            }

            $paidOnThis = (float) $invoice->payments()->sum('amount_paid');
            $runningBalance = $runningBalance + (float) $invoice->invoice_amount - $paidOnThis;
        }

        return $updated;
    }

    private function statusLabel(int $status): string
    {
        return match ($status) {
            Invoice::STATUS_DRAFT     => 'مسودة',
            Invoice::STATUS_ISSUED    => 'جديدة',
            Invoice::STATUS_PARTIAL   => 'مسددة جزئياً',
            Invoice::STATUS_PAID      => 'مسددة',
            Invoice::STATUS_CANCELLED => 'ملغاة',
            default                   => 'غير معروف',
        };
    }
}
