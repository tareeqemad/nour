<?php

namespace Database\Seeders;

use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use Illuminate\Database\Seeder;

class ConstantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. المحافظات
        $governorates = ConstantMaster::updateOrCreate(
            ['constant_number' => 1],
            [
                'constant_name' => 'المحافظات',
                'description' => 'قائمة المحافظات في فلسطين',
                'is_active' => true,
                'order' => 1,
            ]
        );

        // حذف المحافظات القديمة إذا كانت موجودة
        ConstantDetail::where('constant_master_id', $governorates->id)->forceDelete();

        $governorateDetails = [
            ['label' => 'شمال غزة', 'code' => 'NG', 'value' => '10', 'order' => 1],
            ['label' => 'غزة', 'code' => 'GZ', 'value' => '20', 'order' => 2],
            ['label' => 'الوسطى', 'code' => 'MD', 'value' => '30', 'order' => 3],
            ['label' => 'خانيونس', 'code' => 'KH', 'value' => '40', 'order' => 4],
            ['label' => 'رفح', 'code' => 'RF', 'value' => '50', 'order' => 5],
        ];

        $governorateModels = [];
        foreach ($governorateDetails as $detail) {
            $governorateModels[$detail['code']] = ConstantDetail::create([
                'constant_master_id' => $governorates->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 20. المدن (مرتبطة بالمحافظات)
        $cities = ConstantMaster::updateOrCreate(
            ['constant_number' => 20],
            [
                'constant_name' => 'المدينة',
                'description' => 'قائمة المدن مرتبطة بالمحافظات',
                'is_active' => true,
                'order' => 20,
            ]
        );

        // حذف المدن القديمة إذا كانت موجودة
        ConstantDetail::where('constant_master_id', $cities->id)->forceDelete();

        // شمال غزة (value: 10xx)
        $northGazaCities = [
            ['label' => 'بيت حانون', 'code' => 'BH', 'value' => '101', 'order' => 1],
            ['label' => 'بيت لاهيا', 'code' => 'BL', 'value' => '102', 'order' => 2],
            ['label' => 'جباليا', 'code' => 'JB', 'value' => '103', 'order' => 3],
        ];

        foreach ($northGazaCities as $city) {
            ConstantDetail::create([
                'constant_master_id' => $cities->id,
                'parent_detail_id' => $governorateModels['NG']->id,
                'label' => $city['label'],
                'code' => $city['code'],
                'value' => $city['value'],
                'is_active' => true,
                'order' => $city['order'],
            ]);
        }

        // غزة (value: 20xx)
        ConstantDetail::create([
            'constant_master_id' => $cities->id,
            'parent_detail_id' => $governorateModels['GZ']->id,
            'label' => 'غزة',
            'code' => 'GZ',
            'value' => '201',
            'is_active' => true,
            'order' => 1,
        ]);

        // محافظة الوسطى (value: 30xx)
        $middleCities = [
            ['label' => 'دير البلح', 'code' => 'DB', 'value' => '301', 'order' => 1],
            ['label' => 'النصيرات', 'code' => 'NS', 'value' => '302', 'order' => 2],
            ['label' => 'البريج', 'code' => 'BR', 'value' => '303', 'order' => 3],
            ['label' => 'المغازي', 'code' => 'MG', 'value' => '304', 'order' => 4],
            ['label' => 'الزوايدة', 'code' => 'ZW', 'value' => '305', 'order' => 5],
        ];

        foreach ($middleCities as $city) {
            ConstantDetail::create([
                'constant_master_id' => $cities->id,
                'parent_detail_id' => $governorateModels['MD']->id,
                'label' => $city['label'],
                'code' => $city['code'],
                'value' => $city['value'],
                'is_active' => true,
                'order' => $city['order'],
            ]);
        }

        // خانيونس (value: 40xx)
        $khanYunisCities = [
            ['label' => 'خانيونس', 'code' => 'KH', 'value' => '401', 'order' => 1],
            ['label' => 'القرارة', 'code' => 'QR', 'value' => '402', 'order' => 2],
            ['label' => 'عبسان الكبيرة', 'code' => 'AK', 'value' => '403', 'order' => 3],
            ['label' => 'عبسان الصغيرة', 'code' => 'AS', 'value' => '404', 'order' => 4],
            ['label' => 'خزاعة', 'code' => 'KZ', 'value' => '405', 'order' => 5],
        ];

        foreach ($khanYunisCities as $city) {
            ConstantDetail::create([
                'constant_master_id' => $cities->id,
                'parent_detail_id' => $governorateModels['KH']->id,
                'label' => $city['label'],
                'code' => $city['code'],
                'value' => $city['value'],
                'is_active' => true,
                'order' => $city['order'],
            ]);
        }

        // رفح (value: 50xx)
        $rafahCities = [
            ['label' => 'رفح', 'code' => 'RF', 'value' => '501', 'order' => 1],
            ['label' => 'الشوكة', 'code' => 'SH', 'value' => '502', 'order' => 2],
            ['label' => 'النصر', 'code' => 'NA', 'value' => '503', 'order' => 3],
        ];

        foreach ($rafahCities as $city) {
            ConstantDetail::create([
                'constant_master_id' => $cities->id,
                'parent_detail_id' => $governorateModels['RF']->id,
                'label' => $city['label'],
                'code' => $city['code'],
                'value' => $city['value'],
                'is_active' => true,
                'order' => $city['order'],
            ]);
        }

        // 2. جهة التشغيل
        $operationEntity = ConstantMaster::updateOrCreate(
            ['constant_number' => 2],
            [
                'constant_name' => 'جهة التشغيل',
                'description' => 'جهة تشغيل المولد',
                'is_active' => true,
                'order' => 2,
            ]
        );

        $operationEntityDetails = [
            ['label' => 'نفس المالك', 'code' => 'SAME_OWNER', 'value' => '201', 'order' => 1],
            ['label' => 'طرف آخر', 'code' => 'OTHER_PARTY', 'value' => '202', 'order' => 2],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $operationEntity->id)->forceDelete();
        
        foreach ($operationEntityDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $operationEntity->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 3. حالة المولد
        $generatorStatus = ConstantMaster::updateOrCreate(
            ['constant_number' => 3],
            [
                'constant_name' => 'حالة المولد',
                'description' => 'حالة المولد (فعال، غير فعال)',
                'is_active' => true,
                'order' => 3,
            ]
        );

        $generatorStatusDetails = [
            ['label' => 'فعال', 'code' => 'ACTIVE', 'value' => '301', 'order' => 1],
            ['label' => 'غير فعال', 'code' => 'INACTIVE', 'value' => '302', 'order' => 2],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $generatorStatus->id)->forceDelete();
        
        foreach ($generatorStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $generatorStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 4. نوع المحرك
        $engineType = ConstantMaster::updateOrCreate(
            ['constant_number' => 4],
            [
                'constant_name' => 'نوع المحرك',
                'description' => 'نوع محرك المولد',
                'is_active' => true,
                'order' => 4,
            ]
        );

        $engineTypeDetails = [
            ['label' => 'Perkins', 'code' => 'PERKINS', 'value' => '401', 'order' => 1],
            ['label' => 'Volvo', 'code' => 'VOLVO', 'value' => '402', 'order' => 2],
            ['label' => 'Caterpillar', 'code' => 'CATERPILLAR', 'value' => '403', 'order' => 3],
            ['label' => 'DAF', 'code' => 'DAF', 'value' => '404', 'order' => 4],
            ['label' => 'MAN', 'code' => 'MAN', 'value' => '405', 'order' => 5],
            ['label' => 'SCAINA', 'code' => 'SCAINA', 'value' => '406', 'order' => 6],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $engineType->id)->forceDelete();

        foreach ($engineTypeDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $engineType->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 5. نظام الحقن
        $injectionSystem = ConstantMaster::updateOrCreate(
            ['constant_number' => 5],
            [
                'constant_name' => 'نظام الحقن',
                'description' => 'نوع نظام الحقن',
                'is_active' => true,
                'order' => 5,
            ]
        );

        $injectionSystemDetails = [
            ['label' => 'ميكانيكي', 'code' => 'MECHANICAL', 'value' => '501', 'order' => 1],
            ['label' => 'الكتروني', 'code' => 'ELECTRONIC', 'value' => '502', 'order' => 2],
            ['label' => 'هجين', 'code' => 'HYBRID', 'value' => '503', 'order' => 3],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $injectionSystem->id)->forceDelete();

        foreach ($injectionSystemDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $injectionSystem->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 6. مؤشر القياس
        $measurementIndicator = ConstantMaster::updateOrCreate(
            ['constant_number' => 6],
            [
                'constant_name' => 'مؤشر القياس',
                'description' => 'نوع مؤشر القياس',
                'is_active' => true,
                'order' => 6,
            ]
        );

        $measurementIndicatorDetails = [
            ['label' => 'غير متوفر', 'code' => 'NOT_AVAILABLE', 'value' => '601', 'order' => 1],
            ['label' => 'متوفر ويعمل', 'code' => 'AVAILABLE_WORKING', 'value' => '602', 'order' => 2],
            ['label' => 'متوفر ولا يعمل', 'code' => 'AVAILABLE_NOT_WORKING', 'value' => '603', 'order' => 3],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $measurementIndicator->id)->forceDelete();

        foreach ($measurementIndicatorDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $measurementIndicator->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 7. الحالة الفنية
        $technicalCondition = ConstantMaster::updateOrCreate(
            ['constant_number' => 7],
            [
                'constant_name' => 'الحالة الفنية',
                'description' => 'الحالة الفنية للمولد',
                'is_active' => true,
                'order' => 7,
            ]
        );

        $technicalConditionDetails = [
            ['label' => 'ممتازة', 'code' => 'EXCELLENT', 'value' => '701', 'order' => 1],
            ['label' => 'جيدة جدا', 'code' => 'VERY_GOOD', 'value' => '702', 'order' => 2],
            ['label' => 'جيدة', 'code' => 'GOOD', 'value' => '703', 'order' => 3],
            ['label' => 'متوسطة', 'code' => 'FAIR', 'value' => '704', 'order' => 4],
            ['label' => 'سيئة', 'code' => 'POOR', 'value' => '705', 'order' => 5],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $technicalCondition->id)->forceDelete();

        foreach ($technicalConditionDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $technicalCondition->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 8. نوع لوحة التحكم
        $controlPanelType = ConstantMaster::updateOrCreate(
            ['constant_number' => 8],
            [
                'constant_name' => 'نوع لوحة التحكم',
                'description' => 'نوع لوحة التحكم',
                'is_active' => true,
                'order' => 8,
            ]
        );

        $controlPanelTypeDetails = [
            ['label' => 'Deep Sea', 'code' => 'DEEP_SEA', 'value' => '801', 'order' => 1],
            ['label' => 'ComAp', 'code' => 'COMAP', 'value' => '802', 'order' => 2],
            ['label' => 'Datakom', 'code' => 'DATAKOM', 'value' => '803', 'order' => 3],
            ['label' => 'Analog', 'code' => 'ANALOG', 'value' => '804', 'order' => 4],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $controlPanelType->id)->forceDelete();

        foreach ($controlPanelTypeDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $controlPanelType->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 9. حالة لوحة التحكم
        $controlPanelStatus = ConstantMaster::updateOrCreate(
            ['constant_number' => 9],
            [
                'constant_name' => 'حالة لوحة التحكم',
                'description' => 'حالة لوحة التحكم',
                'is_active' => true,
                'order' => 9,
            ]
        );

        $controlPanelStatusDetails = [
            ['label' => 'تعمل', 'code' => 'WORKING', 'value' => '901', 'order' => 1],
            ['label' => 'لا تعمل', 'code' => 'NOT_WORKING', 'value' => '902', 'order' => 2],
            ['label' => 'تحتاج الى اصلاح', 'code' => 'NEEDS_REPAIR', 'value' => '903', 'order' => 3],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $controlPanelStatus->id)->forceDelete();

        foreach ($controlPanelStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $controlPanelStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 10. مادة التصنيع (خزانات الوقود)
        $material = ConstantMaster::updateOrCreate(
            ['constant_number' => 10],
            [
                'constant_name' => 'مادة التصنيع',
                'description' => 'مادة تصنيع خزانات الوقود',
                'is_active' => true,
                'order' => 10,
            ]
        );

        $materialDetails = [
            ['label' => 'حديد', 'code' => 'STEEL', 'value' => '1001', 'order' => 1],
            ['label' => 'بلاستيك مقوى', 'code' => 'REINFORCED_PLASTIC', 'value' => '1002', 'order' => 2],
            ['label' => 'فايبر', 'code' => 'FIBER', 'value' => '1003', 'order' => 3],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $material->id)->forceDelete();

        foreach ($materialDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $material->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 11. الاستخدام (خزانات الوقود)
        $usage = ConstantMaster::updateOrCreate(
            ['constant_number' => 11],
            [
                'constant_name' => 'الاستخدام',
                'description' => 'نوع استخدام خزان الوقود',
                'is_active' => true,
                'order' => 11,
            ]
        );

        $usageDetails = [
            ['label' => 'مركزي', 'code' => 'CENTRAL', 'value' => '1101', 'order' => 1],
            ['label' => 'احتياطي', 'code' => 'RESERVE', 'value' => '1102', 'order' => 2],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $usage->id)->forceDelete();

        foreach ($usageDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $usage->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 12. نوع الصيانة
        $maintenanceType = ConstantMaster::updateOrCreate(
            ['constant_number' => 12],
            [
                'constant_name' => 'نوع الصيانة',
                'description' => 'نوع عملية الصيانة',
                'is_active' => true,
                'order' => 12,
            ]
        );

        $maintenanceTypeDetails = [
            ['label' => 'صيانة دورية', 'code' => 'PERIODIC', 'value' => '1201', 'order' => 1],
            ['label' => 'صيانة طارئة', 'code' => 'EMERGENCY', 'value' => '1202', 'order' => 2],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $maintenanceType->id)->forceDelete();

        foreach ($maintenanceTypeDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $maintenanceType->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 13. حالة شهادة السلامة
        $safetyCertificateStatus = ConstantMaster::updateOrCreate(
            ['constant_number' => 13],
            [
                'constant_name' => 'حالة شهادة السلامة',
                'description' => 'حالة شهادة السلامة',
                'is_active' => true,
                'order' => 13,
            ]
        );

        $safetyCertificateStatusDetails = [
            ['label' => 'صالحة', 'code' => 'VALID', 'value' => '1301', 'order' => 1],
            ['label' => 'منتهية', 'code' => 'EXPIRED', 'value' => '1302', 'order' => 2],
            ['label' => 'غير موجودة', 'code' => 'MISSING', 'value' => '1303', 'order' => 3],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $safetyCertificateStatus->id)->forceDelete();

        foreach ($safetyCertificateStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $safetyCertificateStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 14. حالة الامتثال البيئي
        $environmentalComplianceStatus = ConstantMaster::updateOrCreate(
            ['constant_number' => 14],
            [
                'constant_name' => 'حالة الامتثال البيئي',
                'description' => 'حالة الامتثال البيئي',
                'is_active' => true,
                'order' => 14,
            ]
        );

        $environmentalComplianceStatusDetails = [
            ['label' => 'ملتزم', 'code' => 'COMPLIANT', 'value' => '1401', 'order' => 1],
            ['label' => 'تحت المراقبة', 'code' => 'UNDER_MONITORING', 'value' => '1402', 'order' => 2],
            ['label' => 'تحت التقييم', 'code' => 'UNDER_EVALUATION', 'value' => '1403', 'order' => 3],
            ['label' => 'غير ملتزم', 'code' => 'NON_COMPLIANT', 'value' => '1404', 'order' => 4],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $environmentalComplianceStatus->id)->forceDelete();

        foreach ($environmentalComplianceStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $environmentalComplianceStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 15. حالة الوحدة
        $unitStatus = ConstantMaster::updateOrCreate(
            ['constant_number' => 15],
            [
                'constant_name' => 'حالة الوحدة',
                'description' => 'حالة وحدة التوليد (فعال/غير فعال)',
                'is_active' => true,
                'order' => 15,
            ]
        );

        $unitStatusDetails = [
            ['label' => 'فعال', 'code' => 'ACTIVE', 'value' => '1501', 'order' => 1],
            ['label' => 'غير فعال', 'code' => 'INACTIVE', 'value' => '1502', 'order' => 2],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $unitStatus->id)->forceDelete();

        foreach ($unitStatusDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $unitStatus->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 16. إمكانية المزامنة
        $synchronizationAvailable = ConstantMaster::updateOrCreate(
            ['constant_number' => 16],
            [
                'constant_name' => 'إمكانية المزامنة',
                'description' => 'إمكانية مزامنة المولدات',
                'is_active' => true,
                'order' => 16,
            ]
        );

        $synchronizationAvailableDetails = [
            ['label' => 'متوفرة', 'code' => 'AVAILABLE', 'value' => '1601', 'order' => 1],
            ['label' => 'غير متوفرة', 'code' => 'NOT_AVAILABLE', 'value' => '1602', 'order' => 2],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $synchronizationAvailable->id)->forceDelete();

        foreach ($synchronizationAvailableDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $synchronizationAvailable->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 17. مقارنة كفاءة الوقود
        $fuelEfficiencyComparison = ConstantMaster::updateOrCreate(
            ['constant_number' => 17],
            [
                'constant_name' => 'مقارنة كفاءة الوقود',
                'description' => 'مقارنة كفاءة الوقود',
                'is_active' => true,
                'order' => 17,
            ]
        );

        $fuelEfficiencyComparisonDetails = [
            ['label' => 'ضمن المعدل', 'code' => 'WITHIN_RANGE', 'value' => '1701', 'order' => 1],
            ['label' => 'اعلى من المعدل', 'code' => 'ABOVE_RANGE', 'value' => '1702', 'order' => 2],
            ['label' => 'اقل من المعدل', 'code' => 'BELOW_RANGE', 'value' => '1703', 'order' => 3],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $fuelEfficiencyComparison->id)->forceDelete();

        foreach ($fuelEfficiencyComparisonDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $fuelEfficiencyComparison->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 18. مقارنة كفاءة الطاقة
        $energyEfficiencyComparison = ConstantMaster::updateOrCreate(
            ['constant_number' => 18],
            [
                'constant_name' => 'مقارنة كفاءة الطاقة',
                'description' => 'مقارنة كفاءة الطاقة',
                'is_active' => true,
                'order' => 18,
            ]
        );

        $energyEfficiencyComparisonDetails = [
            ['label' => 'ضمن المعدل', 'code' => 'WITHIN_RANGE', 'value' => '1801', 'order' => 1],
            ['label' => 'اعلى من المعدل', 'code' => 'ABOVE_RANGE', 'value' => '1802', 'order' => 2],
            ['label' => 'اقل من المعدل', 'code' => 'BELOW_RANGE', 'value' => '1803', 'order' => 3],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $energyEfficiencyComparison->id)->forceDelete();

        foreach ($energyEfficiencyComparisonDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $energyEfficiencyComparison->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 21. موقع الخزان
        $tankLocation = ConstantMaster::updateOrCreate(
            ['constant_number' => 21],
            [
                'constant_name' => 'موقع الخزان',
                'description' => 'موقع خزان الوقود',
                'is_active' => true,
                'order' => 21,
            ]
        );

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $tankLocation->id)->forceDelete();

        $tankLocationDetails = [
            ['label' => 'أرضي', 'code' => 'GROUND', 'value' => '2101', 'order' => 1],
            ['label' => 'علوي', 'code' => 'OVERHEAD', 'value' => '2102', 'order' => 2],
            ['label' => 'تحت الأرض', 'code' => 'UNDERGROUND', 'value' => '2103', 'order' => 3],
        ];

        foreach ($tankLocationDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $tankLocation->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 19. طريقة القياس
        $measurementMethod = ConstantMaster::updateOrCreate(
            ['constant_number' => 19],
            [
                'constant_name' => 'طريقة القياس',
                'description' => 'طريقة قياس مستوى الوقود في الخزان',
                'is_active' => true,
                'order' => 19,
            ]
        );

        $measurementMethodDetails = [
            ['label' => 'سيخ مدرج', 'code' => 'DIPSTICK_GAUGE', 'value' => '1901', 'order' => 1],
            ['label' => 'ساعة ميكانيكية', 'code' => 'MECHANICAL_METER', 'value' => '1902', 'order' => 2],
            ['label' => 'حساس الكتروني', 'code' => 'ELECTRONIC_SENSOR', 'value' => '1903', 'order' => 3],
            ['label' => 'خرطوم شفاف', 'code' => 'TRANSPARENT_HOSE', 'value' => '1904', 'order' => 4],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $measurementMethod->id)->forceDelete();

        foreach ($measurementMethodDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $measurementMethod->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        // 22. حالة الخزان
        $tankCondition = ConstantMaster::updateOrCreate(
            ['constant_number' => 22],
            [
                'constant_name' => 'حالة الخزان',
                'description' => 'حالة خزان الوقود',
                'is_active' => true,
                'order' => 22,
            ]
        );

        $tankConditionDetails = [
            ['label' => 'ممتازة', 'code' => 'EXCELLENT', 'value' => '2201', 'order' => 1],
            ['label' => 'جيدة', 'code' => 'GOOD', 'value' => '2202', 'order' => 2],
            ['label' => 'متوسطة', 'code' => 'FAIR', 'value' => '2203', 'order' => 3],
            ['label' => 'سيئة', 'code' => 'POOR', 'value' => '2204', 'order' => 4],
            ['label' => 'تحتاج إصلاح', 'code' => 'NEEDS_REPAIR', 'value' => '2205', 'order' => 5],
        ];

        // حذف التفاصيل القديمة
        ConstantDetail::where('constant_master_id', $tankCondition->id)->forceDelete();

        foreach ($tankConditionDetails as $detail) {
            ConstantDetail::create([
                'constant_master_id' => $tankCondition->id,
                'label' => $detail['label'],
                'code' => $detail['code'],
                'value' => $detail['value'],
                'is_active' => true,
                'order' => $detail['order'],
            ]);
        }

        $this->command->info('تم إنشاء الثوابت بنجاح!');
    }
}
