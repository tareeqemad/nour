<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * يضيف إعداد "رابط الفيديو التعريفي" ليظهر كحقل جاهز في صفحة الإعدادات (مجموعة general).
     * firstOrCreate: لا يلمس باقي الإعدادات ولا يمحي رابطاً أدخله المسؤول إن أُعيد التشغيل.
     */
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'intro_video_url'],
            [
                'value'       => '',
                'type'        => 'text',
                'group'       => 'general',
                'label'       => 'رابط الفيديو التعريفي',
                'description' => 'رابط فيديو دليل التسجيل (YouTube أو ملف MP4) — يظهر في صفحة "دليل التسجيل والمصطلحات"',
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'intro_video_url')->delete();
    }
};
