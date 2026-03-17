<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            // سبب اعتماد القراءة غير الطبيعية (إلزامي عند اعتماد قراءة غير طبيعية)
            $table->text('abnormal_reason')->nullable()->after('reading_status')
                  ->comment('سبب اعتماد القراءة غير الطبيعية');

            // معلومات من اعتمد القراءة ومتى
            $table->unsignedBigInteger('approved_by')->nullable()->after('abnormal_reason');
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['abnormal_reason', 'approved_by', 'approved_at']);
        });
    }
};
