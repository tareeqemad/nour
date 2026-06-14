<?php

namespace App\Console\Commands;

use App\Models\PortalRequest;
use App\Services\PortalApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * مزامنة طلبات البوابة (e.services.gov.ps) إلى جدول محلي portal_requests
 * ليتم الاستعلام عنها بدون استدعاء الـ API في كل مرة.
 *
 * أمثلة:
 *   php artisan portal:sync-requests --dry-run        (معاينة العدد فقط)
 *   php artisan portal:sync-requests                   (مزامنة كل الطلبات)
 *   php artisan portal:sync-requests --status=APPROVED (حالة محددة)
 *   php artisan portal:sync-requests --date=2026-06-14 (تاريخ تقديم محدد)
 */
class SyncPortalRequests extends Command
{
    protected $signature = 'portal:sync-requests
                            {--status= : مزامنة كود حالة محدد فقط}
                            {--date= : مزامنة تاريخ تقديم محدد فقط (YYYY-MM-DD)}
                            {--per-page=100 : عدد العناصر في كل صفحة من الـ API}
                            {--max-pages=500 : أقصى عدد صفحات تُجلب (حماية)}
                            {--fresh : تفريغ الجدول قبل المزامنة}
                            {--dry-run : معاينة دون كتابة أي بيانات}';

    protected $description = 'مزامنة طلبات البوابة من الـ API إلى جدول portal_requests المحلي';

    public function handle(PortalApiService $api): int
    {
        $dryRun   = (bool) $this->option('dry-run');
        $perPage  = max(1, min(200, (int) $this->option('per-page')));
        $maxPages = max(1, (int) $this->option('max-pages'));

        $filters = [];
        if ($this->option('status')) {
            $filters['status'] = trim((string) $this->option('status'));
        }
        if ($this->option('date')) {
            // الـ API يطلب DD-MM-YYYY بينما المستخدم يُدخل YYYY-MM-DD
            $d = trim((string) $this->option('date'));
            $parts = explode('-', $d);
            $filters['date'] = count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : $d;
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  مزامنة طلبات البوابة' . ($dryRun ? '  [معاينة فقط]' : ''));
        $this->info('═══════════════════════════════════════════════════════');
        $this->line('المصدر: ' . config('services.portal.base_url'));
        if (!empty($filters)) {
            $this->line('الفلاتر: ' . json_encode($filters, JSON_UNESCAPED_UNICODE));
        }
        $this->newLine();

        if ($this->option('fresh') && !$dryRun) {
            if ($this->confirm('سيتم حذف كل سجلات portal_requests الحالية قبل المزامنة. متابعة؟', false)) {
                PortalRequest::truncate();
                $this->warn('تم تفريغ الجدول.');
            } else {
                $this->warn('أُلغي التفريغ — سنكمل بالتحديث فوق الموجود.');
            }
        }

        $created = 0;
        $updated = 0;
        $processed = 0;
        $skipped = 0;
        $page = 1;
        $allPages = null;

        do {
            try {
                $res = $api->fetchPage($page, $perPage, $filters);
            } catch (\Throwable $e) {
                $this->error("فشل جلب الصفحة {$page}: " . $e->getMessage());
                return self::FAILURE;
            }

            if ($allPages === null) {
                $allPages = max(1, $res['allPages']);
                $this->line("إجمالي الصفحات: {$allPages} (≈ " . ($allPages * $perPage) . " طلب كحد أقصى)");
                if ($allPages > $maxPages) {
                    $this->warn("⚠ الإجمالي ({$allPages}) أكبر من الحد {$maxPages} — سيتم التوقف عند {$maxPages}. زِد --max-pages للمزامنة الكاملة.");
                }
                $this->newLine();
            }

            $items = $res['items'];
            if (empty($items)) {
                break;
            }

            if (!$dryRun) {
                DB::transaction(function () use ($items, &$created, &$updated, &$processed, &$skipped) {
                    foreach ($items as $row) {
                        if (($row['app_no'] ?? '') === '') {
                            $skipped++;
                            continue;
                        }
                        $processed++;

                        $existing = PortalRequest::where('app_no', $row['app_no'])->first();
                        $payload  = $row + ['synced_at' => now()];

                        if ($existing) {
                            $existing->fill($payload)->save();
                            $updated++;
                        } else {
                            PortalRequest::create($payload);
                            $created++;
                        }
                    }
                });
            } else {
                foreach ($items as $row) {
                    if (($row['app_no'] ?? '') === '') {
                        $skipped++;
                        continue;
                    }
                    $processed++;
                }
            }

            $this->line("صفحة {$page}/{$allPages} — عناصر: " . count($items));
            $page++;
        } while ($page <= min($allPages, $maxPages));

        $this->newLine();
        if ($dryRun) {
            $this->warn("معاينة — لم تُكتب أي بيانات.");
            $this->line("طلبات ستُزامن: {$processed}" . ($skipped ? " (تُتخطّى بدون رقم: {$skipped})" : ''));
        } else {
            $this->info("✓ اكتملت المزامنة — جديدة: {$created}، محدّثة: {$updated}، إجمالي معالَج: {$processed}" . ($skipped ? "، متخطّاة: {$skipped}" : ''));
            $this->line('إجمالي السجلات في الجدول الآن: ' . PortalRequest::count());
        }

        return self::SUCCESS;
    }
}
