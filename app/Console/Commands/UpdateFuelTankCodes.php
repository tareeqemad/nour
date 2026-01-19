<?php

namespace App\Console\Commands;

use App\Models\FuelTank;
use Illuminate\Console\Command;

class UpdateFuelTankCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fuel-tanks:update-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحديث أكواد خزانات الوقود الموجودة إلى الصيغة الجديدة (unit_code-TXX)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('بدء تحديث أكواد خزانات الوقود...');
        
        $tanks = FuelTank::with('generator.operator')->get();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($tanks as $tank) {
            if (!$tank->generator) {
                $this->warn("الخزان #{$tank->id} لا يحتوي على مولد - تم التخطي");
                $skipped++;
                continue;
            }

            if (!$tank->generator->operator) {
                $this->warn("الخزان #{$tank->id} - المولد ({$tank->generator->name}) لا يحتوي على مشغل - تم التخطي");
                $skipped++;
                continue;
            }

            if (!$tank->generator->operator->unit_code) {
                $this->warn("الخزان #{$tank->id} - المشغل ({$tank->generator->operator->name}) لا يحتوي على unit_code - تم التخطي");
                $skipped++;
                continue;
            }

            // التحقق من أن الكود الحالي ليس بالصيغة الجديدة
            $unitCode = $tank->generator->operator->unit_code;
            $prefix = $unitCode . '-T';
            
            if ($tank->tank_code && str_starts_with($tank->tank_code, $prefix)) {
                $this->line("الخزان #{$tank->id} لديه كود بالصيغة الجديدة بالفعل - تم التخطي");
                $skipped++;
                continue;
            }

            // توليد كود جديد
            $newCode = FuelTank::getNextTankCode($tank->generator_id);
            
            if (!$newCode) {
                $this->error("تعذر توليد كود جديد للخزان #{$tank->id} - تم الوصول إلى الحد الأقصى");
                $errors++;
                continue;
            }

            // تحديث الكود
            try {
                $oldCode = $tank->tank_code ?? 'N/A';
                $tank->tank_code = $newCode;
                $tank->save();
                
                $this->info("✓ تم تحديث الخزان #{$tank->id}: {$oldCode} → {$newCode}");
                $updated++;
            } catch (\Exception $e) {
                $this->error("✗ خطأ في تحديث الخزان #{$tank->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('تم الانتهاء من تحديث أكواد خزانات الوقود!');
        $this->info('═══════════════════════════════════════');
        $this->info("تم التحديث: {$updated}");
        $this->info("تم التخطي: {$skipped}");
        $this->info("الأخطاء: {$errors}");
        $this->info('═══════════════════════════════════════');

        return Command::SUCCESS;
    }
}
