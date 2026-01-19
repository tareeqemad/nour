<?php

namespace App\Console\Commands;

use App\Models\Generator;
use App\Models\Operator;
use Illuminate\Console\Command;

class UpdateGeneratorNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generators:update-numbers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحديث أرقام المولدات الموجودة إلى الصيغة الجديدة (unit_code-GXX)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('بدء تحديث أرقام المولدات...');
        
        $generators = Generator::with('operator')->get();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($generators as $generator) {
            if (!$generator->operator) {
                $this->warn("المولد #{$generator->id} ({$generator->name}) لا يحتوي على مشغل - تم التخطي");
                $skipped++;
                continue;
            }

            if (!$generator->operator->unit_code) {
                $this->warn("المولد #{$generator->id} ({$generator->name}) - المشغل ({$generator->operator->name}) لا يحتوي على unit_code - تم التخطي");
                $skipped++;
                continue;
            }

            // التحقق من أن الرقم الحالي ليس بالصيغة الجديدة
            $unitCode = $generator->operator->unit_code;
            $prefix = $unitCode . '-G';
            
            if (str_starts_with($generator->generator_number, $prefix)) {
                $this->line("المولد #{$generator->id} ({$generator->name}) لديه رقم بالصيغة الجديدة بالفعل - تم التخطي");
                $skipped++;
                continue;
            }

            // توليد رقم جديد
            $newNumber = Generator::getNextGeneratorNumber($generator->operator_id);
            
            if (!$newNumber) {
                $this->error("تعذر توليد رقم جديد للمولد #{$generator->id} ({$generator->name}) - تم الوصول إلى الحد الأقصى");
                $errors++;
                continue;
            }

            // تحديث الرقم
            try {
                $oldNumber = $generator->generator_number;
                $generator->generator_number = $newNumber;
                $generator->save();
                
                $this->info("✓ تم تحديث المولد #{$generator->id} ({$generator->name}): {$oldNumber} → {$newNumber}");
                $updated++;
            } catch (\Exception $e) {
                $this->error("✗ خطأ في تحديث المولد #{$generator->id} ({$generator->name}): {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('تم الانتهاء من تحديث أرقام المولدات!');
        $this->info('═══════════════════════════════════════');
        $this->info("تم التحديث: {$updated}");
        $this->info("تم التخطي: {$skipped}");
        $this->info("الأخطاء: {$errors}");
        $this->info('═══════════════════════════════════════');

        return Command::SUCCESS;
    }
}
