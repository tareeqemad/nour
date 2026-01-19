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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // نوع الصيانة - تخزن ID من constant_details، ثابت Master رقم 12 (نوع الصيانة)
            $table->foreignId('maintenance_type_id')
                ->constrained('constant_details')->cascadeOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 12 (نوع الصيانة)');

            // تاريخ الصيانة
            $table->date('maintenance_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->date('next_maintenance_date')->nullable()->comment('تاريخ الصيانة القادمة المتوقع');
            // نوع الصيانة القادمة - تخزن ID من constant_details، ثابت Master رقم 12 (نوع الصيانة)
            $table->foreignId('next_maintenance_type_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 12 (نوع الصيانة)');

            // الفني المسؤول
            $table->string('technician_name')->nullable(); // اسم الفني المسؤول

            // الأعمال المنفذة
            $table->text('work_performed')->nullable(); // الاعمال المنفذة

            // زمن التوقف
            $table->decimal('downtime_hours', 8, 2)->nullable(); // زمن التوقف (ساعات)

            // تكلفة الصيانة
            $table->decimal('parts_cost', 10, 2)->nullable();
            $table->decimal('labor_hours', 8, 2)->nullable();
            $table->decimal('labor_rate_per_hour', 10, 2)->nullable();
            $table->decimal('maintenance_cost', 10, 2)->nullable(); // تكلفة الصيانة

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['generator_id', 'maintenance_date'], 'idx_maintenance_records_generator_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
