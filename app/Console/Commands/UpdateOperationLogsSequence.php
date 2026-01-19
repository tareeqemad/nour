<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOperationLogsSequence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operation-logs:update-sequence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحديث التسلسل لسجلات التشغيل الموجودة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء تحديث التسلسل لسجلات التشغيل...');

        // الحصول على جميع المجموعات الفريدة (operator_id, generation_unit_id, generator_id)
        $groups = DB::table('operation_logs')
            ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
            ->whereNull('operation_logs.deleted_at')
            ->whereNotNull('generators.generation_unit_id')
            ->select(
                'operation_logs.operator_id',
                'generators.generation_unit_id',
                'operation_logs.generator_id'
            )
            ->distinct()
            ->get();
        
        $totalUpdated = 0;
        
        foreach ($groups as $group) {
            $logs = DB::table('operation_logs')
                ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
                ->where('operation_logs.operator_id', $group->operator_id)
                ->where('operation_logs.generator_id', $group->generator_id)
                ->where('generators.generation_unit_id', $group->generation_unit_id)
                ->whereNull('operation_logs.deleted_at')
                ->orderBy('operation_logs.operation_date')
                ->orderBy('operation_logs.start_time')
                ->orderBy('operation_logs.id')
                ->pluck('operation_logs.id');
            
            $sequence = 1;
            foreach ($logs as $logId) {
                DB::table('operation_logs')
                    ->where('id', $logId)
                    ->update(['sequence' => $sequence]);
                $sequence++;
            }
            
            $totalUpdated += $logs->count();
            
            // الحصول على معلومات المجموعة للعرض
            $operator = DB::table('operators')->where('id', $group->operator_id)->value('name');
            $generationUnit = DB::table('generation_units')->where('id', $group->generation_unit_id)->value('unit_code');
            $generator = DB::table('generators')->where('id', $group->generator_id)->value('generator_number');
            
            $this->info("تم تحديث {$logs->count()} سجل - المشغل: {$operator} | الوحدة: {$generationUnit} | المولد: {$generator}");
        }

        $this->info("تم تحديث {$totalUpdated} سجل بنجاح!");
        
        return Command::SUCCESS;
    }
}
