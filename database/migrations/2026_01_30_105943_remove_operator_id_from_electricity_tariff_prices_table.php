<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('electricity_tariff_prices', function (Blueprint $table) {
            // إزالة operator_id لأن الأسعار الآن عامة لجميع المشغلين
            $table->dropForeign(['operator_id']);
            $table->dropIndex('tariff_price_search_idx'); // إزالة الفهرس القديم
            $table->dropColumn('operator_id');
            // إعادة إنشاء الفهرس بدون operator_id
            $table->index(['start_date', 'end_date', 'is_active'], 'tariff_price_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('electricity_tariff_prices', function (Blueprint $table) {
            // إرجاع operator_id (سيحتاج قيم افتراضية)
            $table->foreignId('operator_id')->nullable()->after('id')->constrained('operators')->cascadeOnDelete();
            $table->dropIndex('tariff_price_search_idx');
            $table->index(['operator_id', 'start_date', 'end_date', 'is_active'], 'tariff_price_search_idx');
        });
    }
};
