<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\VersionLog;
use Illuminate\Database\Seeder;

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * إنشاء الإصدار الأول للمنصة
     */
    public function run(): void
    {
        // التحقق من عدم وجود إصدارات مسبقة
        if (VersionLog::count() > 0) {
            $this->command->info('تم تخطي Seeder الإصدارات - يوجد إصدارات مسبقة');
            return;
        }

        // إنشاء الإصدار الأول
        $version = VersionLog::create([
            'version' => '1.0.0',
            'title' => 'الإصدار الأول - إطلاق المنصة',
            'description' => 'الإصدار الأول من منصة نور لإدارة سوق الطاقة في فلسطين. يتضمن جميع الميزات الأساسية للنظام.',
            'type' => 'major',
            'release_date' => now()->format('Y-m-d'),
            'is_current' => true,
            'changes' => [
                'features' => [
                    'نظام إدارة المستخدمين والصلاحيات المتقدم',
                    'إدارة المشغلين ووحدات التوليد',
                    'إدارة المولدات والبيانات التقنية',
                    'سجلات التشغيل والصيانة',
                    'نظام الشكاوى والاقتراحات',
                    'نظام المراسلات الداخلية',
                    'نظام الإشعارات',
                    'لوحة تحكم شاملة مع إحصائيات',
                    'دعم الأدوار المخصصة',
                    'نظام الثوابت الديناميكي',
                ],
                'improvements' => [
                    'واجهة مستخدم عربية كاملة',
                    'تصميم متجاوب للأجهزة المختلفة',
                    'أداء محسّن لقاعدة البيانات',
                ],
                'security' => [
                    'تشفير كلمات المرور',
                    'حماية CSRF',
                    'سياسات الوصول للموارد',
                ],
                'fixes' => [],
            ],
        ]);

        // تحديث إعداد الإصدار
        Setting::set('app_version', '1.0.0', 'text', 'system', 'إصدار التطبيق', 'رقم الإصدار الحالي للمنصة');

        $this->command->info('تم إنشاء الإصدار الأول بنجاح: v1.0.0');
    }
}
