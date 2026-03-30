<?php

namespace Database\Seeders;

use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use Illuminate\Database\Seeder;

class ConstantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. المحافظات
        $governorates = $this->upsertMaster(
            1,
            'المحافظات',
            'قائمة المحافظات في فلسطين',
            1
        );

        $governorateDetails = [
            ['label' => 'شمال غزة', 'code' => 'NG', 'value' => '10', 'order' => 1],
            ['label' => 'غزة', 'code' => 'GZ', 'value' => '20', 'order' => 2],
            ['label' => 'الوسطى', 'code' => 'MD', 'value' => '30', 'order' => 3],
            ['label' => 'خانيونس', 'code' => 'KH', 'value' => '40', 'order' => 4],
            ['label' => 'رفح', 'code' => 'RF', 'value' => '50', 'order' => 5],
        ];

        $governorateModels = [];
        foreach ($governorateDetails as $detail) {
            $governorateModels[$detail['code']] = $this->upsertDetail(
                $governorates->id,
                $detail['code'],
                $detail['label'],
                $detail['value'],
                $detail['order']
            );
        }

        // 20. المدن
        $cities = $this->upsertMaster(
            20,
            'المدينة',
            'قائمة المدن مرتبطة بالمحافظات',
            20
        );

        $northGazaCities = [
            ['label' => 'بيت حانون', 'code' => 'BH', 'value' => '101', 'order' => 1],
            ['label' => 'بيت لاهيا', 'code' => 'BL', 'value' => '102', 'order' => 2],
            ['label' => 'جباليا', 'code' => 'JB', 'value' => '103', 'order' => 3],
        ];

        foreach ($northGazaCities as $city) {
            $this->upsertDetail(
                $cities->id,
                $city['code'],
                $city['label'],
                $city['value'],
                $city['order'],
                $governorateModels['NG']->id
            );
        }

        $this->upsertDetail(
            $cities->id,
            'GZ',
            'غزة',
            '201',
            1,
            $governorateModels['GZ']->id
        );

        $middleCities = [
            ['label' => 'دير البلح', 'code' => 'DB', 'value' => '301', 'order' => 1],
            ['label' => 'النصيرات', 'code' => 'NS', 'value' => '302', 'order' => 2],
            ['label' => 'البريج', 'code' => 'BR', 'value' => '303', 'order' => 3],
            ['label' => 'المغازي', 'code' => 'MG', 'value' => '304', 'order' => 4],
            ['label' => 'الزوايدة', 'code' => 'ZW', 'value' => '305', 'order' => 5],
        ];

        foreach ($middleCities as $city) {
            $this->upsertDetail(
                $cities->id,
                $city['code'],
                $city['label'],
                $city['value'],
                $city['order'],
                $governorateModels['MD']->id
            );
        }

        $khanYunisCities = [
            ['label' => 'خانيونس', 'code' => 'KH', 'value' => '401', 'order' => 1],
            ['label' => 'القرارة', 'code' => 'QR', 'value' => '402', 'order' => 2],
            ['label' => 'عبسان الكبيرة', 'code' => 'AK', 'value' => '403', 'order' => 3],
            ['label' => 'عبسان الصغيرة', 'code' => 'AS', 'value' => '404', 'order' => 4],
            ['label' => 'خزاعة', 'code' => 'KZ', 'value' => '405', 'order' => 5],
        ];

        foreach ($khanYunisCities as $city) {
            $this->upsertDetail(
                $cities->id,
                $city['code'],
                $city['label'],
                $city['value'],
                $city['order'],
                $governorateModels['KH']->id
            );
        }

        $rafahCities = [
            ['label' => 'رفح', 'code' => 'RF', 'value' => '501', 'order' => 1],
            ['label' => 'الشوكة', 'code' => 'SH', 'value' => '502', 'order' => 2],
            ['label' => 'النصر', 'code' => 'NA', 'value' => '503', 'order' => 3],
        ];

        foreach ($rafahCities as $city) {
            $this->upsertDetail(
                $cities->id,
                $city['code'],
                $city['label'],
                $city['value'],
                $city['order'],
                $governorateModels['RF']->id
            );
        }

        // 2. جهة التشغيل
        $operationEntity = $this->upsertMaster(2, 'جهة التشغيل', 'جهة تشغيل المولد', 2);
        $this->seedDetails($operationEntity->id, [
            ['label' => 'نفس المالك', 'code' => 'SAME_OWNER', 'value' => '201', 'order' => 1],
            ['label' => 'طرف آخر', 'code' => 'OTHER_PARTY', 'value' => '202', 'order' => 2],
        ]);

        // 3. حالة المولد
        $generatorStatus = $this->upsertMaster(3, 'حالة المولد', 'حالة المولد (فعال، غير فعال)', 3);
        $this->seedDetails($generatorStatus->id, [
            ['label' => 'فعال', 'code' => 'ACTIVE', 'value' => '301', 'order' => 1],
            ['label' => 'غير فعال', 'code' => 'INACTIVE', 'value' => '302', 'order' => 2],
        ]);

        // 4. نوع المحرك
        $engineType = $this->upsertMaster(4, 'نوع المحرك', 'نوع محرك المولد', 4);
        $this->seedDetails($engineType->id, [
            ['label' => 'Perkins', 'code' => 'PERKINS', 'value' => '401', 'order' => 1],
            ['label' => 'Volvo', 'code' => 'VOLVO', 'value' => '402', 'order' => 2],
            ['label' => 'Caterpillar', 'code' => 'CATERPILLAR', 'value' => '403', 'order' => 3],
            ['label' => 'DAF', 'code' => 'DAF', 'value' => '404', 'order' => 4],
            ['label' => 'MAN', 'code' => 'MAN', 'value' => '405', 'order' => 5],
            ['label' => 'SCAINA', 'code' => 'SCAINA', 'value' => '406', 'order' => 6],
        ]);

        // 5. نظام الحقن
        $injectionSystem = $this->upsertMaster(5, 'نظام الحقن', 'نوع نظام الحقن', 5);
        $this->seedDetails($injectionSystem->id, [
            ['label' => 'ميكانيكي', 'code' => 'MECHANICAL', 'value' => '501', 'order' => 1],
            ['label' => 'الكتروني', 'code' => 'ELECTRONIC', 'value' => '502', 'order' => 2],
            ['label' => 'هجين', 'code' => 'HYBRID', 'value' => '503', 'order' => 3],
        ]);

        // 6. مؤشر القياس
        $measurementIndicator = $this->upsertMaster(6, 'مؤشر القياس', 'نوع مؤشر القياس', 6);
        $this->seedDetails($measurementIndicator->id, [
            ['label' => 'غير متوفر', 'code' => 'NOT_AVAILABLE', 'value' => '601', 'order' => 1],
            ['label' => 'متوفر ويعمل', 'code' => 'AVAILABLE_WORKING', 'value' => '602', 'order' => 2],
            ['label' => 'متوفر ولا يعمل', 'code' => 'AVAILABLE_NOT_WORKING', 'value' => '603', 'order' => 3],
        ]);

        // 7. الحالة الفنية
        $technicalCondition = $this->upsertMaster(7, 'الحالة الفنية', 'الحالة الفنية للمولد', 7);
        $this->seedDetails($technicalCondition->id, [
            ['label' => 'ممتازة', 'code' => 'EXCELLENT', 'value' => '701', 'order' => 1],
            ['label' => 'جيدة جدا', 'code' => 'VERY_GOOD', 'value' => '702', 'order' => 2],
            ['label' => 'جيدة', 'code' => 'GOOD', 'value' => '703', 'order' => 3],
            ['label' => 'متوسطة', 'code' => 'FAIR', 'value' => '704', 'order' => 4],
            ['label' => 'سيئة', 'code' => 'POOR', 'value' => '705', 'order' => 5],
        ]);

        // 8. نوع لوحة التحكم
        $controlPanelType = $this->upsertMaster(8, 'نوع لوحة التحكم', 'نوع لوحة التحكم', 8);
        $this->seedDetails($controlPanelType->id, [
            ['label' => 'Deep Sea', 'code' => 'DEEP_SEA', 'value' => '801', 'order' => 1],
            ['label' => 'ComAp', 'code' => 'COMAP', 'value' => '802', 'order' => 2],
            ['label' => 'Datakom', 'code' => 'DATAKOM', 'value' => '803', 'order' => 3],
            ['label' => 'Analog', 'code' => 'ANALOG', 'value' => '804', 'order' => 4],
        ]);

        // 9. حالة لوحة التحكم
        $controlPanelStatus = $this->upsertMaster(9, 'حالة لوحة التحكم', 'حالة لوحة التحكم', 9);
        $this->seedDetails($controlPanelStatus->id, [
            ['label' => 'تعمل', 'code' => 'WORKING', 'value' => '901', 'order' => 1],
            ['label' => 'لا تعمل', 'code' => 'NOT_WORKING', 'value' => '902', 'order' => 2],
            ['label' => 'تحتاج الى اصلاح', 'code' => 'NEEDS_REPAIR', 'value' => '903', 'order' => 3],
        ]);

        // 10. مادة التصنيع
        $material = $this->upsertMaster(10, 'مادة التصنيع', 'مادة تصنيع خزانات الوقود', 10);
        $this->seedDetails($material->id, [
            ['label' => 'حديد', 'code' => 'STEEL', 'value' => '1001', 'order' => 1],
            ['label' => 'بلاستيك مقوى', 'code' => 'REINFORCED_PLASTIC', 'value' => '1002', 'order' => 2],
            ['label' => 'فايبر', 'code' => 'FIBER', 'value' => '1003', 'order' => 3],
        ]);

        // 11. الاستخدام
        $usage = $this->upsertMaster(11, 'الاستخدام', 'نوع استخدام خزان الوقود', 11);
        $this->seedDetails($usage->id, [
            ['label' => 'مركزي', 'code' => 'CENTRAL', 'value' => '1101', 'order' => 1],
            ['label' => 'احتياطي', 'code' => 'RESERVE', 'value' => '1102', 'order' => 2],
        ]);

        // 12. نوع الصيانة
        $maintenanceType = $this->upsertMaster(12, 'نوع الصيانة', 'نوع عملية الصيانة', 12);
        $this->seedDetails($maintenanceType->id, [
            ['label' => 'صيانة دورية', 'code' => 'PERIODIC', 'value' => '1201', 'order' => 1],
            ['label' => 'صيانة طارئة', 'code' => 'EMERGENCY', 'value' => '1202', 'order' => 2],
        ]);

        // 13. حالة شهادة السلامة
        $safetyCertificateStatus = $this->upsertMaster(13, 'حالة شهادة السلامة', 'حالة شهادة السلامة', 13);
        $this->seedDetails($safetyCertificateStatus->id, [
            ['label' => 'صالحة', 'code' => 'VALID', 'value' => '1301', 'order' => 1],
            ['label' => 'منتهية', 'code' => 'EXPIRED', 'value' => '1302', 'order' => 2],
            ['label' => 'غير موجودة', 'code' => 'MISSING', 'value' => '1303', 'order' => 3],
        ]);

        // 14. حالة الامتثال البيئي
        $environmentalComplianceStatus = $this->upsertMaster(14, 'حالة الامتثال البيئي', 'حالة الامتثال البيئي', 14);
        $this->seedDetails($environmentalComplianceStatus->id, [
            ['label' => 'ملتزم', 'code' => 'COMPLIANT', 'value' => '1401', 'order' => 1],
            ['label' => 'تحت المراقبة', 'code' => 'UNDER_MONITORING', 'value' => '1402', 'order' => 2],
            ['label' => 'تحت التقييم', 'code' => 'UNDER_EVALUATION', 'value' => '1403', 'order' => 3],
            ['label' => 'غير ملتزم', 'code' => 'NON_COMPLIANT', 'value' => '1404', 'order' => 4],
        ]);

        // 15. حالة الوحدة
        $unitStatus = $this->upsertMaster(15, 'حالة الوحدة', 'حالة وحدة التوليد (فعال/غير فعال)', 15);
        $this->seedDetails($unitStatus->id, [
            ['label' => 'فعال', 'code' => 'ACTIVE', 'value' => '1501', 'order' => 1],
            ['label' => 'غير فعال', 'code' => 'INACTIVE', 'value' => '1502', 'order' => 2],
        ]);

        // 16. إمكانية المزامنة
        $synchronizationAvailable = $this->upsertMaster(16, 'إمكانية المزامنة', 'إمكانية مزامنة المولدات', 16);
        $this->seedDetails($synchronizationAvailable->id, [
            ['label' => 'متوفرة', 'code' => 'AVAILABLE', 'value' => '1601', 'order' => 1],
            ['label' => 'غير متوفرة', 'code' => 'NOT_AVAILABLE', 'value' => '1602', 'order' => 2],
        ]);

        // 17. مقارنة كفاءة الوقود
        $fuelEfficiencyComparison = $this->upsertMaster(17, 'مقارنة كفاءة الوقود', 'مقارنة كفاءة الوقود', 17);
        $this->seedDetails($fuelEfficiencyComparison->id, [
            ['label' => 'ضمن المعدل', 'code' => 'WITHIN_RANGE', 'value' => '1701', 'order' => 1],
            ['label' => 'اعلى من المعدل', 'code' => 'ABOVE_RANGE', 'value' => '1702', 'order' => 2],
            ['label' => 'اقل من المعدل', 'code' => 'BELOW_RANGE', 'value' => '1703', 'order' => 3],
        ]);

        // 18. مقارنة كفاءة الطاقة
        $energyEfficiencyComparison = $this->upsertMaster(18, 'مقارنة كفاءة الطاقة', 'مقارنة كفاءة الطاقة', 18);
        $this->seedDetails($energyEfficiencyComparison->id, [
            ['label' => 'ضمن المعدل', 'code' => 'WITHIN_RANGE', 'value' => '1801', 'order' => 1],
            ['label' => 'اعلى من المعدل', 'code' => 'ABOVE_RANGE', 'value' => '1802', 'order' => 2],
            ['label' => 'اقل من المعدل', 'code' => 'BELOW_RANGE', 'value' => '1803', 'order' => 3],
        ]);

        // 19. طريقة القياس
        $measurementMethod = $this->upsertMaster(19, 'طريقة القياس', 'طريقة قياس مستوى الوقود في الخزان', 19);
        $this->seedDetails($measurementMethod->id, [
            ['label' => 'سيخ مدرج', 'code' => 'DIPSTICK_GAUGE', 'value' => '1901', 'order' => 1],
            ['label' => 'ساعة ميكانيكية', 'code' => 'MECHANICAL_METER', 'value' => '1902', 'order' => 2],
            ['label' => 'حساس الكتروني', 'code' => 'ELECTRONIC_SENSOR', 'value' => '1903', 'order' => 3],
            ['label' => 'خرطوم شفاف', 'code' => 'TRANSPARENT_HOSE', 'value' => '1904', 'order' => 4],
        ]);

        // 21. موقع الخزان
        $tankLocation = $this->upsertMaster(21, 'موقع الخزان', 'موقع خزان الوقود', 21);
        $this->seedDetails($tankLocation->id, [
            ['label' => 'أرضي', 'code' => 'GROUND', 'value' => '2101', 'order' => 1],
            ['label' => 'علوي', 'code' => 'OVERHEAD', 'value' => '2102', 'order' => 2],
            ['label' => 'تحت الأرض', 'code' => 'UNDERGROUND', 'value' => '2103', 'order' => 3],
        ]);

        // 22. حالة الخزان
        $tankCondition = $this->upsertMaster(22, 'حالة الخزان', 'حالة خزان الوقود', 22);
        $this->seedDetails($tankCondition->id, [
            ['label' => 'ممتازة', 'code' => 'EXCELLENT', 'value' => '2201', 'order' => 1],
            ['label' => 'جيدة', 'code' => 'GOOD', 'value' => '2202', 'order' => 2],
            ['label' => 'متوسطة', 'code' => 'FAIR', 'value' => '2203', 'order' => 3],
            ['label' => 'سيئة', 'code' => 'POOR', 'value' => '2204', 'order' => 4],
            ['label' => 'تحتاج إصلاح', 'code' => 'NEEDS_REPAIR', 'value' => '2205', 'order' => 5],
        ]);

        // 23. تصنيف الاشتراك
        $subscriptionCategory = $this->upsertMaster(23, 'تصنيف الاشتراك', 'تصنيف اشتراك المشترك (منزلي، تجاري، خدماتي، صناعي)', 23);
        $this->seedDetails($subscriptionCategory->id, [
            ['label' => 'منزلي', 'code' => 'RESIDENTIAL', 'value' => '1', 'order' => 1],
            ['label' => 'تجاري', 'code' => 'COMMERCIAL', 'value' => '2', 'order' => 2],
            ['label' => 'خدماتي', 'code' => 'SERVICE', 'value' => '3', 'order' => 3],
            ['label' => 'صناعي', 'code' => 'INDUSTRIAL', 'value' => '4', 'order' => 4],
        ]);

        // 24. نوع الفاز
        $phaseType = $this->upsertMaster(24, 'نوع الفاز', 'نوع الفاز الكهربائي للاشتراك (1 فاز / 3 فاز)', 24);
        $this->seedDetails($phaseType->id, [
            ['label' => '1 فاز', 'code' => 'SINGLE_PHASE', 'value' => '1', 'order' => 1],
            ['label' => '3 فاز', 'code' => 'THREE_PHASE', 'value' => '2', 'order' => 2],
        ]);

        // 25. حالة الاشتراك
        $subscriptionStatus = $this->upsertMaster(25, 'حالة الاشتراك', 'حالة اشتراك المشترك (نشط، موقوف، مغلق)', 25);
        $this->seedDetails($subscriptionStatus->id, [
            ['label' => 'نشط', 'code' => 'ACTIVE', 'value' => '1', 'order' => 1],
            ['label' => 'موقوف', 'code' => 'SUSPENDED', 'value' => '2', 'order' => 2],
            ['label' => 'مغلق', 'code' => 'CLOSED', 'value' => '3', 'order' => 3],
        ]);

        // 26. نوع الخدمة
        $serviceType = $this->upsertMaster(26, 'نوع الخدمة', 'نوع مصدر الطاقة للمشترك (مولد، شمسي، هجين)', 26);
        $this->seedDetails($serviceType->id, [
            ['label' => 'مولّد', 'code' => 'GENERATOR', 'value' => '1', 'order' => 1],
            ['label' => 'شمسي', 'code' => 'SOLAR', 'value' => '2', 'order' => 2],
            ['label' => 'هجين', 'code' => 'HYBRID', 'value' => '3', 'order' => 3],
        ]);

        // 27. نوع المشترك
        $subscriberType = $this->upsertMaster(27, 'نوع المشترك', 'هل المشترك موظف شركة أم مشترك عادي', 27);
        $this->seedDetails($subscriberType->id, [
            ['label' => 'مشترك عادي', 'code' => 'REGULAR', 'value' => '0', 'order' => 1],
            ['label' => 'موظف شركة', 'code' => 'EMPLOYEE', 'value' => '1', 'order' => 2],
        ]);

        // 28. حالة القراءة
        $readingStatus = $this->upsertMaster(28, 'حالة القراءة', 'حالة قراءة العداد (طبيعية / غير طبيعية)', 28);
        $this->seedDetails($readingStatus->id, [
            ['label' => 'طبيعية', 'code' => 'NORMAL', 'value' => '1', 'order' => 1],
            ['label' => 'غير طبيعية', 'code' => 'ABNORMAL', 'value' => '2', 'order' => 2],
        ]);

        // 29. إجراء القراءة
        $readingAction = $this->upsertMaster(29, 'إجراء القراءة', 'حالة إجراء قراءة العداد (غير معتمدة / معتمدة / مفوترة)', 29);
        $this->seedDetails($readingAction->id, [
            ['label' => 'غير معتمدة', 'code' => 'PENDING', 'value' => '0', 'order' => 1],
            ['label' => 'معتمدة', 'code' => 'APPROVED', 'value' => '1', 'order' => 2],
            ['label' => 'مفوترة', 'code' => 'BILLED', 'value' => '2', 'order' => 3],
        ]);

        // 30. حالة الفاتورة
        $invoiceStatus = $this->upsertMaster(30, 'حالة الفاتورة', 'حالة الفاتورة (مسودة، جديدة، متأخرة، مسددة جزئياً، مسددة، ملغاة)', 30);
        $this->seedDetails($invoiceStatus->id, [
            ['label' => 'مسودة', 'code' => 'DRAFT', 'value' => '0', 'order' => 1],
            ['label' => 'جديدة', 'code' => 'ISSUED', 'value' => '1', 'order' => 2],
            ['label' => 'متأخرة', 'code' => 'OVERDUE', 'value' => '5', 'order' => 3],
            ['label' => 'مسددة جزئياً', 'code' => 'PARTIAL', 'value' => '2', 'order' => 4],
            ['label' => 'مسددة', 'code' => 'PAID', 'value' => '3', 'order' => 5],
            ['label' => 'ملغاة', 'code' => 'CANCELLED', 'value' => '4', 'order' => 6],
        ]);

        // 31. قيم أمبير الاشتراك
        $ampereValues = $this->upsertMaster(31, 'قيم أمبير الاشتراك', 'قيم أمبير المتاحة لاشتراك المشترك', 31);
        $this->seedDetails($ampereValues->id, [
            ['label' => '1 أمبير', 'code' => 'A1', 'value' => '1', 'order' => 1],
            ['label' => '2 أمبير', 'code' => 'A2', 'value' => '2', 'order' => 2],
            ['label' => '3 أمبير', 'code' => 'A3', 'value' => '3', 'order' => 3],
            ['label' => '4 أمبير', 'code' => 'A4', 'value' => '4', 'order' => 4],
            ['label' => '6 أمبير', 'code' => 'A6', 'value' => '6', 'order' => 5],
            ['label' => '10 أمبير', 'code' => 'A10', 'value' => '10', 'order' => 6],
            ['label' => '16 أمبير', 'code' => 'A16', 'value' => '16', 'order' => 7],
        ]);

        $this->command->info('تم إنشاء/تحديث الثوابت بنجاح بدون حذف بيانات المستخدمين.');
    }

    private function upsertMaster(int $number, string $name, string $description, int $order): ConstantMaster
    {
        return ConstantMaster::updateOrCreate(
            ['constant_number' => $number],
            [
                'constant_name' => $name,
                'description' => $description,
                'is_active' => true,
                'order' => $order,
            ]
        );
    }

    private function seedDetails(int $masterId, array $details): void
    {
        foreach ($details as $detail) {
            $this->upsertDetail(
                $masterId,
                $detail['code'],
                $detail['label'],
                $detail['value'],
                $detail['order'],
                $detail['parent_detail_id'] ?? null
            );
        }
    }

    private function upsertDetail(
        int $masterId,
        string $code,
        string $label,
        string $value,
        int $order,
        ?int $parentDetailId = null
    ): ConstantDetail {
        return ConstantDetail::updateOrCreate(
            [
                'constant_master_id' => $masterId,
                'code' => $code,
            ],
            [
                'parent_detail_id' => $parentDetailId,
                'label' => $label,
                'value' => $value,
                'is_active' => true,
                'order' => $order,
            ]
        );
    }
}
