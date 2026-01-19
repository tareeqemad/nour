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
        Schema::table('fuel_tanks', function (Blueprint $table) {
            // إضافة حقل condition_id الجديد
            $table->foreignId('condition_id')->nullable()
                ->after('filtration_system_available')
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 22 (حالة الخزان)');
        });

        // حذف حقل condition القديم بعد إضافة condition_id
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            // إعادة إضافة حقل condition القديم
            $table->string('condition')->nullable()->after('filtration_system_available');
        });

        Schema::table('fuel_tanks', function (Blueprint $table) {
            // حذف حقل condition_id
            $table->dropForeign(['condition_id']);
            $table->dropColumn('condition_id');
        });
    }
};
