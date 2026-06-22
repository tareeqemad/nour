<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * حالة الترخيص للمشغّل (دورة حياة الطلب بعد الاعتماد):
     * pending / preliminary / needs_edit / rejected / cancelled / licensed
     *
     * آمن تماماً: يضيف عموداً جديداً فقط ولا يلمس is_approved أو status أو أي بيانات قائمة،
     * ولا يرسل أي رسائل. المشغّلون المعتمدون حالياً تُعيَّن حالتهم إلى "موافقة مبدئية"
     * (وغير المعتمدين يبقون "قيد المراجعة").
     */
    public function up(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->string('license_status', 30)->default('pending')->after('is_approved');
            $table->index('license_status');
        });

        // backfill غير مُخرِّب: المعتمد حالياً → موافقة مبدئية (الحقل جديد ولا يعتمد عليه أي منطق قائم)
        DB::table('operators')->where('is_approved', true)->update(['license_status' => 'preliminary']);
    }

    public function down(): void
    {
        Schema::table('operators', function (Blueprint $table) {
            $table->dropIndex(['license_status']);
            $table->dropColumn('license_status');
        });
    }
};
