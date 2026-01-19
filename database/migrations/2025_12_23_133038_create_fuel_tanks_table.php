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
        Schema::create('fuel_tanks', function (Blueprint $table) {
            $table->id();
            // generation_unit_id سيتم إضافته بعد إنشاء جدول generation_units
            $table->string('tank_code')->nullable();
            $table->integer('capacity')->nullable(); // لتر
            // موقع الخزان - تخزن ID من constant_details، ثابت Master رقم 21 (موقع الخزان)
            $table->foreignId('location_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 21 (موقع الخزان)');
            $table->boolean('filtration_system_available')->default(false);
            $table->string('condition')->nullable();
            // مادة التصنيع - تخزن ID من constant_details، ثابت Master رقم 10 (مادة التصنيع)
            $table->foreignId('material_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 10 (مادة التصنيع)');
            // الاستخدام - تخزن ID من constant_details، ثابت Master رقم 11 (الاستخدام)
            $table->foreignId('usage_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 11 (الاستخدام)');
            // طريقة القياس - تخزن ID من constant_details، ثابت Master رقم 19 (طريقة القياس)
            $table->foreignId('measurement_method_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 19 (طريقة القياس)');
            $table->integer('order')->default(1); // ترتيب الخزان
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tank_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_tanks');
    }
};
