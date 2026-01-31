<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // أولاً: ملء القيم الفارغة بقيمة افتراضية (تاريخ اليوم)
        DB::table('subscribers')
            ->whereNull('subscription_date')
            ->update(['subscription_date' => now()->toDateString()]);

        // ثانياً: تعديل العمود ليكون إجباري
        Schema::table('subscribers', function (Blueprint $table) {
            $table->date('subscription_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->date('subscription_date')->nullable()->change();
        });
    }
};
