<?php

use App\Models\SmsTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * يستبدل اسم الموقع المثبّت نصياً ("منصة نور") بالمتغير الديناميكي
     * "منصة {site_name}" في قالبَي بيانات الدخول وإعادة تعيين كلمة المرور،
     * حتى يتغيّر اسم الموقع في الرسائل تلقائياً عند تعديله من الإعدادات.
     *
     * لا يلمس القوالب التي عدّلها السوبر أدمن لتستخدم {site_name} أصلاً.
     */
    public function up(): void
    {
        foreach (['user_credentials', 'password_reset'] as $key) {
            $template = SmsTemplate::where('key', $key)->first();

            if ($template && str_contains($template->template, 'منصة نور')) {
                $template->template = str_replace('منصة نور', 'منصة {site_name}', $template->template);
                $template->save();
            }
        }
    }

    public function down(): void
    {
        foreach (['user_credentials', 'password_reset'] as $key) {
            $template = SmsTemplate::where('key', $key)->first();

            if ($template && str_contains($template->template, 'منصة {site_name}')) {
                $template->template = str_replace('منصة {site_name}', 'منصة نور', $template->template);
                $template->save();
            }
        }
    }
};
