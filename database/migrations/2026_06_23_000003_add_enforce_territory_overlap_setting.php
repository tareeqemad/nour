<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/**
 * يضيف مفتاح تجميد منع التداخل الجغرافي عند تسجيل البيانات.
 *
 * '0' (الافتراضي) = مُجمّد: لا يُمنع الحفظ عند التداخل، مع إبقاء كشف التداخل
 * وعرضه على الخريطة. يُضبط على '1' لاحقاً عند تفعيل آلية التلزيم ومناطق النفوذ
 * لإعادة فرض المنع.
 *
 * غير مُخرّب: firstOrCreate لا يلمس أي إعداد آخر ولا يُعيد قيمة عدّلها المسؤول.
 */
return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'enforce_territory_overlap'],
            [
                'value'       => '0',
                'type'        => 'boolean',
                'group'       => 'general',
                'label'       => 'فرض منع التداخل الجغرافي',
                'description' => 'عند التفعيل يُمنع تسجيل وحدة توليد ضمن نطاق مشغّل آخر. مُجمّد حالياً لحين تفعيل آلية التلزيم ومناطق النفوذ (مع بقاء كشف التداخل على الخريطة).',
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'enforce_territory_overlap')->delete();
    }
};
