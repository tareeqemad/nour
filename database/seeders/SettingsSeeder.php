<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('جاري حفظ الألوان الافتراضية...');

        // الألوان الافتراضية
        $defaultColors = [
            'primary_color' => [
                'value' => '#19228f',
                'label' => 'اللون الأساسي',
                'description' => 'اللون الأساسي المستخدم في الأزرار والروابط والعناصر الرئيسية',
            ],
            'dark_color' => [
                'value' => '#3b4863',
                'label' => 'اللون الداكن',
                'description' => 'اللون الداكن المستخدم في الوضع الداكن',
            ],
            'header_color' => [
                'value' => '#19228f',
                'label' => 'لون الـ Header',
                'description' => 'لون رأس الصفحة (Header)',
            ],
            'menu_color' => [
                'value' => '#F7F7F7',
                'label' => 'لون القائمة الجانبية',
                'description' => 'لون خلفية القائمة الجانبية (Sidebar Menu) عند اختيار Light',
            ],
        ];

        // حفظ الألوان
        foreach ($defaultColors as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'type' => 'color',
                    'group' => 'design',
                    'label' => $data['label'],
                    'description' => $data['description'],
                ]
            );
            $this->command->info("✓ تم حفظ {$data['label']}: {$data['value']}");
        }

        // إعدادات الأنماط الافتراضية
        $defaultStyles = [
            'menu_styles' => [
                'value' => 'light',
                'label' => 'ستايل القائمة الجانبية',
                'description' => 'نمط القائمة الجانبية (light, dark, color)',
            ],
            'header_styles' => [
                'value' => 'light',
                'label' => 'ستايل الـ Header',
                'description' => 'نمط رأس الصفحة (light, dark, color)',
            ],
        ];

        // حفظ الأنماط
        foreach ($defaultStyles as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'type' => 'select',
                    'group' => 'design',
                    'label' => $data['label'],
                    'description' => $data['description'],
                ]
            );
            $this->command->info("✓ تم حفظ {$data['label']}: {$data['value']}");
        }

        $this->command->info('تم حفظ جميع الألوان والإعدادات الافتراضية بنجاح!');

        // إعدادات اللوجو والأيقونات
        $this->command->info('جاري حفظ إعدادات اللوجو والأيقونات...');

        $logoAndFavicon = [
            'site_logo' => [
                'value' => 'assets/admin/images/brand-logos/nour_logo.png',
                'label' => 'لوجو الموقع',
                'description' => 'لوجو الموقع الرئيسي',
            ],
            'site_favicon' => [
                'value' => 'assets/admin/images/brand-logos/favicon.ico',
                'label' => 'أيقونة الموقع',
                'description' => 'أيقونة الموقع (Favicon)',
            ],
        ];

        // حفظ اللوجو والأيقونات
        foreach ($logoAndFavicon as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'type' => 'image',
                    'group' => 'logo',
                    'label' => $data['label'],
                    'description' => $data['description'],
                ]
            );
            $this->command->info("✓ تم حفظ {$data['label']}: {$data['value']}");
        }

        $this->command->info('تم حفظ جميع إعدادات اللوجو والأيقونات بنجاح!');

        // إعدادات اسم الموقع
        $this->command->info('جاري حفظ إعدادات اسم الموقع...');

        $siteSettings = [
            'site_name' => [
                'value' => 'نور',
                'label' => 'اسم الموقع',
                'description' => 'اسم الموقع الذي يظهر في جميع صفحات النظام',
            ],
        ];

        // حفظ إعدادات اسم الموقع
        foreach ($siteSettings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'type' => 'text',
                    'group' => 'general',
                    'label' => $data['label'],
                    'description' => $data['description'],
                ]
            );
            $this->command->info("✓ تم حفظ {$data['label']}: {$data['value']}");
        }

        $this->command->info('تم حفظ جميع إعدادات اسم الموقع بنجاح!');
    }
}

