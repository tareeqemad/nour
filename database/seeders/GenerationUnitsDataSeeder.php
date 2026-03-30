<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder لبيانات وحدات التوليد الفعلية
 */
class GenerationUnitsDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $units = [
            [
                'id' => 1,
                'operator_id' => 1,
                'created_by' => null,
                'last_updated_by' => null,
                'unit_code' => 'GU-GZ-GZ-001',
                'unit_number' => '001',
                'name' => 'وحدة التوليد الاختبارية',
                'generators_count' => 1,
                'status_id' => 65,
                'owner_name' => 'مالك الاختبار',
                'owner_id_number' => '123456789',
                'operation_entity_id' => 23,
                'operator_id_number' => '987654321',
                'operator_name' => null,
                'phone' => '0598888888',
                'phone_alt' => '0598888889',
                'email' => 'test@operator.ps',
                'governorate_id' => 2,
                'city_id' => 9,
                'detailed_address' => 'غزة - شارع الاختبار - مبنى رقم 1',
                'latitude' => 31.35470000,
                'longitude' => 34.30880000,
                'total_capacity' => 250.00,
                'synchronization_available_id' => 67,
                'max_synchronization_capacity' => 200.00,
                'beneficiaries_count' => 50,
                'beneficiaries_description' => 'سكان المنطقة',
                'environmental_compliance_status_id' => 61,
                'qr_code_generated_at' => null,
            ],
            [
                'id' => 2,
                'operator_id' => 1,
                'created_by' => 9,
                'last_updated_by' => 9,
                'unit_code' => 'GU-MD-DB-001',
                'unit_number' => '001',
                'name' => 'مولد نور الحياة',
                'generators_count' => 3,
                'status_id' => 65,
                'owner_name' => 'مالك الاختبار',
                'owner_id_number' => '123456789',
                'operation_entity_id' => 23,
                'operator_id_number' => '987654321',
                'operator_name' => 'op_test',
                'phone' => '0598888888',
                'phone_alt' => '0599701504',
                'email' => 'naamer@gedco.com',
                'governorate_id' => 3,
                'city_id' => 10,
                'detailed_address' => 'دير البلح شارع النخيل',
                'latitude' => 31.42258413,
                'longitude' => 34.33969975,
                'total_capacity' => 50.00,
                'synchronization_available_id' => 68,
                'max_synchronization_capacity' => 0.00,
                'beneficiaries_count' => 800,
                'beneficiaries_description' => null,
                'environmental_compliance_status_id' => 63,
                'qr_code_generated_at' => null,
            ],
        ];

        foreach ($units as $unit) {
            DB::table('generation_units')->updateOrInsert(
                ['id' => $unit['id']],
                array_merge($unit, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        $this->command->info('تم إدراج/تحديث ' . count($units) . ' وحدة توليد بنجاح.');
    }
}
