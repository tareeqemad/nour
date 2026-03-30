<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\Permission;
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
            $this->command->warn('لا يوجد مشغلين في النظام.');
            return;
        }

        $permissions = Permission::all();
        $created = 0;
        $skipped = 0;

        // صلاحيات الفني الافتراضية
        $technicianPermissionNames = [
            'generation_units.view', 'generators.view',
            'maintenance_records.view', 'maintenance_records.create', 'maintenance_records.update',
            'operation_logs.view', 'operation_logs.create', 'operation_logs.update',
            'fuel_efficiencies.view', 'fuel_efficiencies.create', 'fuel_efficiencies.update',
            'compliance_safeties.view', 'compliance_safeties.create', 'compliance_safeties.update',
            'meter_readings.view', 'meter_readings.create', 'meter_readings.update',
            'subscribers.view',
            'tasks.view', 'tasks.create', 'tasks.update',
        ];

        // صلاحيات المحاسب الافتراضية
        $accountantPermissionNames = [
            'subscribers.view', 'meter_readings.view',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.issue',
            'invoice_reports.view', 'subscriber_accounts.view',
            'minimum_charge_rules.view', 'minimum_charge_rules.update', 'electricity_tariff_prices.view',
            'generation_units.view', 'generators.view',
            'operation_logs.view', 'fuel_efficiencies.view',
            'maintenance_records.view', 'compliance_safeties.view',
        ];

        $techPermIds = $permissions->whereIn('name', $technicianPermissionNames)->pluck('id');
        $accPermIds = $permissions->whereIn('name', $accountantPermissionNames)->pluck('id');

        foreach ($operators as $operator) {
            $this->command->info("معالجة المشغل: {$operator->name} (ID: {$operator->id})");

            // 1. دور فني المشغل
            $technicianRole = Role::firstOrCreate(
                ['name' => 'technician_' . $operator->id, 'operator_id' => $operator->id],
                [
                    'label' => 'فني مشغل',
                    'description' => 'دور فني المشغل: ' . $operator->name,
                    'is_system' => false,
                    'created_by' => $operator->owner_id,
                    'order' => 100,
                ]
            );

            // تحديث الصلاحيات (sync يضيف الجديد ويشيل القديم)
            $technicianRole->permissions()->sync($techPermIds);

            if ($technicianRole->wasRecentlyCreated) {
                $this->command->info("  + فني المشغل ({$techPermIds->count()} صلاحية)");
                $created++;
            } else {
                $this->command->info("  ~ فني المشغل - تم تحديث الصلاحيات ({$techPermIds->count()})");
                $skipped++;
            }

            // 2. دور المحاسب
            $accountantRole = Role::firstOrCreate(
                ['name' => 'accountant_' . $operator->id, 'operator_id' => $operator->id],
                [
                    'label' => 'محاسب',
                    'description' => 'دور المحاسب للمشغل: ' . $operator->name,
                    'is_system' => false,
                    'created_by' => $operator->owner_id,
                    'order' => 101,
                ]
            );

            $accountantRole->permissions()->sync($accPermIds);

            if ($accountantRole->wasRecentlyCreated) {
                $this->command->info("  + محاسب ({$accPermIds->count()} صلاحية)");
                $created++;
            } else {
                $this->command->info("  ~ محاسب - تم تحديث الصلاحيات ({$accPermIds->count()})");
                $skipped++;
            }
        }

        $this->command->newLine();
        $this->command->info("تم: {$operators->count()} مشغل | {$created} دور جديد | {$skipped} دور محدّث");
    }
}
