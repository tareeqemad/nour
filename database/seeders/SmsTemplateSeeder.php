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

            // ===== حالات ترخيص المشغّل =====
            [
                'key' => 'op_status_preliminary',
                'name' => 'حالة الطلب: موافقة مبدئية',
                'template' => "مرحباً {name}، حصل طلب {operator_name} في {site_name} على موافقة مبدئية. {note} يرجى استكمال بياناتك الفنية لإتمام إجراءات الترخيص. الدخول: {login_url}",
                'max_length' => 280,
                'is_active' => true,
            ],
            [
                'key' => 'op_status_needs_edit',
                'name' => 'حالة الطلب: مُرجَع للتعديل',
                'template' => "مرحباً {name}، طلب {operator_name} في {site_name} يحتاج إلى تعديل/استكمال البيانات. {note} يرجى الدخول وتحديثها: {login_url}",
                'max_length' => 280,
                'is_active' => true,
            ],
            [
                'key' => 'op_status_rejected',
                'name' => 'حالة الطلب: مرفوض',
                'template' => "مرحباً {name}، نأسف لإبلاغك بأنه تم رفض طلب {operator_name} في {site_name}. {note} للاستفسار يرجى التواصل مع سلطة الطاقة.",
                'max_length' => 280,
                'is_active' => true,
            ],
            [
                'key' => 'op_status_cancelled',
                'name' => 'حالة الطلب: ملغى',
                'template' => "مرحباً {name}، تم إلغاء طلب {operator_name} في {site_name}. {note} للاستفسار يرجى التواصل مع سلطة الطاقة.",
                'max_length' => 280,
                'is_active' => true,
            ],
            [
                'key' => 'op_status_licensed',
                'name' => 'حالة الطلب: حاصل على رخصة',
                'template' => "مرحباً {name}، تهانينا! حصل {operator_name} على الرخصة في {site_name}. {note} يمكنك الدخول: {login_url}",
                'max_length' => 280,
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
