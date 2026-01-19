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
        Schema::create('fuel_efficiencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // تاريخ الاستهلاك
            $table->date('consumption_date');

            // ساعات التشغيل
            $table->decimal('operating_hours', 8, 2)->nullable();

            // سعر الوقود
            $table->decimal('fuel_price_per_liter', 10, 2)->nullable(); // سعر الوقود (للتر)
            $table->decimal('fuel_consumed', 10, 2)->nullable();

            // كفاءة استهلاك الوقود
            $table->decimal('fuel_efficiency_percentage', 5, 2)->nullable(); // كفاءة استهلاك الوقود (%)
            // مقارنة كفاءة الوقود - تخزن ID من constant_details، ثابت Master رقم 17 (مقارنة كفاءة الوقود)
            $table->foreignId('fuel_efficiency_comparison_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 17 (مقارنة كفاءة الوقود)');

            // كفاءة توزيع الطاقة
            $table->decimal('energy_distribution_efficiency', 5, 2)->nullable(); // كفاءة توزيع الطاقة (%)
            // مقارنة كفاءة الطاقة - تخزن ID من constant_details، ثابت Master رقم 18 (مقارنة كفاءة الطاقة)
            $table->foreignId('energy_efficiency_comparison_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 18 (مقارنة كفاءة الطاقة)');

            // تكلفة التشغيل
            $table->decimal('total_operating_cost', 12, 2)->nullable(); // حساب تكلفة التشغيل الإجمالية

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['generator_id', 'consumption_date'], 'idx_fuel_efficiencies_generator_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_efficiencies');
    }
};
