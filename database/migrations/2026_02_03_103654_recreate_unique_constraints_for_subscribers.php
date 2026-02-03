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
        // حذف أي constraints قديمة إذا وجدت
        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->dropUnique('unique_subscriber_id_number');
            });
        } catch (\Exception $e) {}
        
        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->dropUnique('unique_subscriber_phone');
            });
        } catch (\Exception $e) {}
        
        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->dropUnique('unique_subscriber_meter_number');
            });
        } catch (\Exception $e) {}

        // إضافة constraints جديدة
        Schema::table('subscribers', function (Blueprint $table) {
            $table->unique('subscriber_id_number', 'unique_subscriber_id_number');
            $table->unique('phone', 'unique_subscriber_phone');
            $table->unique('meter_number', 'unique_subscriber_meter_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropUnique('unique_subscriber_id_number');
            $table->dropUnique('unique_subscriber_phone');
            $table->dropUnique('unique_subscriber_meter_number');
        });
    }
};
