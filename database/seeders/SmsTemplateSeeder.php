<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'user_credentials',
                'name' => 'رسالة بيانات الدخول للمستخدم',
                'template' => "مرحباً {name}،\nتم تسجيلك على منصة نور.\nالدور: {role}\nاسم المستخدم: {username}\nكلمة المرور: {password}\nرابط الدخول: {login_url}",
                'max_length' => 220,
                'is_active' => true,
            ],
            [
                'key' => 'password_reset',
                'name' => 'رسالة إعادة تعيين كلمة المرور',
                'template' => "مرحباً {name}،\nتم إعادة تعيين كلمة المرور لحسابك على منصة نور.\n\nاسم المستخدم: {username}\nكلمة المرور الجديدة: {password}\n\nرابط الدخول: {login_url}",
                'max_length' => 220,
                'is_active' => true,
            ],
            [
                'key' => 'join_credentials',
                'name' => 'بيانات الدخول لطلبات الانضمام',
                'template' => "{site_name}\nاسم المستخدم: {username}\nكلمة المرور: {password}\nالدخول: {login_url}",
                'max_length' => 160,
                'is_active' => true,
            ],
            [
                'key' => 'authorized_phone_welcome',
                'name' => 'إشعار إضافة رقم للأرقام المصرح بها',
                'template' => "مرحباً {name}، تم إضافة رقمك للأرقام المصرّح بها في {site_name}. يمكنك التسجيل الآن عبر: {join_url}",
                'max_length' => 160,
                'is_active' => true,
            ],
            [
                'key' => 'task_assigned',
                'name' => 'رسالة تكليف بمهمة',
                'template' => "تم تكليفك بمهمة {task_type}\nالمشغل: {operator_name}\nرابط المهمة: {task_url}",
                'max_length' => 160,
                'is_active' => true,
            ],
        ];

        // firstOrCreate (وليس updateOrCreate): يضيف القوالب الناقصة فقط
        // ولا يستبدل أي قالب عدّله السوبر أدمن من لوحة التحكم — تعديلاته تبقى دائمة.
        foreach ($templates as $template) {
            SmsTemplate::firstOrCreate(
                ['key' => $template['key']],
                $template
            );
        }

        $this->command->info('تم إنشاء/تحديث قوالب SMS بنجاح!');
    }
}
