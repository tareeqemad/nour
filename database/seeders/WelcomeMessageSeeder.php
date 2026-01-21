<?php

namespace Database\Seeders;

use App\Models\WelcomeMessage;
use Illuminate\Database\Seeder;

class WelcomeMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            [
                'key' => 'welcome',
                'title' => 'رسالة ترحيبية',
                'subject' => 'مرحباً بك في منصة نور',
                'body' => "عزيزي/عزيزتي {name}،\n\nنرحب بك في منصة نور لإدارة وحدات التوليد. نتمنى أن تجد في النظام كل ما تحتاجه لإدارة عملك بكفاءة وفعالية.\n\nنتمنى لك تجربة ممتعة!",
                'order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'quick_guide',
                'title' => 'دليل الاستخدام السريع',
                'subject' => 'دليل الاستخدام السريع',
                'body' => "عزيزي/عزيزتي {name}،\n\nيمكنك من خلال النظام:\n- إدارة بيانات المولدات ووحدات التوليد\n- متابعة سجلات التشغيل والوقود\n- إدارة أعمال الصيانة\n- التواصل مع الفريق من خلال نظام الرسائل\n\nللمزيد من المعلومات، يرجى مراجعة الدليل الإرشادي.",
                'order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'important_info',
                'title' => 'معلومات مهمة',
                'subject' => 'معلومات مهمة',
                'body' => "عزيزي/عزيزتي {name}،\n\nنود تذكيرك بأن:\n- يرجى إكمال بيانات المشغل في أقرب وقت ممكن\n- يمكنك التواصل معنا في أي وقت من خلال نظام الرسائل\n- ننصح بتغيير كلمة المرور بعد تسجيل الدخول لأول مرة\n\nنتمنى لك تجربة ناجحة!",
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($messages as $message) {
            WelcomeMessage::updateOrCreate(
                ['key' => $message['key']],
                $message
            );
        }

        $this->command->info('تم إنشاء/تحديث الرسائل الترحيبية بنجاح!');
    }
}
