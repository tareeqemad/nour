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
        Schema::create('generators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generator_number')->unique();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            // generation_unit_id سيتم إضافته بعد إنشاء جدول generation_units
            $table->text('description')->nullable();
            // الحالة - تخزن ID من constant_details، ثابت Master رقم 3 (حالة المولد)
            $table->foreignId('status_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 3 (حالة المولد)');
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();

            // المواصفات الفنية
            $table->decimal('capacity_kva', 10, 2)->nullable();
            $table->decimal('power_factor', 4, 2)->nullable();
            $table->integer('voltage')->nullable();
            $table->integer('frequency')->nullable();
            // نوع المحرك - تخزن ID من constant_details، ثابت Master رقم 4 (نوع المحرك)
            $table->foreignId('engine_type_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 4 (نوع المحرك)');

            // التشغيل والوقود
            $table->year('manufacturing_year')->nullable();
            // نظام الحقن - تخزن ID من constant_details، ثابت Master رقم 5 (نظام الحقن)
            $table->foreignId('injection_system_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 5 (نظام الحقن)');
            $table->decimal('fuel_consumption_rate', 8, 2)->nullable();
            $table->decimal('ideal_fuel_efficiency', 8, 3)->nullable()->comment('كفاءة الوقود المثالية (kWh/لتر)');
            $table->integer('internal_tank_capacity')->nullable();
            // مؤشر القياس - تخزن ID من constant_details، ثابت Master رقم 6 (مؤشر القياس)
            $table->foreignId('measurement_indicator_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 6 (مؤشر القياس)');

            // الحالة الفنية والتوثيق
            // الحالة الفنية - تخزن ID من constant_details، ثابت Master رقم 7 (الحالة الفنية)
            $table->foreignId('technical_condition_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 7 (الحالة الفنية)');
            $table->date('last_major_maintenance_date')->nullable();
            $table->date('last_operation_date')->nullable()->comment('تاريخ آخر تشغيل');
            $table->string('engine_data_plate_image')->nullable();
            $table->string('generator_data_plate_image')->nullable();

            // نظام التحكم
            $table->boolean('control_panel_available')->default(false);
            // نوع لوحة التحكم - تخزن ID من constant_details، ثابت Master رقم 8 (نوع لوحة التحكم)
            $table->foreignId('control_panel_type_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 8 (نوع لوحة التحكم)');
            // حالة لوحة التحكم - تخزن ID من constant_details، ثابت Master رقم 9 (حالة لوحة التحكم)
            $table->foreignId('control_panel_status_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 9 (حالة لوحة التحكم)');
            $table->string('control_panel_image')->nullable();
            $table->integer('operating_hours')->nullable();
            $table->integer('total_operating_hours')->default(0)->comment('إجمالي ساعات التشغيل');

            // خزانات الوقود
            $table->boolean('external_fuel_tank')->default(false);
            $table->integer('fuel_tanks_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['operator_id', 'status_id'], 'idx_generators_operator_status');
            $table->index(['name', 'generator_number'], 'idx_generators_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generators');
    }
};
