<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateDefaultOperatorRolesSeeder extends Seeder
{
    /**
     * إنشاء أدوار افتراضية (فني ومحاسب) لجميع المشغلين الموجودين
     */
    public function run(): void
    {
        $this->command->info('🚀 بدء إنشاء الأدوار الافتراضية للمشغلين...');
        
        $operators = Operator::all();
        
        if ($operators->isEmpty()) {
            $this->command->warn('⚠️  لا يوجد مشغلين في النظام.');
            return;
        }
        
        $created = 0;
        $skipped = 0;
        
        foreach ($operators as $operator) {
            $this->command->info("📋 معالجة المشغل: {$operator->name} (ID: {$operator->id})");
            
            // 1. إنشاء دور فني المشغل إذا لم يكن موجوداً
            $technicianRole = Role::firstOrCreate(
                [
                    'name' => 'technician_' . $operator->id,
                    'operator_id' => $operator->id,
                ],
                [
                    'label' => 'فني مشغل',
                    'description' => 'دور فني المشغل: ' . $operator->name,
                    'is_system' => false,
                    'created_by' => $operator->owner_id, // ربط الدور بالمشغل (المالك)
                    'order' => 100,
                ]
            );
            
            // تحديث الدور الموجود (label و created_by)
            if (!$technicianRole->wasRecentlyCreated) {
                $updates = [];
                if (!$technicianRole->created_by) {
                    $updates['created_by'] = $operator->owner_id;
                }
                if ($technicianRole->label !== 'فني مشغل') {
                    $updates['label'] = 'فني مشغل';
                }
                if (!empty($updates)) {
                    $technicianRole->update($updates);
                    $this->command->info("   🔄 تم تحديث دور فني المشغل");
                }
            }
            
            if ($technicianRole->wasRecentlyCreated) {
                $this->command->info("   ✅ تم إنشاء دور فني المشغل");
                $created++;
            } else {
                $this->command->warn("   ⏭️  دور فني المشغل موجود مسبقاً");
                $skipped++;
            }
            
            // 2. إنشاء دور المحاسب إذا لم يكن موجوداً
            $accountantRole = Role::firstOrCreate(
                [
                    'name' => 'accountant_' . $operator->id,
                    'operator_id' => $operator->id,
                ],
                [
                    'label' => 'محاسب',
                    'description' => 'دور المحاسب للمشغل: ' . $operator->name,
                    'is_system' => false,
                    'created_by' => $operator->owner_id, // ربط الدور بالمشغل (المالك)
                    'order' => 101,
                ]
            );
            
            // تحديث created_by إذا كان null للأدوار الموجودة
            if (!$accountantRole->wasRecentlyCreated && !$accountantRole->created_by) {
                $accountantRole->update(['created_by' => $operator->owner_id]);
                $this->command->info("   🔄 تم تحديث created_by لدور المحاسب");
            }
            
            if ($accountantRole->wasRecentlyCreated) {
                $this->command->info("   ✅ تم إنشاء دور المحاسب");
                $created++;
            } else {
                $this->command->warn("   ⏭️  دور المحاسب موجود مسبقاً");
                $skipped++;
            }
        }
        
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('✨ تم الانتهاء بنجاح!');
        $this->command->info("📊 عدد المشغلين: " . $operators->count());
        $this->command->info("✅ عدد الأدوار المُنشأة: {$created}");
        $this->command->info("⏭️  عدد الأدوار المتخطاة: {$skipped}");
        $this->command->info('═══════════════════════════════════════');
    }
}
