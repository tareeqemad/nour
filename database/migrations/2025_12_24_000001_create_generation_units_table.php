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
        Schema::create('generation_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();

            // بيانات وحدة التوليد
            $table->string('unit_code')->unique()->comment('كود الوحدة التلقائي: GU-PP-CC-NNN (PP=كود المحافظة، CC=كود المدينة، NNN=رقم تسلسلي للوحدة)');
            $table->string('unit_number')->comment('رقم الوحدة: 001, 002, إلخ');
            $table->string('name')->comment('اسم وحدة التوليد');
            $table->integer('generators_count')->default(0)->comment('عدد المولدات المطلوبة في هذه الوحدة');
            
            // الحالة - تخزن ID من constant_details، ثابت Master رقم 15
            $table->foreignId('status_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 15 (حالة الوحدة)');

            // الملكية والتشغيل
            $table->string('owner_name')->nullable();
            $table->string('owner_id_number')->nullable();
            // جهة التشغيل - تخزن ID من constant_details، ثابت Master رقم 2
            $table->foreignId('operation_entity_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 2 (جهة التشغيل)');
            $table->string('operator_id_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_alt')->nullable();
            $table->string('email')->nullable();

            // الموقع
            // المحافظة - تخزن ID من constant_details، ثابت Master رقم 1
            $table->foreignId('governorate_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 1 (المحافظات)');
            // المدينة - تخزن ID من constant_details، ثابت Master رقم 20
            $table->foreignId('city_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 20 (المدن)');
            $table->string('detailed_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // القدرات الفنية
            $table->decimal('total_capacity', 10, 2)->nullable()->comment('إجمالي القدرة (KVA)');
            // إمكانية المزامنة - تخزن ID من constant_details، ثابت Master رقم 16
            $table->foreignId('synchronization_available_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 16 (إمكانية المزامنة)');
            $table->decimal('max_synchronization_capacity', 10, 2)->nullable();

            // المستفيدون والبيئة
            $table->integer('beneficiaries_count')->nullable();
            $table->text('beneficiaries_description')->nullable();
            // حالة الامتثال البيئي - تخزن ID من constant_details، ثابت Master رقم 14
            $table->foreignId('environmental_compliance_status_id')->nullable()
                ->constrained('constant_details')->nullOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 14 (حالة الامتثال البيئي)');

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['operator_id', 'status_id'], 'idx_generation_units_operator_status');
            $table->index(['unit_code'], 'idx_generation_units_code');
            $table->index(['governorate_id', 'city_id'], 'idx_generation_units_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_units');
    }
};
