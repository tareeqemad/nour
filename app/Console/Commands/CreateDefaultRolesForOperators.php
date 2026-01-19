<?php

namespace App\Console\Commands;

use App\Models\Operator;
use App\Models\Role;
use Illuminate\Console\Command;

class CreateDefaultRolesForOperators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operators:create-default-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء أدوار افتراضية (فني ومحاسب) لجميع المشغلين الموجودين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء إنشاء الأدوار الافتراضية للمشغلين...');
        
        $operators = Operator::all();
        $created = 0;
        $skipped = 0;
        
        foreach ($operators as $operator) {
            $this->info("معالجة المشغل: {$operator->name} (ID: {$operator->id})");
            
            // التحقق من وجود دور الفني
            $technicianRole = Role::where('operator_id', $operator->id)
                ->where('name', 'technician_' . $operator->id)
                ->first();
                
            if (!$technicianRole) {
                Role::create([
                    'name' => 'technician_' . $operator->id,
                    'label' => 'فني',
                    'description' => 'دور الفني - تم إنشاؤه تلقائياً للمشغل: ' . $operator->name,
                    'is_system' => false,
                    'operator_id' => $operator->id,
                    'order' => 100,
                ]);
                $this->info("  ✓ تم إنشاء دور الفني");
                $created++;
            } else {
                $this->warn("  - دور الفني موجود مسبقاً");
                $skipped++;
            }
            
            // التحقق من وجود دور المحاسب
            $accountantRole = Role::where('operator_id', $operator->id)
                ->where('name', 'accountant_' . $operator->id)
                ->first();
                
            if (!$accountantRole) {
                Role::create([
                    'name' => 'accountant_' . $operator->id,
                    'label' => 'محاسب',
                    'description' => 'دور المحاسب - تم إنشاؤه تلقائياً للمشغل: ' . $operator->name,
                    'is_system' => false,
                    'operator_id' => $operator->id,
                    'order' => 101,
                ]);
                $this->info("  ✓ تم إنشاء دور المحاسب");
                $created++;
            } else {
                $this->warn("  - دور المحاسب موجود مسبقاً");
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info("===========================================");
        $this->info("تم الانتهاء!");
        $this->info("عدد المشغلين: " . $operators->count());
        $this->info("عدد الأدوار المُنشأة: {$created}");
        $this->info("عدد الأدوار المتخطاة (موجودة مسبقاً): {$skipped}");
        $this->info("===========================================");
        
        return Command::SUCCESS;
    }
}
