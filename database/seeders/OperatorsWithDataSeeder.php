<?php

namespace Database\Seeders;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Models\ComplianceSafety;
use App\Models\FuelEfficiency;
use App\Models\FuelTank;
use App\Models\GenerationUnit;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\OperationLog;
use App\Models\Operator;
use App\Models\Permission;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OperatorsWithDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('بدء إنشاء البيانات...');

        // الحصول على المحافظات والمدن من الثوابت
        $governoratesMaster = ConstantsHelper::get(1); // رقم ثابت المحافظات
        $citiesMaster = ConstantsHelper::get(20); // رقم ثابت المدن
        
        if ($governoratesMaster->isEmpty() || $citiesMaster->isEmpty()) {
            $this->command->error('يجب تشغيل ConstantSeeder أولاً!');
            return;
        }
        
        // تحضير بيانات المحافظات مع المدن من الثوابت
        $governoratesData = [];
        foreach ($governoratesMaster as $gov) {
            $cities = ConstantsHelper::getCitiesByGovernorate($gov->id);
            if ($cities->isNotEmpty()) {
                $governoratesData[] = [
                    'id' => $gov->id,
                    'code' => $gov->code,
                    'value' => (int) $gov->value,
                    'name' => $gov->label,
                    'cities' => $cities->map(function($city) {
                        return [
                            'id' => $city->id,
                            'code' => $city->code,
                            'label' => $city->label,
                        ];
                    })->toArray(),
                ];
            }
        }
        
        if (empty($governoratesData)) {
            $this->command->error('لا توجد محافظات مع مدن في الثوابت!');
            return;
        }

        // إحداثيات تقريبية لكل محافظة (استخدام value من الثوابت)
        $governorateCoordinates = [
            10 => ['lat' => 31.3547, 'lng' => 34.3088], // شمال غزة
            20 => ['lat' => 31.3547, 'lng' => 34.3088], // غزة
            30 => ['lat' => 31.4170, 'lng' => 34.3500], // الوسطى
            40 => ['lat' => 31.3436, 'lng' => 34.3061], // خانيونس
            50 => ['lat' => 31.2969, 'lng' => 34.2436], // رفح
        ];

        // أسماء المشغلين
        $operatorNames = [
            'gedco1',
            'gedco2',
            'gedco3',
            'gedco4',
        ];

        // أسماء للموظفين
        $employeeNames = [
            'أحمد محمد علي',
            'محمد خالد حسن',
            'خالد أحمد محمود',
            'علي سعيد إبراهيم',
            'سعيد فتحي ناصر',
            'فتحي رامي سالم',
            'رامي وليد كمال',
            'وليد يوسف سمير',
            'يوسف تامر بدر',
            'تامر زياد عمر',
        ];

        // أسماء للمولدات
        $generatorNames = [
            'المولد الرئيسي',
            'المولد الاحتياطي الأول',
            'المولد الاحتياطي الثاني',
            'المولد المساعد',
            'المولد الإضافي',
        ];

        // أسماء الفنيين
        $technicianNames = [
            'فني صيانة 1',
            'فني صيانة 2',
            'فني صيانة 3',
            'فني كهرباء 1',
            'فني كهرباء 2',
        ];

        // جمع جميع المولدات لاستخدامها في السجلات
        $allGenerators = collect();
        $allOperators = collect();
        $mmlukGenerators = collect(); // مولدات مشغل المملوك
        $mmlukOperator = null; // مشغل المملوك

        // جلب الثوابت (نستخدمها لاحقاً)
        $statusConstants = ConstantsHelper::get(3); // حالة المولد
        $engineTypeConstants = ConstantsHelper::get(4); // نوع المحرك
        $injectionSystemConstants = ConstantsHelper::get(5); // نظام الحقن
        $measurementIndicatorConstants = ConstantsHelper::get(6); // مؤشر القياس
        $technicalConditionConstants = ConstantsHelper::get(7); // الحالة الفنية
        $controlPanelTypeConstants = ConstantsHelper::get(8); // نوع لوحة التحكم
        $controlPanelStatusConstants = ConstantsHelper::get(9); // حالة لوحة التحكم
        
        // ثوابت إضافية
        $maintenanceTypeConstants = ConstantsHelper::get(12); // نوع الصيانة
        $fuelEfficiencyComparisonConstants = ConstantsHelper::get(17); // مقارنة كفاءة الوقود
        $energyEfficiencyComparisonConstants = ConstantsHelper::get(18); // مقارنة كفاءة الطاقة
        $safetyCertificateStatusConstants = ConstantsHelper::get(13); // حالة شهادة السلامة
        $tankLocationConstants = ConstantsHelper::get(21); // موقع الخزان
        $tankMaterialConstants = ConstantsHelper::get(10); // مادة التصنيع
        $tankUsageConstants = ConstantsHelper::get(11); // الاستخدام
        $tankMeasurementMethodConstants = ConstantsHelper::get(19); // طريقة القياس
        
        // الحصول على الأدوار النظامية
        $companyOwnerRoleModel = RoleModel::where('name', 'company_owner')->first();
        $employeeRoleModel = RoleModel::where('name', 'employee')->first();
        $technicianRoleModel = RoleModel::where('name', 'technician')->first();

        // دالة مساعدة للحصول على ID الثابت بناءً على code
        $getConstantId = function($collection, $code) {
            $detail = $collection->where('code', $code)->first();
            return $detail ? $detail->id : null;
        };
        
        // دالة مساعدة للحصول على ID عشوائي من مجموعة ثوابت
        $getRandomConstantId = function($collection) {
            return $collection->isNotEmpty() ? $collection->random()->id : null;
        };
        
        // جلب IDs ثوابت نوع الصيانة
        $maintenanceTypePeriodicId = $getConstantId($maintenanceTypeConstants, 'PERIODIC');
        $maintenanceTypeEmergencyId = $getConstantId($maintenanceTypeConstants, 'EMERGENCY');

        // إنشاء مشغل المملوك أولاً
        $this->command->info('جاري إنشاء مشغل المملوك...');
        $mmlukOwner = User::where('username', 'mmluk')->first();
        
        if ($mmlukOwner) {
            // التحقق من عدم وجود المشغل مسبقاً
            $existingOperator = Operator::where('name', 'مشغل المملوك')->first();
            if (!$existingOperator) {
                // الحصول على محافظة غزة ومدينة غزة من الثوابت
                $gazaGovernorate = $governoratesMaster->where('code', 'GZ')->first();
                $gazaCity = $citiesMaster->where('code', 'GZ')->first();
                
                if ($gazaGovernorate && $gazaCity) {
                    $governorateEnum = Governorate::fromValue((int) $gazaGovernorate->value);
                    
                    $mmlukOperator = Operator::create([
                        'name' => 'مشغل المملوك',
                        'owner_id' => $mmlukOwner->id,
                        'status' => 'active',
                        'profile_completed' => true,
                    ]);
                }

                $allOperators->push($mmlukOperator);

                // الحصول على IDs الثوابت للمولدات
                $statusActiveId = $getConstantId($statusConstants, 'ACTIVE');
                $engineTypePerkinsId = $getConstantId($engineTypeConstants, 'PERKINS');
                $injectionSystemMechanicalId = $getConstantId($injectionSystemConstants, 'MECHANICAL');
                $measurementIndicatorAvailableWorkingId = $getConstantId($measurementIndicatorConstants, 'AVAILABLE_WORKING');
                $technicalConditionGoodId = $getConstantId($technicalConditionConstants, 'GOOD');
                $controlPanelTypeDeepSeaId = $getConstantId($controlPanelTypeConstants, 'DEEP_SEA');
                $controlPanelStatusWorkingId = $getConstantId($controlPanelStatusConstants, 'WORKING');
                
                // إنشاء 4 مولدات لمشغل المملوك
                $mmlukGeneratorsData = [
                    [
                        'name' => 'مولد المملوك 1',
                        'description' => 'مولد ديزل بقوة 100 كيلو فولت أمبير',
                        'capacity_kva' => 100,
                        'power_factor' => 0.8,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type_id' => $engineTypePerkinsId,
                        'manufacturing_year' => 2020,
                        'injection_system_id' => $injectionSystemMechanicalId,
                        'fuel_consumption_rate' => 25.5,
                        'ideal_fuel_efficiency' => 0.5,
                        'internal_tank_capacity' => 200,
                        'measurement_indicator_id' => $measurementIndicatorAvailableWorkingId,
                        'technical_condition_id' => $technicalConditionGoodId,
                        'control_panel_available' => true,
                        'control_panel_type_id' => $controlPanelTypeDeepSeaId,
                        'control_panel_status_id' => $controlPanelStatusWorkingId,
                        'operating_hours' => 5000,
                        'external_fuel_tank' => true,
                        'fuel_tanks_count' => 2,
                        'status_id' => $statusActiveId,
                    ],
                    [
                        'name' => 'مولد المملوك 2',
                        'description' => 'مولد ديزل بقوة 150 كيلو فولت أمبير',
                        'capacity_kva' => 150,
                        'power_factor' => 0.85,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type_id' => $engineTypePerkinsId,
                        'manufacturing_year' => 2021,
                        'injection_system_id' => $injectionSystemMechanicalId,
                        'fuel_consumption_rate' => 35.0,
                        'ideal_fuel_efficiency' => 0.55,
                        'internal_tank_capacity' => 300,
                        'measurement_indicator_id' => $measurementIndicatorAvailableWorkingId,
                        'technical_condition_id' => $technicalConditionGoodId,
                        'control_panel_available' => true,
                        'control_panel_type_id' => $controlPanelTypeDeepSeaId,
                        'control_panel_status_id' => $controlPanelStatusWorkingId,
                        'operating_hours' => 3500,
                        'external_fuel_tank' => true,
                        'fuel_tanks_count' => 2,
                        'status_id' => $statusActiveId,
                    ],
                    [
                        'name' => 'مولد المملوك 3',
                        'description' => 'مولد ديزل بقوة 200 كيلو فولت أمبير',
                        'capacity_kva' => 200,
                        'power_factor' => 0.9,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type_id' => $engineTypePerkinsId,
                        'manufacturing_year' => 2019,
                        'injection_system_id' => $injectionSystemMechanicalId,
                        'fuel_consumption_rate' => 45.5,
                        'ideal_fuel_efficiency' => 0.48,
                        'internal_tank_capacity' => 400,
                        'measurement_indicator_id' => $measurementIndicatorAvailableWorkingId,
                        'technical_condition_id' => $technicalConditionGoodId,
                        'control_panel_available' => true,
                        'control_panel_type_id' => $controlPanelTypeDeepSeaId,
                        'control_panel_status_id' => $controlPanelStatusWorkingId,
                        'operating_hours' => 8000,
                        'external_fuel_tank' => true,
                        'fuel_tanks_count' => 2,
                        'status_id' => $statusActiveId,
                    ],
                    [
                        'name' => 'مولد المملوك 4',
                        'description' => 'مولد ديزل بقوة 50 كيلو فولت أمبير',
                        'capacity_kva' => 50,
                        'power_factor' => 0.75,
                        'voltage' => 400,
                        'frequency' => 50,
                        'engine_type_id' => $engineTypePerkinsId,
                        'manufacturing_year' => 2022,
                        'injection_system_id' => $injectionSystemMechanicalId,
                        'fuel_consumption_rate' => 15.0,
                        'ideal_fuel_efficiency' => 0.52,
                        'internal_tank_capacity' => 150,
                        'measurement_indicator_id' => $measurementIndicatorAvailableWorkingId,
                        'technical_condition_id' => $technicalConditionGoodId,
                        'control_panel_available' => true,
                        'control_panel_type_id' => $controlPanelTypeDeepSeaId,
                        'control_panel_status_id' => $controlPanelStatusWorkingId,
                        'operating_hours' => 2000,
                        'external_fuel_tank' => false,
                        'fuel_tanks_count' => 0,
                        'status_id' => $statusActiveId,
                    ],
                ];

                // إنشاء وحدة توليد واحدة لمشغل المملوك
                $governorateCode = $governorateEnum->code();
                $cityCode = $gazaCity->code;
                $unitNumber = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
                $unitCode = "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";
                
                // الحصول على IDs من الثوابت
                $statusConstants = ConstantsHelper::get(15); // حالة الوحدة
                $operationEntityConstants = ConstantsHelper::get(2); // جهة التشغيل
                $syncConstants = ConstantsHelper::get(16); // إمكانية المزامنة
                $complianceConstants = ConstantsHelper::get(14); // حالة الامتثال البيئي
                
                $statusActiveId = $statusConstants->where('code', 'ACTIVE')->first()?->id;
                $operationSameOwnerId = $operationEntityConstants->where('code', 'SAME_OWNER')->first()?->id;
                $syncAvailableId = $syncConstants->where('code', 'AVAILABLE')->first()?->id;
                $complianceCompliantId = $complianceConstants->where('code', 'COMPLIANT')->first()?->id;
                
                $generationUnit = GenerationUnit::create([
                    'operator_id' => $mmlukOperator->id,
                    'unit_code' => $unitCode,
                    'unit_number' => $unitNumber,
                    'name' => 'وحدة التوليد الرئيسية',
                    'generators_count' => 4,
                    'status_id' => $statusActiveId,
                    // الملكية والتشغيل
                    'owner_name' => $mmlukOwner->name,
                    'owner_id_number' => str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                    'operation_entity_id' => $operationSameOwnerId,
                    'operator_id_number' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'phone' => '0599123456',
                    'phone_alt' => '0599123457',
                    'email' => 'info@mmluk.ps',
                    // الموقع
                    'governorate_id' => $gazaGovernorate->id,
                    'city_id' => $gazaCity->id,
                    'detailed_address' => 'غزة - شارع المملوك - مبنى رقم 5',
                    'latitude' => 31.3547,
                    'longitude' => 34.3088,
                    // القدرات الفنية
                    'total_capacity' => 500,
                    'synchronization_available_id' => $syncAvailableId,
                    'max_synchronization_capacity' => 400,
                    // المستفيدون والبيئة
                    'beneficiaries_count' => 150,
                    'beneficiaries_description' => 'سكان المنطقة والمؤسسات',
                    'environmental_compliance_status_id' => $complianceCompliantId,
                ]);

                foreach ($mmlukGeneratorsData as $genData) {
                    // توليد رقم المولد تلقائياً بناءً على unit_code لوحدة التوليد
                    $genData['generator_number'] = Generator::getNextGeneratorNumber($generationUnit->id);
                    
                    $generator = Generator::create([
                        'operator_id' => $mmlukOperator->id,
                        'generation_unit_id' => $generationUnit->id,
                        ...$genData,
                    ]);

                    $allGenerators->push($generator);
                    $mmlukGenerators->push($generator); // إضافة لمولدات المملوك

                    // إنشاء خزانات الوقود إذا كان المولد يحتوي على خزانات
                    if (isset($genData['fuel_tanks_count']) && $genData['fuel_tanks_count'] > 0 && $generator->generation_unit_id) {
                        for ($t = 0; $t < $genData['fuel_tanks_count']; $t++) {
                            // توليد كود الخزان تلقائياً
                            $tankCode = FuelTank::getNextTankCode($generator->generation_unit_id);
                            
                            FuelTank::create([
                                'generation_unit_id' => $generator->generation_unit_id,
                                'tank_code' => $tankCode,
                                'capacity' => rand(100, 500),
                                'location_id' => $getRandomConstantId($tankLocationConstants),
                                'filtration_system_available' => rand(0, 1) === 1,
                                'condition' => ['جيد', 'ممتاز', 'مقبول'][rand(0, 2)],
                                'material_id' => $getRandomConstantId($tankMaterialConstants),
                                'usage_id' => $getRandomConstantId($tankUsageConstants),
                                'measurement_method_id' => $getRandomConstantId($tankMeasurementMethodConstants),
                                'order' => $t + 1,
                            ]);
                        }
                    }
                }

                // إنشاء أدوار خاصة لمشغل المملوك
                $this->command->info("جاري إنشاء أدوار خاصة لمشغل المملوك...");
                
                // الحصول على الصلاحيات المتاحة (ما عدا صلاحيات النظام)
                $allPermissions = Permission::all();
                $systemPermissions = [
                    'users.view', 'users.create', 'users.update', 'users.delete',
                    'operators.view', 'operators.create', 'operators.update', 'operators.delete',
                    'permissions.manage',
                ];
                $availablePermissions = $allPermissions->reject(function ($permission) use ($systemPermissions) {
                    return in_array($permission->name, $systemPermissions);
                });

                // دور: فني صيانة متقدم
                $mmlukAdvancedTechnicianRole = RoleModel::create([
                    'name' => 'technician_advanced_mmluk',
                    'label' => 'فني صيانة متقدم - مشغل المملوك',
                    'description' => 'فني صيانة متقدم مع صلاحيات كاملة في الصيانة',
                    'is_system' => false,
                    'operator_id' => $mmlukOperator->id,
                    'order' => 10,
                ]);
                $mmlukAdvancedTechnicianRole->permissions()->attach($availablePermissions->whereIn('name', [
                    'generators.view',
                    'maintenance_records.view',
                    'maintenance_records.create',
                    'maintenance_records.update',
                ])->pluck('id'));

                // دور: مشرف سجلات
                $mmlukRecordsSupervisorRole = RoleModel::create([
                    'name' => 'records_supervisor_mmluk',
                    'label' => 'مشرف سجلات - مشغل المملوك',
                    'description' => 'مشرف على جميع السجلات',
                    'is_system' => false,
                    'operator_id' => $mmlukOperator->id,
                    'order' => 11,
                ]);
                $mmlukRecordsSupervisorRole->permissions()->attach($availablePermissions->whereIn('name', [
                    'operation_logs.view',
                    'operation_logs.create',
                    'operation_logs.update',
                    'fuel_efficiencies.view',
                    'fuel_efficiencies.create',
                    'fuel_efficiencies.update',
                    'maintenance_records.view',
                    'maintenance_records.create',
                    'maintenance_records.update',
                    'compliance_safeties.view',
                    'compliance_safeties.create',
                    'compliance_safeties.update',
                ])->pluck('id'));

                // الحصول على الأدوار النظامية
                $employeeRoleModel = RoleModel::where('name', 'employee')->first();
                $technicianRoleModel = RoleModel::where('name', 'technician')->first();

                // ربط الموظفين الخمسة بالمشغل مع أدوار مختلفة
                $employees = User::whereIn('username', [
                    'emp1_mmluk',
                    'emp2_mmluk',
                    'emp3_mmluk',
                    'emp4_mmluk',
                    'emp5_mmluk',
                ])->get();

                // تحديث role_id لبعض الموظفين لاستخدام الأدوار الخاصة
                if ($employees->count() >= 5) {
                    // الموظف 3 - استخدام دور فني صيانة متقدم
                    $emp3 = $employees->where('username', 'emp3_mmluk')->first();
                    if ($emp3) {
                        $emp3->update(['role_id' => $mmlukAdvancedTechnicianRole->id]);
                    }
                    
                    // الموظف 4 - استخدام دور مشرف سجلات
                    $emp4 = $employees->where('username', 'emp4_mmluk')->first();
                    if ($emp4) {
                        $emp4->update(['role_id' => $mmlukRecordsSupervisorRole->id]);
                    }
                }

                foreach ($employees as $employee) {
                    $mmlukOperator->users()->attach($employee->id);
                }

                $this->command->info('✓ تم إنشاء مشغل المملوك مع 4 مولدات و ' . $employees->count() . ' موظف وأدوار خاصة');
            } else {
                $this->command->info('مشغل المملوك موجود بالفعل، سيتم استخدامه');
                $allOperators->push($existingOperator);
                $mmlukOperator = $existingOperator; // حفظ للملوك
                // إضافة مولدات المشغل الموجود
                $existingGenerators = $existingOperator->generators;
                $allGenerators = $allGenerators->merge($existingGenerators);
                $mmlukGenerators = $mmlukGenerators->merge($existingGenerators); // إضافة لمولدات المملوك
            }
        } else {
            $this->command->warn('لم يتم العثور على المستخدم mmluk، سيتم تخطي مشغل المملوك');
        }

        // إنشاء 4 مشغلين
        for ($i = 0; $i < 4; $i++) {
            // اختيار محافظة عشوائية
            $governorateData = $governoratesData[$i % count($governoratesData)];
            
            // اختيار مدينة عشوائية من المحافظة المختارة
            $cityData = $governorateData['cities'][array_rand($governorateData['cities'])];
            
            $coords = $governorateCoordinates[$governorateData['value']] ?? ['lat' => 31.3547, 'lng' => 34.3088];
            
            // إضافة تغيير بسيط في الإحداثيات
            $latitude = $coords['lat'] + (rand(-50, 50) / 1000);
            $longitude = $coords['lng'] + (rand(-50, 50) / 1000);

            // تحويل المحافظة إلى enum
            $governorateEnum = Governorate::fromValue($governorateData['value']);

            // إنشاء CompanyOwner
            $ownerPassword = 'password';
            $owner = User::firstOrCreate(
                ['email' => 'owner' . ($i + 1) . '@example.com'],
                [
                    'name' => 'صاحب شركة ' . ($i + 1),
                    'username' => 'company_owner_' . ($i + 1),
                    'password' => Hash::make($ownerPassword),
                    'role' => Role::CompanyOwner,
                    'status' => 'active',
                ]
            );

            // إنشاء Operator (فقط الاسم)
            $operator = Operator::create([
                'name' => $operatorNames[$i],
                'owner_id' => $owner->id,
                'status' => 'active',
                'profile_completed' => true,
            ]);

            $allOperators->push($operator);

            // إعادة تحميل المشغل من قاعدة البيانات لضمان تحديث جميع العلاقات
            $operator = $operator->fresh();

            // التحقق من أن المشغل لديه governorate و city_id (يجب أن يكون موجوداً من البيانات المحملة)
            // لكن في الواقع، هذه البيانات يجب أن تأتي من المشغل نفسه وليس من الـ form
            // لذلك سنجعل governorate و city_id من المشغل نفسه
            // لكن بما أن المشغل لا يحتوي على هذه البيانات الآن، سنستخدم البيانات من المتغيرات
            $cityDetail = \App\Models\ConstantDetail::find($cityData['id']);
            if (!$cityDetail || !$cityDetail->code) {
                $this->command->warn("فشل الحصول على city code للمشغل {$operator->id}");
                continue;
            }
            $cityCode = $cityDetail->code;
            $governorateCode = $governorateEnum->code();

            // إنشاء 2-3 وحدات توليد لكل مشغل
            $unitsCount = rand(2, 3);
            $operatorTotalGenerators = 0;
            
            for ($unitIndex = 0; $unitIndex < $unitsCount; $unitIndex++) {
                // توليد رقم الوحدة وكودها
                $unitNumber = GenerationUnit::getNextUnitNumberByLocation($governorateCode, $cityCode);
                $unitCode = "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";
                
                // عدد المولدات في هذه الوحدة (لا يقل عن 2)
                $generatorsCount = rand(2, 4);
                $operatorTotalGenerators += $generatorsCount;
                
                // الحصول على IDs من الثوابت
                $statusConstants = ConstantsHelper::get(15); // حالة الوحدة
                $operationEntityConstants = ConstantsHelper::get(2); // جهة التشغيل
                $syncConstants = ConstantsHelper::get(16); // إمكانية المزامنة
                $complianceConstants = ConstantsHelper::get(14); // حالة الامتثال البيئي
                
                $statusActiveId = $statusConstants->where('code', 'ACTIVE')->first()?->id;
                $operationSameOwnerId = $operationEntityConstants->where('code', 'SAME_OWNER')->first()?->id;
                $operationOtherPartyId = $operationEntityConstants->where('code', 'OTHER_PARTY')->first()?->id;
                $syncAvailableId = $syncConstants->where('code', 'AVAILABLE')->first()?->id;
                $syncNotAvailableId = $syncConstants->where('code', 'NOT_AVAILABLE')->first()?->id;
                $complianceCompliantId = $complianceConstants->where('code', 'COMPLIANT')->first()?->id;
                $complianceNonCompliantId = $complianceConstants->where('code', 'NON_COMPLIANT')->first()?->id;
                
                $generationUnit = GenerationUnit::create([
                    'operator_id' => $operator->id,
                    'unit_code' => $unitCode,
                    'unit_number' => $unitNumber,
                    'name' => 'وحدة التوليد ' . ($unitIndex + 1) . ' - ' . $operatorNames[$i],
                    'generators_count' => $generatorsCount,
                    'status_id' => $statusActiveId,
                    // الملكية والتشغيل
                    'owner_name' => $owner->name,
                    'owner_id_number' => str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                    'operation_entity_id' => rand(0, 1) === 1 ? $operationSameOwnerId : $operationOtherPartyId,
                    'operator_id_number' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                    'phone' => '059' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'phone_alt' => '056' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'email' => 'operator' . ($i + 1) . '_unit' . ($unitIndex + 1) . '@example.com',
                    // الموقع
                    'governorate_id' => $governorateData['id'],
                    'city_id' => $cityData['id'],
                    'detailed_address' => 'مبنى رقم ' . ($i + 1) . '-' . ($unitIndex + 1) . '، ' . $cityData['label'] . '، ' . $governorateData['name'],
                    'latitude' => $latitude + (rand(-10, 10) / 1000),
                    'longitude' => $longitude + (rand(-10, 10) / 1000),
                    // القدرات الفنية
                    'total_capacity' => rand(500, 2000),
                    'synchronization_available_id' => rand(0, 1) === 1 ? $syncAvailableId : $syncNotAvailableId,
                    'max_synchronization_capacity' => rand(300, 1500),
                    // المستفيدون والبيئة
                    'beneficiaries_count' => rand(50, 500),
                    'beneficiaries_description' => 'مستفيدون من خدمات الكهرباء في منطقة ' . $cityData['label'],
                    'environmental_compliance_status_id' => rand(0, 1) === 1 ? $complianceCompliantId : $complianceNonCompliantId,
                ]);

                // إنشاء المولدات تابعة لوحدة التوليد (لا يقل عن 2)
                for ($j = 0; $j < $generatorsCount; $j++) {
                    // توليد رقم المولد تلقائياً بناءً على unit_code لوحدة التوليد
                    $generatorNumber = Generator::getNextGeneratorNumber($generationUnit->id);
                    if (!$generatorNumber) {
                        $this->command->warn("تم الوصول إلى الحد الأقصى لعدد المولدات لوحدة التوليد {$generationUnit->id}");
                        break;
                    }
                    
                    // اختيار IDs عشوائية من الثوابت
                    $statusId = $getConstantId($statusConstants, 'ACTIVE') ?? $getRandomConstantId($statusConstants);
                    $engineTypeId = $getRandomConstantId($engineTypeConstants);
                    $injectionSystemId = $getRandomConstantId($injectionSystemConstants);
                    $measurementIndicatorId = $getRandomConstantId($measurementIndicatorConstants);
                    $technicalConditionId = $getRandomConstantId($technicalConditionConstants);
                    $controlPanelTypeId = $getRandomConstantId($controlPanelTypeConstants);
                    $controlPanelStatusId = $getRandomConstantId($controlPanelStatusConstants);

                    $generator = Generator::create([
                        'name' => $generatorNames[$j % count($generatorNames)] . ' - وحدة ' . ($unitIndex + 1),
                        'generator_number' => $generatorNumber,
                        'operator_id' => $operator->id,
                        'generation_unit_id' => $generationUnit->id,
                        'description' => 'مولد كهربائي بقدرة ' . rand(50, 500) . ' KVA',
                        'status_id' => $statusId,
                        'capacity_kva' => rand(50, 500),
                        'power_factor' => round(rand(80, 95) / 100, 2),
                        'voltage' => rand(220, 380),
                        'frequency' => 50,
                        'engine_type_id' => $engineTypeId,
                        'manufacturing_year' => rand(2015, 2024),
                        'injection_system_id' => $injectionSystemId,
                        'fuel_consumption_rate' => round(rand(10, 50) + (rand(0, 99) / 100), 2),
                        'ideal_fuel_efficiency' => round(0.4 + (rand(0, 20) / 100), 3),
                        'internal_tank_capacity' => rand(100, 500),
                        'measurement_indicator_id' => $measurementIndicatorId,
                        'technical_condition_id' => $technicalConditionId,
                        'last_major_maintenance_date' => now()->subDays(rand(30, 365)),
                        'control_panel_available' => rand(0, 1) === 1,
                        'control_panel_type_id' => $controlPanelTypeId,
                        'control_panel_status_id' => $controlPanelStatusId,
                        'operating_hours' => rand(1000, 10000),
                        'external_fuel_tank' => rand(0, 1) === 1,
                        'fuel_tanks_count' => rand(0, 3),
                    ]);

                    $allGenerators->push($generator);

                    // إنشاء خزانات وقود لوحدة التوليد (إذا كان المولد لديه خزانات)
                    if ($generator->fuel_tanks_count > 0 && $generator->generation_unit_id) {
                        for ($t = 0; $t < $generator->fuel_tanks_count; $t++) {
                            // توليد كود الخزان تلقائياً
                            $tankCode = FuelTank::getNextTankCode($generator->generation_unit_id);
                            
                            FuelTank::create([
                                'generation_unit_id' => $generator->generation_unit_id,
                                'tank_code' => $tankCode,
                                'capacity' => rand(100, 500),
                                'location_id' => $getRandomConstantId($tankLocationConstants),
                                'filtration_system_available' => rand(0, 1) === 1,
                                'condition' => ['جيد', 'ممتاز', 'مقبول'][rand(0, 2)],
                                'material_id' => $getRandomConstantId($tankMaterialConstants),
                                'usage_id' => $getRandomConstantId($tankUsageConstants),
                                'measurement_method_id' => $getRandomConstantId($tankMeasurementMethodConstants),
                                'order' => $t + 1,
                            ]);
                        }
                    }
                }
            }

            // إنشاء أدوار خاصة لكل مشغل
            $this->command->info("جاري إنشاء أدوار خاصة لمشغل: {$operator->name}");
            
            // الحصول على الصلاحيات المتاحة (ما عدا صلاحيات النظام)
            $allPermissions = Permission::all();
            $systemPermissions = [
                'users.view', 'users.create', 'users.update', 'users.delete',
                'operators.view', 'operators.create', 'operators.update', 'operators.delete',
                'permissions.manage',
            ];
            $availablePermissions = $allPermissions->reject(function ($permission) use ($systemPermissions) {
                return in_array($permission->name, $systemPermissions);
            });

            // دور: فني صيانة متقدم (له صلاحيات صيانة كاملة)
            $advancedTechnicianRole = RoleModel::create([
                'name' => 'technician_advanced_' . $operator->id,
                'label' => 'فني صيانة متقدم - ' . $operator->name,
                'description' => 'فني صيانة متقدم مع صلاحيات كاملة في الصيانة',
                'is_system' => false,
                'operator_id' => $operator->id,
                'order' => 10,
            ]);
            $advancedTechnicianRole->permissions()->attach($availablePermissions->whereIn('name', [
                'generators.view',
                'maintenance_records.view',
                'maintenance_records.create',
                'maintenance_records.update',
            ])->pluck('id'));

            // دور: موظف مبيعات (صلاحيات عرض فقط)
            $salesEmployeeRole = RoleModel::create([
                'name' => 'sales_employee_' . $operator->id,
                'label' => 'موظف مبيعات - ' . $operator->name,
                'description' => 'موظف مبيعات مع صلاحيات عرض فقط',
                'is_system' => false,
                'operator_id' => $operator->id,
                'order' => 11,
            ]);
            $salesEmployeeRole->permissions()->attach($availablePermissions->whereIn('name', [
                'generators.view',
                'generation_units.view',
                'operation_logs.view',
            ])->pluck('id'));

            // دور: مشرف سجلات (صلاحيات على السجلات)
            $recordsSupervisorRole = RoleModel::create([
                'name' => 'records_supervisor_' . $operator->id,
                'label' => 'مشرف سجلات - ' . $operator->name,
                'description' => 'مشرف على جميع السجلات',
                'is_system' => false,
                'operator_id' => $operator->id,
                'order' => 12,
            ]);
            $recordsSupervisorRole->permissions()->attach($availablePermissions->whereIn('name', [
                'operation_logs.view',
                'operation_logs.create',
                'operation_logs.update',
                'fuel_efficiencies.view',
                'fuel_efficiencies.create',
                'fuel_efficiencies.update',
                'maintenance_records.view',
                'maintenance_records.create',
                'maintenance_records.update',
                'compliance_safeties.view',
                'compliance_safeties.create',
                'compliance_safeties.update',
            ])->pluck('id'));

            // إنشاء 6 موظفين لكل مشغل
            // 2 موظفين بنظام Employee
            // 2 فنيين بنظام Technician
            // 1 فني صيانة متقدم (دور خاص)
            // 1 مشرف سجلات (دور خاص)
            $usersRoles = [
                ['role' => Role::Employee, 'roleModel' => $employeeRoleModel, 'name' => 'موظف عادي'],
                ['role' => Role::Employee, 'roleModel' => $employeeRoleModel, 'name' => 'موظف عادي'],
                ['role' => Role::Technician, 'roleModel' => $technicianRoleModel, 'name' => 'فني'],
                ['role' => Role::Technician, 'roleModel' => $technicianRoleModel, 'name' => 'فني'],
                ['role' => Role::Employee, 'roleModel' => $advancedTechnicianRole, 'name' => 'فني صيانة متقدم'], // استخدام دور خاص
                ['role' => Role::Employee, 'roleModel' => $recordsSupervisorRole, 'name' => 'مشرف سجلات'], // استخدام دور خاص
            ];

            for ($k = 0; $k < 6; $k++) {
                $userRoleData = $usersRoles[$k];
                $employeePassword = 'password';
                $employee = User::firstOrCreate(
                    ['email' => 'user' . ($i + 1) . '_' . ($k + 1) . '@example.com'],
                    [
                        'name' => $employeeNames[$k] . ' (' . $userRoleData['name'] . ')',
                        'username' => 'user_' . ($i + 1) . '_' . ($k + 1),
                        'password' => Hash::make($employeePassword),
                        'role' => $userRoleData['role'],
                        'role_id' => $userRoleData['roleModel']?->id,
                        'status' => 'active',
                    ]
                );

                // ربط الموظف/الفني بالمشغل
                $operator->users()->attach($employee->id);
            }
        }

        $this->command->info('تم إنشاء 4 مشغلين مع وحدات التوليد والمولدات وموظفيهم');

        // دالة مساعدة لإنشاء سجل تشغيل مع sequence
        $createOperationLog = function($generator, $operationDate, $startTime, $endTime, $loadPercentage, $fuelMeterStart, $fuelMeterEnd, $fuelConsumed, $energyMeterStart, $energyMeterEnd, $energyProduced, $operationalNotes, $malfunctions) {
            // حساب التسلسل لكل مولد
            $lastSequence = OperationLog::where('generator_id', $generator->id)->max('sequence') ?? 0;
            $sequence = $lastSequence + 1;

            return OperationLog::create([
                'generator_id' => $generator->id,
                'operator_id' => $generator->operator_id,
                'sequence' => $sequence,
                'operation_date' => $operationDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'load_percentage' => $loadPercentage,
                'fuel_meter_start' => $fuelMeterStart,
                'fuel_meter_end' => $fuelMeterEnd,
                'fuel_consumed' => $fuelConsumed,
                'energy_meter_start' => $energyMeterStart,
                'energy_meter_end' => $energyMeterEnd,
                'energy_produced' => $energyProduced,
                'operational_notes' => $operationalNotes,
                'malfunctions' => $malfunctions,
            ]);
        };

        // إنشاء أكثر من 100 سجل تشغيل لمشغل المملوك
        if ($mmlukOperator && $mmlukGenerators->isNotEmpty()) {
            $this->command->info('جاري إنشاء سجلات تشغيل لمشغل المملوك...');
            $mmlukOperationLogsCount = 110; // أكثر من 100
            
            // تجميع السجلات لكل مولد حسب التاريخ لضمان التسلسل الصحيح
            $mmlukLogsData = [];
            $logsPerGenerator = (int) ceil($mmlukOperationLogsCount / $mmlukGenerators->count());
            foreach ($mmlukGenerators as $mmlukGenerator) {
                for ($i = 0; $i < $logsPerGenerator; $i++) {
                    $operationDate = now()->subDays(rand(0, 365));
                    $startTime = $operationDate->copy()->setTime(rand(6, 10), rand(0, 59));
                    $endTime = $startTime->copy()->addHours(rand(2, 12))->addMinutes(rand(0, 59));
                    
                    $loadPercentage = round(rand(30, 10000) / 100, 2); // بين 30.00 و 100.00
                    $fuelMeterStart = round(rand(0, 1000) + (rand(0, 99) / 100), 2);
                    $fuelMeterEnd = $fuelMeterStart + round(rand(10, 200) + (rand(0, 99) / 100), 2);
                    $fuelConsumed = $fuelMeterEnd - $fuelMeterStart;
                    
                    $energyMeterStart = round(rand(0, 10000) + (rand(0, 99) / 100), 2);
                    $energyMeterEnd = $energyMeterStart + round(rand(50, 500) + (rand(0, 99) / 100), 2);
                    $energyProduced = $energyMeterEnd - $energyMeterStart;
                    
                    $mmlukLogsData[] = [
                        'generator' => $mmlukGenerator,
                        'operation_date' => $operationDate,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'load_percentage' => $loadPercentage,
                        'fuel_meter_start' => $fuelMeterStart,
                        'fuel_meter_end' => $fuelMeterEnd,
                        'fuel_consumed' => $fuelConsumed,
                        'energy_meter_start' => $energyMeterStart,
                        'energy_meter_end' => $energyMeterEnd,
                        'energy_produced' => $energyProduced,
                        'operational_notes' => rand(0, 1) === 1 ? 'تشغيل عادي بدون مشاكل' : 'تم التشغيل بنجاح',
                        'malfunctions' => rand(0, 1) === 1 ? null : 'لا توجد أعطال',
                    ];
                }
            }
            
            // ترتيب حسب المولد ثم التاريخ
            usort($mmlukLogsData, function($a, $b) {
                if ($a['generator']->id != $b['generator']->id) {
                    return $a['generator']->id <=> $b['generator']->id;
                }
                return $a['operation_date'] <=> $b['operation_date'];
            });
            
            // إنشاء السجلات بالترتيب الصحيح
            foreach ($mmlukLogsData as $logData) {
                $createOperationLog(
                    $logData['generator'],
                    $logData['operation_date'],
                    $logData['start_time'],
                    $logData['end_time'],
                    $logData['load_percentage'],
                    $logData['fuel_meter_start'],
                    $logData['fuel_meter_end'],
                    $logData['fuel_consumed'],
                    $logData['energy_meter_start'],
                    $logData['energy_meter_end'],
                    $logData['energy_produced'],
                    $logData['operational_notes'],
                    $logData['malfunctions']
                );
            }
            
            $this->command->info('✓ تم إنشاء ' . count($mmlukLogsData) . ' سجل تشغيل لمشغل المملوك');
        }

        // إنشاء 100 سجل تشغيل لباقي المشغلين
        $this->command->info('جاري إنشاء 100 سجل تشغيل للمشغلين الآخرين...');
        $mmlukGeneratorIds = $mmlukGenerators->pluck('id')->toArray();
        $otherGenerators = $allGenerators->filter(function($gen) use ($mmlukGeneratorIds) {
            return !in_array($gen->id, $mmlukGeneratorIds);
        });
        for ($i = 0; $i < 100; $i++) {
            $generator = $otherGenerators->random();
            $operator = $generator->operator;

            $operationDate = now()->subDays(rand(0, 365));
            $startTime = $operationDate->copy()->setTime(rand(6, 10), rand(0, 59));
            $endTime = $startTime->copy()->addHours(rand(2, 12))->addMinutes(rand(0, 59));

            $loadPercentage = round(rand(30, 10000) / 100, 2); // بين 30.00 و 100.00
            $fuelMeterStart = round(rand(0, 1000) + (rand(0, 99) / 100), 2);
            $fuelMeterEnd = $fuelMeterStart + round(rand(10, 200) + (rand(0, 99) / 100), 2);
            $fuelConsumed = $fuelMeterEnd - $fuelMeterStart;

            $energyMeterStart = round(rand(0, 10000) + (rand(0, 99) / 100), 2);
            $energyMeterEnd = $energyMeterStart + round(rand(50, 500) + (rand(0, 99) / 100), 2);
            $energyProduced = $energyMeterEnd - $energyMeterStart;

            $createOperationLog(
                $generator,
                $operationDate,
                $startTime,
                $endTime,
                $loadPercentage,
                $fuelMeterStart,
                $fuelMeterEnd,
                $fuelConsumed,
                $energyMeterStart,
                $energyMeterEnd,
                $energyProduced,
                rand(0, 1) === 1 ? 'تشغيل عادي بدون مشاكل' : 'تم التشغيل بنجاح',
                rand(0, 1) === 1 ? null : 'لا توجد أعطال'
            );
        }
        $this->command->info('✓ تم إنشاء 100 سجل تشغيل للمشغلين الآخرين');

        // إنشاء أكثر من 100 سجل صيانة لمشغل المملوك
        if ($mmlukOperator && $mmlukGenerators->isNotEmpty()) {
            $this->command->info('جاري إنشاء سجلات صيانة لمشغل المملوك...');
            $mmlukMaintenanceCount = 110;
            
            for ($i = 0; $i < $mmlukMaintenanceCount; $i++) {
                $generator = $mmlukGenerators->random();
                $maintenanceDate = now()->subDays(rand(0, 365));
                $startHour = rand(8, 14);
                $startMinute = rand(0, 59);
                $endHour = $startHour + rand(2, 8);
                $endMinute = rand(0, 59);
                if ($endHour >= 24) {
                    $endHour = 23;
                    $endMinute = 59;
                }
                
                $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
                $endTime = sprintf('%02d:%02d:00', $endHour, $endMinute);
                
                $laborHours = round(rand(2, 8) + (rand(0, 99) / 100), 2);
                $laborRatePerHour = round(rand(50, 200) + (rand(0, 99) / 100), 2);
                $partsCost = round(rand(100, 2000) + (rand(0, 99) / 100), 2);
                $laborCost = round($laborHours * $laborRatePerHour, 2);
                $maintenanceCost = round($partsCost + $laborCost, 2);

                $maintenanceTypeId = rand(0, 1) === 1 ? $maintenanceTypePeriodicId : $maintenanceTypeEmergencyId;
                
                MaintenanceRecord::create([
                    'generator_id' => $generator->id,
                    'maintenance_type_id' => $maintenanceTypeId,
                    'maintenance_date' => $maintenanceDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'technician_name' => $technicianNames[rand(0, count($technicianNames) - 1)],
                    'work_performed' => 'تم إجراء صيانة على المولد',
                    'downtime_hours' => round(rand(1, 24) + (rand(0, 99) / 100), 2),
                    'parts_cost' => $partsCost,
                    'labor_hours' => $laborHours,
                    'labor_rate_per_hour' => $laborRatePerHour,
                    'maintenance_cost' => $maintenanceCost,
                ]);
            }
            $this->command->info('✓ تم إنشاء ' . $mmlukMaintenanceCount . ' سجل صيانة لمشغل المملوك');
        }

        // إنشاء 100 سجل صيانة لباقي المشغلين
        $this->command->info('جاري إنشاء 100 سجل صيانة للمشغلين الآخرين...');
        $mmlukGeneratorIds = $mmlukGenerators->pluck('id')->toArray();
        $otherGenerators = $allGenerators->filter(function($gen) use ($mmlukGeneratorIds) {
            return !in_array($gen->id, $mmlukGeneratorIds);
        });
        for ($i = 0; $i < 100; $i++) {
            $generator = $otherGenerators->random();
            $maintenanceDate = now()->subDays(rand(0, 365));
            $startHour = rand(8, 14);
            $startMinute = rand(0, 59);
            $endHour = $startHour + rand(2, 8);
            $endMinute = rand(0, 59);
            if ($endHour >= 24) {
                $endHour = 23;
                $endMinute = 59;
            }
            
            $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
            $endTime = sprintf('%02d:%02d:00', $endHour, $endMinute);
            
            $laborHours = round(rand(2, 8) + (rand(0, 99) / 100), 2);
            $laborRatePerHour = round(rand(50, 200) + (rand(0, 99) / 100), 2);
            $partsCost = round(rand(100, 2000) + (rand(0, 99) / 100), 2);
            $laborCost = round($laborHours * $laborRatePerHour, 2);
            $maintenanceCost = round($partsCost + $laborCost, 2);

            $maintenanceTypeId = rand(0, 1) === 1 ? $maintenanceTypePeriodicId : $maintenanceTypeEmergencyId;
            
            MaintenanceRecord::create([
                'generator_id' => $generator->id,
                'maintenance_type_id' => $maintenanceTypeId,
                'maintenance_date' => $maintenanceDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'technician_name' => $technicianNames[rand(0, count($technicianNames) - 1)],
                'work_performed' => 'تم إجراء صيانة على المولد',
                'downtime_hours' => round(rand(1, 24) + (rand(0, 99) / 100), 2),
                'parts_cost' => $partsCost,
                'labor_hours' => $laborHours,
                'labor_rate_per_hour' => $laborRatePerHour,
                'maintenance_cost' => $maintenanceCost,
            ]);
        }
        $this->command->info('✓ تم إنشاء 100 سجل صيانة للمشغلين الآخرين');

        // إنشاء 100 سجل كفاءة وقود
        $this->command->info('جاري إنشاء 100 سجل كفاءة وقود...');
        for ($i = 0; $i < 100; $i++) {
            $generator = $allGenerators->random();
            $consumptionDate = now()->subDays(rand(0, 365));

            $operatingHours = round(rand(1, 24) + (rand(0, 99) / 100), 2);
            $fuelPricePerLiter = round(rand(5, 10) + (rand(0, 99) / 100), 2);
            $fuelConsumed = round(rand(50, 500) + (rand(0, 99) / 100), 2);
            $fuelEfficiencyPercentage = round(rand(70, 95) + (rand(0, 99) / 100), 2);
            $energyDistributionEfficiency = round(rand(75, 98) + (rand(0, 99) / 100), 2);
            $totalOperatingCost = round($fuelConsumed * $fuelPricePerLiter, 2);

            $fuelEfficiencyComparisonId = $getRandomConstantId($fuelEfficiencyComparisonConstants);
            $energyEfficiencyComparisonId = $getRandomConstantId($energyEfficiencyComparisonConstants);
            
            FuelEfficiency::create([
                'operator_id' => $generator->operator_id,
                'generation_unit_id' => $generator->generation_unit_id,
                'generator_id' => $generator->id,
                'consumption_date' => $consumptionDate,
                'operating_hours' => $operatingHours,
                'fuel_price_per_liter' => $fuelPricePerLiter,
                'fuel_consumed' => $fuelConsumed,
                'fuel_efficiency_percentage' => $fuelEfficiencyPercentage,
                'fuel_efficiency_comparison_id' => $fuelEfficiencyComparisonId,
                'energy_distribution_efficiency' => $energyDistributionEfficiency,
                'energy_efficiency_comparison_id' => $energyEfficiencyComparisonId,
                'total_operating_cost' => $totalOperatingCost,
            ]);
        }
        $this->command->info('✓ تم إنشاء 100 سجل كفاءة وقود');

        // إنشاء أكثر من 100 سجل امتثال وسلامة لمشغل المملوك
        if ($mmlukOperator) {
            $this->command->info('جاري إنشاء سجلات امتثال وسلامة لمشغل المملوك...');
            $mmlukComplianceCount = 110;
            
            $inspectionAuthorities = ['وزارة البيئة', 'البلدية', 'الدفاع المدني', 'جهة مختصة'];
            $inspectionResults = ['ممتاز', 'جيد', 'مقبول', 'يحتاج تحسين'];
            
            for ($i = 0; $i < $mmlukComplianceCount; $i++) {
                $inspectionDate = now()->subDays(rand(0, 365));
                $safetyCertificateStatusId = $getRandomConstantId($safetyCertificateStatusConstants);

                ComplianceSafety::create([
                    'operator_id' => $mmlukOperator->id,
                    'safety_certificate_status_id' => $safetyCertificateStatusId,
                    'last_inspection_date' => $inspectionDate,
                    'inspection_authority' => $inspectionAuthorities[rand(0, count($inspectionAuthorities) - 1)],
                    'inspection_result' => $inspectionResults[rand(0, count($inspectionResults) - 1)],
                    'violations' => rand(0, 1) === 1 ? 'لا توجد مخالفات' : null,
                ]);
            }
            $this->command->info('✓ تم إنشاء ' . $mmlukComplianceCount . ' سجل امتثال وسلامة لمشغل المملوك');
        }

        // إنشاء 100 سجل امتثال وسلامة لباقي المشغلين
        $this->command->info('جاري إنشاء 100 سجل امتثال وسلامة للمشغلين الآخرين...');
        $otherOperators = $allOperators->filter(function($op) use ($mmlukOperator) {
            return $mmlukOperator && $op->id != $mmlukOperator->id;
        });
        $inspectionAuthorities = ['وزارة البيئة', 'البلدية', 'الدفاع المدني', 'جهة مختصة'];
        $inspectionResults = ['ممتاز', 'جيد', 'مقبول', 'يحتاج تحسين'];
        
        for ($i = 0; $i < 100; $i++) {
            $operator = $otherOperators->random();
            $inspectionDate = now()->subDays(rand(0, 365));
            $safetyCertificateStatusId = $getRandomConstantId($safetyCertificateStatusConstants);

            ComplianceSafety::create([
                'operator_id' => $operator->id,
                'safety_certificate_status_id' => $safetyCertificateStatusId,
                'last_inspection_date' => $inspectionDate,
                'inspection_authority' => $inspectionAuthorities[rand(0, count($inspectionAuthorities) - 1)],
                'inspection_result' => $inspectionResults[rand(0, count($inspectionResults) - 1)],
                'violations' => rand(0, 1) === 1 ? 'لا توجد مخالفات' : null,
            ]);
        }
        $this->command->info('✓ تم إنشاء 100 سجل امتثال وسلامة للمشغلين الآخرين');

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('تم إنشاء جميع البيانات بنجاح!');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('الملخص:');
        $this->command->info('- ' . $allOperators->count() . ' مشغل (بما في ذلك مشغل المملوك)');
        $this->command->info('- ' . $allGenerators->count() . ' مولد');
        
        $totalOperationLogs = \App\Models\OperationLog::count();
        $totalMaintenanceRecords = \App\Models\MaintenanceRecord::count();
        $totalComplianceSafeties = \App\Models\ComplianceSafety::count();
        
        $this->command->info('- ' . $totalOperationLogs . ' سجل تشغيل');
        $this->command->info('- ' . $totalMaintenanceRecords . ' سجل صيانة');
        $this->command->info('- 100 سجل كفاءة وقود');
        $this->command->info('- ' . $totalComplianceSafeties . ' سجل امتثال وسلامة');
        $this->command->info('═══════════════════════════════════════');
    }
}
