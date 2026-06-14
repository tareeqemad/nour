<?php

namespace App\Console\Commands;

use App\Models\MeterReading;
use Illuminate\Console\Command;

class FixFirstMeterReadings extends Command
{
    protected $signature = 'meter-readings:fix-first {--dry-run : Preview changes without saving}';

    protected $description = 'تصحيح القراءات الأولى المخزّنة بقراءة سابقة 0 رغم وجود قراءة افتتاحية';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $candidates = MeterReading::with(['subscriber', 'invoice'])
            ->where('previous_reading', 0)
            ->whereHas('subscriber', fn ($q) => $q->whereNotNull('opening_reading')->where('opening_reading', '>', 0))
            ->orderBy('subscriber_id')
            ->orderBy('reading_date')
            ->orderBy('id')
            ->get()
            ->filter(fn (MeterReading $reading) => $reading->isFirstForSubscriber());

        if ($candidates->isEmpty()) {
            $this->info('لا توجد قراءات تحتاج تصحيحاً.');
            return self::SUCCESS;
        }

        $fixed = 0;
        $skipped = 0;

        foreach ($candidates as $reading) {
            $opening = (float) $reading->subscriber->opening_reading;
            $consumption = round((float) $reading->current_reading - $opening, 2);

            if ($consumption < 0) {
                $this->warn("تخطي {$reading->reading_number}: الاستهلاك المحسوب سالب ({$consumption} kWh)");
                $skipped++;
                continue;
            }

            $invoice = $reading->invoice;
            if ($invoice && abs((float) $invoice->consumption_kwh - $consumption) > 0.01) {
                $this->warn("تخطي {$reading->reading_number}: الفاتورة تسجّل {$invoice->consumption_kwh} kWh والتصحيح يعطي {$consumption} kWh");
                $skipped++;
                continue;
            }

            $updates = [
                'previous_reading' => $opening,
                'consumption_kwh' => $consumption,
                'reading_status' => MeterReading::determineReadingStatus($consumption),
            ];

            if ($reading->meter_number !== null
                && $reading->meter_number !== ''
                && (float) $reading->meter_number === $opening) {
                $updates['meter_number'] = $reading->subscriber->meter_number ?? '';
            }

            $this->line(sprintf(
                '%s | سابق: %s → %s | استهلاك: %s → %s',
                $reading->reading_number,
                $reading->previous_reading,
                $opening,
                $reading->consumption_kwh,
                $consumption
            ));

            if (!$dryRun) {
                $reading->update($updates);
            }

            $fixed++;
        }

        $this->info(($dryRun ? '[معاينة] ' : '') . "تم تصحيح {$fixed} قراءة. تم تخطي {$skipped}.");

        return self::SUCCESS;
    }
}
