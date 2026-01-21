<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // المستخدمون
            ['name' => 'users.view', 'label' => 'عرض المستخدمين', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على عرض قائمة المستخدمين', 'order' => 1],
            ['name' => 'users.create', 'label' => 'إنشاء مستخدم', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على إنشاء مستخدم جديد', 'order' => 2],
            ['name' => 'users.update', 'label' => 'تحديث مستخدم', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على تحديث بيانات المستخدم', 'order' => 3],
            ['name' => 'users.delete', 'label' => 'حذف مستخدم', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على حذف المستخدم', 'order' => 4],
            ['name' => 'users.suspend', 'label' => 'تعطيل/حظر مستخدم', 'group' => 'users', 'group_label' => 'المستخدمون', 'description' => 'القدرة على تعطيل أو حظر المستخدمين الذين يسببون مشاكل', 'order' => 5],

            // المشغلون
            ['name' => 'operators.view', 'label' => 'عرض المشغلين', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على عرض قائمة المشغلين', 'order' => 5],
            ['name' => 'operators.create', 'label' => 'إنشاء مشغل', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على إنشاء مشغل جديد', 'order' => 6],
            ['name' => 'operators.update', 'label' => 'تحديث مشغل', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على تحديث بيانات المشغل', 'order' => 7],
            ['name' => 'operators.delete', 'label' => 'حذف مشغل', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على حذف المشغل', 'order' => 8],
            ['name' => 'operators.approve', 'label' => 'اعتماد/تفعيل مشغل', 'group' => 'operators', 'group_label' => 'المشغلون', 'description' => 'القدرة على اعتماد وتفعيل المشغلين', 'order' => 9],

            // المولدات
            ['name' => 'generators.view', 'label' => 'عرض المولدات', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على عرض قائمة المولدات', 'order' => 9],
            ['name' => 'generators.create', 'label' => 'إنشاء مولد', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على إنشاء مولد جديد', 'order' => 10],
            ['name' => 'generators.update', 'label' => 'تحديث مولد', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على تحديث بيانات المولد', 'order' => 11],
            ['name' => 'generators.delete', 'label' => 'حذف مولد', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على حذف المولد', 'order' => 12],
            ['name' => 'generators.transfer', 'label' => 'نقل مولد', 'group' => 'generators', 'group_label' => 'المولدات', 'description' => 'القدرة على نقل المولدات بين المشغلين', 'order' => 13],

            // سجلات التشغيل
            ['name' => 'operation_logs.view', 'label' => 'عرض سجلات التشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على عرض سجلات التشغيل', 'order' => 13],
            ['name' => 'operation_logs.create', 'label' => 'إنشاء سجل تشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على إنشاء سجل تشغيل جديد', 'order' => 14],
            ['name' => 'operation_logs.update', 'label' => 'تحديث سجل تشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على تحديث سجل التشغيل', 'order' => 15],
            ['name' => 'operation_logs.delete', 'label' => 'حذف سجل تشغيل', 'group' => 'operation_logs', 'group_label' => 'سجلات التشغيل', 'description' => 'القدرة على حذف سجل التشغيل', 'order' => 16],

            // كفاءة الوقود
            ['name' => 'fuel_efficiencies.view', 'label' => 'عرض كفاءة الوقود', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على عرض سجلات كفاءة الوقود', 'order' => 17],
            ['name' => 'fuel_efficiencies.create', 'label' => 'إنشاء سجل كفاءة', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على إنشاء سجل كفاءة وقود جديد', 'order' => 18],
            ['name' => 'fuel_efficiencies.update', 'label' => 'تحديث سجل كفاءة', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على تحديث سجل كفاءة الوقود', 'order' => 19],
            ['name' => 'fuel_efficiencies.delete', 'label' => 'حذف سجل كفاءة', 'group' => 'fuel_efficiencies', 'group_label' => 'كفاءة الوقود', 'description' => 'القدرة على حذف سجل كفاءة الوقود', 'order' => 20],

            // سجلات الصيانة
            ['name' => 'maintenance_records.view', 'label' => 'عرض سجلات الصيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على عرض سجلات الصيانة', 'order' => 21],
            ['name' => 'maintenance_records.create', 'label' => 'إنشاء سجل صيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على إنشاء سجل صيانة جديد', 'order' => 22],
            ['name' => 'maintenance_records.update', 'label' => 'تحديث سجل صيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على تحديث سجل الصيانة', 'order' => 23],
            ['name' => 'maintenance_records.delete', 'label' => 'حذف سجل صيانة', 'group' => 'maintenance_records', 'group_label' => 'سجلات الصيانة', 'description' => 'القدرة على حذف سجل الصيانة', 'order' => 24],

            // الامتثال والسلامة
            ['name' => 'compliance_safeties.view', 'label' => 'عرض الامتثال والسلامة', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على عرض سجلات الامتثال والسلامة', 'order' => 25],
            ['name' => 'compliance_safeties.create', 'label' => 'إنشاء سجل امتثال', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على إنشاء سجل امتثال جديد', 'order' => 26],
            ['name' => 'compliance_safeties.update', 'label' => 'تحديث سجل امتثال', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على تحديث سجل الامتثال', 'order' => 27],
            ['name' => 'compliance_safeties.delete', 'label' => 'حذف سجل امتثال', 'group' => 'compliance_safeties', 'group_label' => 'الامتثال والسلامة', 'description' => 'القدرة على حذف سجل الامتثال', 'order' => 28],

            // Tasks (المهام)
            ['name' => 'tasks.view', 'label' => 'عرض المهام', 'group' => 'tasks', 'group_label' => 'المهام', 'description' => 'القدرة على عرض المهام', 'order' => 29],
            ['name' => 'tasks.create', 'label' => 'إنشاء مهمة', 'group' => 'tasks', 'group_label' => 'المهام', 'description' => 'القدرة على إنشاء مهمة جديدة', 'order' => 30],
            ['name' => 'tasks.update', 'label' => 'تحديث مهمة', 'group' => 'tasks', 'group_label' => 'المهام', 'description' => 'القدرة على تحديث المهمة', 'order' => 31],
            ['name' => 'tasks.delete', 'label' => 'حذف مهمة', 'group' => 'tasks', 'group_label' => 'المهام', 'description' => 'القدرة على حذف المهمة', 'order' => 32],

            // أسعار التعرفة الكهربائية
            ['name' => 'electricity_tariff_prices.view', 'label' => 'عرض أسعار التعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على عرض أسعار التعرفة الكهربائية', 'order' => 29],
            ['name' => 'electricity_tariff_prices.create', 'label' => 'إنشاء سعر تعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على إنشاء سعر تعرفة جديد', 'order' => 30],
            ['name' => 'electricity_tariff_prices.update', 'label' => 'تحديث سعر تعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على تحديث سعر التعرفة', 'order' => 31],
            ['name' => 'electricity_tariff_prices.delete', 'label' => 'حذف سعر تعرفة', 'group' => 'electricity_tariff_prices', 'group_label' => 'أسعار التعرفة الكهربائية', 'description' => 'القدرة على حذف سعر التعرفة', 'order' => 32],

            // إدارة الصلاحيات
            ['name' => 'permissions.manage', 'label' => 'إدارة الصلاحيات', 'group' => 'permissions', 'group_label' => 'الصلاحيات', 'description' => 'القدرة على إدارة صلاحيات المستخدمين', 'order' => 33],

            // وحدات التوليد
            ['name' => 'generation_units.view', 'label' => 'عرض وحدات التوليد', 'group' => 'generation_units', 'group_label' => 'وحدات التوليد', 'description' => 'القدرة على عرض قائمة وحدات التوليد', 'order' => 34],
            ['name' => 'generation_units.create', 'label' => 'إنشاء وحدة توليد', 'group' => 'generation_units', 'group_label' => 'وحدات التوليد', 'description' => 'القدرة على إنشاء وحدة توليد جديدة', 'order' => 35],
            ['name' => 'generation_units.update', 'label' => 'تحديث وحدة توليد', 'group' => 'generation_units', 'group_label' => 'وحدات التوليد', 'description' => 'القدرة على تحديث بيانات وحدة التوليد', 'order' => 36],
            ['name' => 'generation_units.delete', 'label' => 'حذف وحدة توليد', 'group' => 'generation_units', 'group_label' => 'وحدات التوليد', 'description' => 'القدرة على حذف وحدة التوليد', 'order' => 37],

            // الأرقام المصرح بها
            ['name' => 'authorized_phones.view', 'label' => 'عرض الأرقام المصرح بها', 'group' => 'authorized_phones', 'group_label' => 'الأرقام المصرح بها', 'description' => 'القدرة على عرض قائمة الأرقام المصرح بها', 'order' => 38],
            ['name' => 'authorized_phones.create', 'label' => 'إنشاء رقم مصرح به', 'group' => 'authorized_phones', 'group_label' => 'الأرقام المصرح بها', 'description' => 'القدرة على إضافة رقم جديد للقائمة المصرح بها', 'order' => 39],
            ['name' => 'authorized_phones.update', 'label' => 'تحديث رقم مصرح به', 'group' => 'authorized_phones', 'group_label' => 'الأرقام المصرح بها', 'description' => 'القدرة على تحديث بيانات الرقم المصرح به', 'order' => 40],
            ['name' => 'authorized_phones.delete', 'label' => 'حذف رقم مصرح به', 'group' => 'authorized_phones', 'group_label' => 'الأرقام المصرح بها', 'description' => 'القدرة على حذف رقم من القائمة المصرح بها', 'order' => 41],

            // سجل الأخطاء
            ['name' => 'logs.view', 'label' => 'عرض سجل الأخطاء', 'group' => 'logs', 'group_label' => 'سجل الأخطاء', 'description' => 'القدرة على عرض سجل أخطاء النظام', 'order' => 42],
            ['name' => 'logs.clear', 'label' => 'حذف سجل الأخطاء', 'group' => 'logs', 'group_label' => 'سجل الأخطاء', 'description' => 'القدرة على حذف سجل الأخطاء', 'order' => 43],
            ['name' => 'logs.download', 'label' => 'تحميل سجل الأخطاء', 'group' => 'logs', 'group_label' => 'سجل الأخطاء', 'description' => 'القدرة على تحميل ملف سجل الأخطاء', 'order' => 44],

            // الدليل الإرشادي
            ['name' => 'guide.view', 'label' => 'عرض الدليل الإرشادي', 'group' => 'guide', 'group_label' => 'الدليل الإرشادي', 'description' => 'القدرة على عرض الدليل الإرشادي', 'order' => 45],

            // الرسائل الترحيبية
            ['name' => 'welcome_messages.view', 'label' => 'عرض الرسائل الترحيبية', 'group' => 'welcome_messages', 'group_label' => 'الرسائل الترحيبية', 'description' => 'القدرة على عرض الرسائل الترحيبية', 'order' => 46],
            ['name' => 'welcome_messages.update', 'label' => 'تعديل الرسائل الترحيبية', 'group' => 'welcome_messages', 'group_label' => 'الرسائل الترحيبية', 'description' => 'القدرة على تعديل الرسائل الترحيبية', 'order' => 47],

            // قوالب SMS
            ['name' => 'sms_templates.view', 'label' => 'عرض قوالب SMS', 'group' => 'sms_templates', 'group_label' => 'قوالب SMS', 'description' => 'القدرة على عرض قوالب رسائل الجوال', 'order' => 48],
            ['name' => 'sms_templates.update', 'label' => 'تعديل قوالب SMS', 'group' => 'sms_templates', 'group_label' => 'قوالب SMS', 'description' => 'القدرة على تعديل قوالب رسائل الجوال', 'order' => 49],

            // إعدادات الموقع (SuperAdmin only)
            ['name' => 'settings.view', 'label' => 'عرض إعدادات الموقع', 'group' => 'settings', 'group_label' => 'إعدادات الموقع', 'description' => 'القدرة على عرض إعدادات الموقع', 'order' => 50],
            ['name' => 'settings.update', 'label' => 'تعديل إعدادات الموقع', 'group' => 'settings', 'group_label' => 'إعدادات الموقع', 'description' => 'القدرة على تعديل إعدادات الموقع', 'order' => 51],

            // إدارة الثوابت (SuperAdmin only)
            ['name' => 'constants.view', 'label' => 'عرض الثوابت', 'group' => 'constants', 'group_label' => 'إدارة الثوابت', 'description' => 'القدرة على عرض الثوابت', 'order' => 52],
            ['name' => 'constants.create', 'label' => 'إنشاء ثابت', 'group' => 'constants', 'group_label' => 'إدارة الثوابت', 'description' => 'القدرة على إنشاء ثابت جديد', 'order' => 53],
            ['name' => 'constants.update', 'label' => 'تحديث ثابت', 'group' => 'constants', 'group_label' => 'إدارة الثوابت', 'description' => 'القدرة على تحديث الثوابت', 'order' => 54],
            ['name' => 'constants.delete', 'label' => 'حذف ثابت', 'group' => 'constants', 'group_label' => 'إدارة الثوابت', 'description' => 'القدرة على حذف الثوابت', 'order' => 55],

            // إدارة الأدوار (SuperAdmin and Energy Authority)
            ['name' => 'roles.view', 'label' => 'عرض الأدوار', 'group' => 'roles', 'group_label' => 'الأدوار', 'description' => 'القدرة على عرض الأدوار', 'order' => 56],
            ['name' => 'roles.create', 'label' => 'إنشاء دور', 'group' => 'roles', 'group_label' => 'الأدوار', 'description' => 'القدرة على إنشاء دور جديد', 'order' => 57],
            ['name' => 'roles.update', 'label' => 'تحديث دور', 'group' => 'roles', 'group_label' => 'الأدوار', 'description' => 'القدرة على تحديث الأدوار', 'order' => 58],
            ['name' => 'roles.delete', 'label' => 'حذف دور', 'group' => 'roles', 'group_label' => 'الأدوار', 'description' => 'القدرة على حذف الأدوار', 'order' => 59],

            // الشكاوى والمقترحات
            ['name' => 'complaints_suggestions.view', 'label' => 'عرض الشكاوى والمقترحات', 'group' => 'complaints_suggestions', 'group_label' => 'الشكاوى والمقترحات', 'description' => 'القدرة على عرض الشكاوى والمقترحات', 'order' => 60],
            ['name' => 'complaints_suggestions.update', 'label' => 'تحديث شكوى/مقترح', 'group' => 'complaints_suggestions', 'group_label' => 'الشكاوى والمقترحات', 'description' => 'القدرة على تحديث الشكاوى والمقترحات', 'order' => 61],
            ['name' => 'complaints_suggestions.delete', 'label' => 'حذف شكوى/مقترح', 'group' => 'complaints_suggestions', 'group_label' => 'الشكاوى والمقترحات', 'description' => 'القدرة على حذف الشكاوى والمقترحات', 'order' => 62],
            ['name' => 'complaints_suggestions.respond', 'label' => 'الرد على شكوى/مقترح', 'group' => 'complaints_suggestions', 'group_label' => 'الشكاوى والمقترحات', 'description' => 'القدرة على الرد على الشكاوى والمقترحات', 'order' => 63],

            // الإشعارات
            ['name' => 'notifications.view', 'label' => 'عرض الإشعارات', 'group' => 'notifications', 'group_label' => 'الإشعارات', 'description' => 'القدرة على عرض الإشعارات', 'order' => 64],
            ['name' => 'notifications.delete', 'label' => 'حذف إشعار', 'group' => 'notifications', 'group_label' => 'الإشعارات', 'description' => 'القدرة على حذف الإشعارات', 'order' => 65],

            // الرسائل الداخلية
            ['name' => 'messages.view', 'label' => 'عرض الرسائل', 'group' => 'messages', 'group_label' => 'الرسائل', 'description' => 'القدرة على عرض الرسائل الداخلية', 'order' => 66],
            ['name' => 'messages.create', 'label' => 'إنشاء رسالة', 'group' => 'messages', 'group_label' => 'الرسائل', 'description' => 'القدرة على إنشاء رسالة جديدة', 'order' => 67],
            ['name' => 'messages.update', 'label' => 'تحديث رسالة', 'group' => 'messages', 'group_label' => 'الرسائل', 'description' => 'القدرة على تحديث الرسائل', 'order' => 68],
            ['name' => 'messages.delete', 'label' => 'حذف رسالة', 'group' => 'messages', 'group_label' => 'الرسائل', 'description' => 'القدرة على حذف الرسائل', 'order' => 69],

            // سجل تغييرات الصلاحيات
            ['name' => 'permission_audit_logs.view', 'label' => 'عرض سجل تغييرات الصلاحيات', 'group' => 'permission_audit_logs', 'group_label' => 'سجل تغييرات الصلاحيات', 'description' => 'القدرة على عرض سجل تغييرات الصلاحيات', 'order' => 70],

            // سجل الأنشطة (Audit Logs)
            ['name' => 'activity_logs.view', 'label' => 'عرض سجل الأنشطة', 'group' => 'activity_logs', 'group_label' => 'سجل الأنشطة', 'description' => 'القدرة على عرض سجل أنشطة النظام', 'order' => 71],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('تم إنشاء/تحديث الصلاحيات بنجاح!');
    }
}
