<?php

namespace Database\Seeders;

use App\Helpers\ConstantsHelper;
use App\Models\GenerationUnit;
use App\Models\Generator;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('بدء إنشاء بيانات الاختبار...');

        $nourUser = User::where('username', 'op_nour')->first();

        if (!$nourUser) {
            $this->command->error('يوزر op_nour غير موجود، يجب تشغيل UserSeeder أولاً!');
            return;
        }

        // ===== الثوابت =====
        $governorates = ConstantsHelper::get(1);
        $cities       = ConstantsHelper::get(20);

        if ($governorates->isEmpty() || $cities->isEmpty()) {
            $this->command->error('يجب تشغيل ConstantSeeder أولاً!');
            return;
        }

        $middleGovernorate = $governorates->where('code', 'MD')->first() ?? $governorates->first();
        $middleCity        = ConstantsHelper::getCitiesByGovernorate($middleGovernorate->id)->first();

        if (!$middleCity) {
            $this->command->error('لا توجد مدن متاحة!');
            return;
        }

        $statusActiveId                        = ConstantsHelper::get(15)->where('code', 'ACTIVE')->first()?->id;
        $operationSameOwnerId                  = ConstantsHelper::get(2)->where('code', 'SAME_OWNER')->first()?->id;
        $syncAvailableId                       = ConstantsHelper::get(16)->where('code', 'AVAILABLE')->first()?->id;
        $complianceCompliantId                 = ConstantsHelper::get(14)->where('code', 'COMPLIANT')->first()?->id;
        $generatorStatusActiveId               = ConstantsHelper::get(3)->where('code', 'ACTIVE')->first()?->id;
        $engineTypePerkinsId                   = ConstantsHelper::get(4)->where('code', 'PERKINS')->first()?->id;
        $injectionSystemMechanicalId           = ConstantsHelper::get(5)->where('code', 'MECHANICAL')->first()?->id;
        $measurementIndicatorAvailableWorkingId = ConstantsHelper::get(6)->where('code', 'AVAILABLE_WORKING')->first()?->id;
        $technicalConditionGoodId              = ConstantsHelper::get(7)->where('code', 'GOOD')->first()?->id;
        $controlPanelTypeDeepSeaId             = ConstantsHelper::get(8)->where('code', 'DEEP_SEA')->first()?->id;
        $controlPanelStatusWorkingId           = ConstantsHelper::get(9)->where('code', 'WORKING')->first()?->id;

        // ===== 1. المشغل: مشغل نور غزة 1 =====
        $operator = Operator::updateOrCreate(
            ['owner_id' => $nourUser->id],
            [
                'name'               => 'مشغل نور غزة 1',
                'owner_id'           => $nourUser->id,
                'owner_name'         => $nourUser->name,
                'owner_id_number'    => '100000001',
                'operator_id_number' => '200000001',
                'status'             => 'active',
                'is_approved'        => true,
                'profile_completed'  => true,
            ]
        );
        $this->command->info("✓ المشغل: {$operator->name} (op_nour)");

        // ===== 2. وحدة التوليد =====
        $governorateCode = $middleGovernorate->code;
        $cityCode        = $middleCity->code;
        $unitNumber      = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
        $unitCode        = "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";

        $generationUnit = GenerationUnit::updateOrCreate(
            ['operator_id' => $operator->id, 'name' => 'وحدة توليد نور غزة'],
            [
                'operator_id'                       => $operator->id,
                'unit_code'                         => $unitCode,
                'unit_number'                       => $unitNumber,
                'name'                              => 'وحدة توليد نور غزة',
                'generators_count'                  => 1,
                'status_id'                         => $statusActiveId,
                'owner_name'                        => $operator->owner_name,
                'owner_id_number'                   => $operator->owner_id_number,
                'operation_entity_id'               => $operationSameOwnerId,
                'operator_id_number'                => $operator->operator_id_number,
                'phone'                             => '0598888888',
                'phone_alt'                         => '0598888889',
                'email'                             => 'nour@operator.ps',
                'governorate_id'                    => $middleGovernorate->id,
                'city_id'                           => $middleCity->id,
                'detailed_address'                  => 'الوسطى - دير البلح',
                'latitude'                          => 31.3547,
                'longitude'                         => 34.3088,
                'total_capacity'                    => 250,
                'synchronization_available_id'      => $syncAvailableId,
                'max_synchronization_capacity'      => 200,
                'beneficiaries_count'               => 50,
                'beneficiaries_description'         => 'سكان المنطقة',
                'environmental_compliance_status_id'=> $complianceCompliantId,
            ]
        );
        $this->command->info("✓ وحدة التوليد: {$unitCode}");

        // ===== 3. المولد =====
        $generatorNumber = Generator::getNextGeneratorNumber($generationUnit->id);

        Generator::updateOrCreate(
            ['generation_unit_id' => $generationUnit->id, 'name' => 'مولد نور غزة'],
            [
                'operator_id'              => $operator->id,
                'generation_unit_id'       => $generationUnit->id,
                'name'                     => 'مولد نور غزة',
                'generator_number'         => $generatorNumber,
                'description'              => 'مولد ديزل',
                'status_id'                => $generatorStatusActiveId,
                'capacity_kva'             => 250,
                'power_factor'             => 0.8,
                'voltage'                  => 200,
                'frequency'                => 50,
                'engine_type_id'           => $engineTypePerkinsId,
                'manufacturing_year'       => 2023,
                'injection_system_id'      => $injectionSystemMechanicalId,
                'fuel_consumption_rate'    => 30.0,
                'ideal_fuel_efficiency'    => 0.5,
                'internal_tank_capacity'   => 250,
                'measurement_indicator_id' => $measurementIndicatorAvailableWorkingId,
                'technical_condition_id'   => $technicalConditionGoodId,
                'last_major_maintenance_date' => '2024-01-01',
                'control_panel_available'  => true,
                'control_panel_type_id'    => $controlPanelTypeDeepSeaId,
                'control_panel_status_id'  => $controlPanelStatusWorkingId,
                'operating_hours'          => 1000,
                'external_fuel_tank'       => false,
                'fuel_tanks_count'         => 0,
            ]
        );
        $this->command->info("✓ المولد: {$generatorNumber}");

        $this->command->info('تم إنشاء بيانات الاختبار بنجاح!');
    }
}
