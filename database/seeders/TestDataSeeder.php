<?php

namespace Database\Seeders;

use App\Helpers\ConstantsHelper;
use App\Models\GenerationUnit;
use App\Models\Generator;
use App\Models\Operator;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('بدء إنشاء بيانات الاختبار...');

        // الحصول على الأدوار
        $energyAuthorityRole = RoleModel::where('name', 'energy_authority')->first();
        $companyOwnerRole = RoleModel::where('name', 'company_owner')->first();
        
        if (!$energyAuthorityRole || !$companyOwnerRole) {
            $this->command->error('يجب تشغيل RoleSeeder أولاً!');
            return;
        }

        // 1. إنشاء يوزر لسلطة الطاقة
        $energyAuthorityUser = User::updateOrCreate(
            ['username' => 'ea_test'],
            [
                'name' => 'مستخدم الاختبار',
                'name_en' => 'Test User',
                'email' => 'ea_test@gazarased.com',
                'username' => 'ea_test',
                'password' => Hash::make('12345678'),
                'role' => Role::EnergyAuthority,
                'role_id' => $energyAuthorityRole->id,
                'status' => 'active',
                'phone' => '0599999999',
            ]
        );

        $this->command->info("تم إنشاء يوزر سلطة الطاقة: ea_test / 12345678");

        // 1.5. إنشاء يوزر CompanyOwner للمشغل
        $companyOwnerUser = User::updateOrCreate(
            ['username' => 'op_test'],
            [
                'name' => 'مالك المشغل الاختباري',
                'name_en' => 'Test Company Owner',
                'email' => 'op_test@gazarased.com',
                'username' => 'op_test',
                'password' => Hash::make('12345678'),
                'role' => Role::CompanyOwner,
                'role_id' => $companyOwnerRole->id,
                'status' => 'active',
                'phone' => '0598888888',
            ]
        );

        $this->command->info("تم إنشاء يوزر المشغل: op_test / 12345678");

        // الحصول على الثوابت المطلوبة
        $governorates = ConstantsHelper::get(1);
        $cities = ConstantsHelper::get(20);
        
        if ($governorates->isEmpty() || $cities->isEmpty()) {
            $this->command->error('يجب تشغيل ConstantSeeder أولاً!');
            return;
        }

        // اختيار محافظة ومدينة (غزة)
        $gazaGovernorate = $governorates->where('code', 'GZ')->first();
        if (!$gazaGovernorate) {
            $gazaGovernorate = $governorates->first();
        }
        
        $gazaCities = ConstantsHelper::getCitiesByGovernorate($gazaGovernorate->id);
        $gazaCity = $gazaCities->first();
        
        if (!$gazaCity) {
            $this->command->error('لا توجد مدن متاحة!');
            return;
        }

        // الحصول على IDs من الثوابت
        $statusConstants = ConstantsHelper::get(15); // حالة الوحدة
        $operationEntityConstants = ConstantsHelper::get(2); // جهة التشغيل
        $syncConstants = ConstantsHelper::get(16); // إمكانية المزامنة
        $complianceConstants = ConstantsHelper::get(14); // حالة الامتثال البيئي
        $generatorStatusConstants = ConstantsHelper::get(3); // حالة المولد
        $engineTypeConstants = ConstantsHelper::get(4); // نوع المحرك
        $injectionSystemConstants = ConstantsHelper::get(5); // نظام الحقن
        $measurementIndicatorConstants = ConstantsHelper::get(6); // مؤشر القياس
        $technicalConditionConstants = ConstantsHelper::get(7); // الحالة الفنية
        $controlPanelTypeConstants = ConstantsHelper::get(8); // نوع لوحة التحكم
        $controlPanelStatusConstants = ConstantsHelper::get(9); // حالة لوحة التحكم

        $statusActiveId = $statusConstants->where('code', 'ACTIVE')->first()?->id;
        $operationSameOwnerId = $operationEntityConstants->where('code', 'SAME_OWNER')->first()?->id;
        $syncAvailableId = $syncConstants->where('code', 'AVAILABLE')->first()?->id;
        $complianceCompliantId = $complianceConstants->where('code', 'COMPLIANT')->first()?->id;
        $generatorStatusActiveId = $generatorStatusConstants->where('code', 'ACTIVE')->first()?->id;
        $engineTypePerkinsId = $engineTypeConstants->where('code', 'PERKINS')->first()?->id;
        $injectionSystemMechanicalId = $injectionSystemConstants->where('code', 'MECHANICAL')->first()?->id;
        $measurementIndicatorAvailableWorkingId = $measurementIndicatorConstants->where('code', 'AVAILABLE_WORKING')->first()?->id;
        $technicalConditionGoodId = $technicalConditionConstants->where('code', 'GOOD')->first()?->id;
        $controlPanelTypeDeepSeaId = $controlPanelTypeConstants->where('code', 'DEEP_SEA')->first()?->id;
        $controlPanelStatusWorkingId = $controlPanelStatusConstants->where('code', 'WORKING')->first()?->id;

        // 2. إنشاء مشغل op_test
        $operator = Operator::updateOrCreate(
            ['name' => 'op_test'],
            [
                'name' => 'op_test',
                'owner_id' => $companyOwnerUser->id,
                'owner_name' => 'مالك الاختبار',
                'owner_id_number' => '123456789',
                'operator_id_number' => '987654321',
                'status' => 'active',
                'is_approved' => true,
                'profile_completed' => true,
            ]
        );

        $this->command->info("تم إنشاء المشغل: op_test");

        // 3. إنشاء وحدة توليد وهمية
        $governorateCode = $gazaGovernorate->code;
        $cityCode = $gazaCity->code;
        $unitNumber = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
        $unitCode = "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";

        $generationUnit = GenerationUnit::updateOrCreate(
            ['unit_code' => $unitCode],
            [
                'operator_id' => $operator->id,
                'unit_code' => $unitCode,
                'unit_number' => $unitNumber,
                'name' => 'وحدة التوليد الاختبارية',
                'generators_count' => 1,
                'status_id' => $statusActiveId,
                // الملكية والتشغيل
                'owner_name' => $operator->owner_name,
                'owner_id_number' => $operator->owner_id_number,
                'operation_entity_id' => $operationSameOwnerId,
                'operator_id_number' => $operator->operator_id_number,
                'phone' => '0598888888',
                'phone_alt' => '0598888889',
                'email' => 'test@operator.ps',
                // الموقع
                'governorate_id' => $gazaGovernorate->id,
                'city_id' => $gazaCity->id,
                'detailed_address' => 'غزة - شارع الاختبار - مبنى رقم 1',
                'latitude' => 31.3547,
                'longitude' => 34.3088,
                // القدرات الفنية
                'total_capacity' => 250,
                'synchronization_available_id' => $syncAvailableId,
                'max_synchronization_capacity' => 200,
                // المستفيدون والبيئة
                'beneficiaries_count' => 50,
                'beneficiaries_description' => 'سكان المنطقة',
                'environmental_compliance_status_id' => $complianceCompliantId,
            ]
        );

        $this->command->info("تم إنشاء وحدة التوليد: {$unitCode}");

        // 4. إنشاء مولد وهمي
        $generatorNumber = Generator::getNextGeneratorNumber($generationUnit->id);
        
        $generator = Generator::updateOrCreate(
            ['generator_number' => $generatorNumber],
            [
                'operator_id' => $operator->id,
                'generation_unit_id' => $generationUnit->id,
                'name' => 'مولد الاختبار',
                'generator_number' => $generatorNumber,
                'description' => 'مولد ديزل للاختبار',
                'status_id' => $generatorStatusActiveId,
                // المواصفات الفنية
                'capacity_kva' => 250,
                'power_factor' => 0.8,
                'voltage' => 200,
                'frequency' => 50,
                'engine_type_id' => $engineTypePerkinsId,
                'manufacturing_year' => 2023,
                // التشغيل والوقود
                'injection_system_id' => $injectionSystemMechanicalId,
                'fuel_consumption_rate' => 30.0,
                'ideal_fuel_efficiency' => 0.5,
                'internal_tank_capacity' => 250,
                'measurement_indicator_id' => $measurementIndicatorAvailableWorkingId,
                // الحالة الفنية
                'technical_condition_id' => $technicalConditionGoodId,
                'last_major_maintenance_date' => '2024-01-01',
                // نظام التحكم
                'control_panel_available' => true,
                'control_panel_type_id' => $controlPanelTypeDeepSeaId,
                'control_panel_status_id' => $controlPanelStatusWorkingId,
                'operating_hours' => 1000,
                'external_fuel_tank' => false,
                'fuel_tanks_count' => 0,
            ]
        );

        $this->command->info("تم إنشاء المولد: {$generatorNumber}");

        $this->command->info('تم إنشاء جميع بيانات الاختبار بنجاح!');
        $this->command->info("يوزر سلطة الطاقة: ea_test / 12345678");
        $this->command->info("يوزر المشغل: op_test / 12345678");
        $this->command->info("المشغل: op_test");
        $this->command->info("وحدة التوليد: {$unitCode}");
        $this->command->info("المولد: {$generatorNumber}");
    }
}
