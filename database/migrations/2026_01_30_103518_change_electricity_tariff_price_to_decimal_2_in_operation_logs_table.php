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
        Schema::table('operation_logs', function (Blueprint $table) {
            // تغيير electricity_tariff_price من decimal(10,4) إلى decimal(10,2)
            $table->decimal('electricity_tariff_price', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operation_logs', function (Blueprint $table) {
            // إرجاع electricity_tariff_price إلى decimal(10,4)
            $table->decimal('electricity_tariff_price', 10, 4)->nullable()->change();
        });
    }
};
