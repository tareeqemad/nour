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
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();

            // رقم القراءة (يتم توليده تلقائياً)
            $table->string('reading_number')->unique()->comment('رقم القراءة');

            // ربط مع المشترك
            $table->foreignId('subscriber_id')->constrained('subscribers')->cascadeOnDelete()->comment('رقم الاشتراك');

            // بيانات القراءة
            $table->string('meter_number')->comment('رقم العداد');
            $table->decimal('previous_reading', 10, 2)->comment('القراءة السابقة');
            $table->decimal('current_reading', 10, 2)->comment('القراءة الحالية');
            $table->decimal('consumption_kwh', 10, 2)->comment('قيمة الاستهلاك Kwh');
            $table->date('reading_date')->comment('تاريخ القراءة');
            $table->date('previous_reading_date')->nullable()->comment('تاريخ القراءة السابقة');
            $table->integer('consumption_period_days')->comment('فترة الاستهلاك (عدد الأيام)');

            // حالة القراءة (1-طبيعية، 2-غير طبيعية)
            $table->tinyInteger('reading_status')->default(1)->comment('حالة القراءة: 1-طبيعية، 2-غير طبيعية');

            // سبب القراءة غير الطبيعية
            $table->text('abnormal_reason')->nullable()->comment('سبب اعتماد القراءة غير الطبيعية');

            // معلومات الاعتماد
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            // حقل الإجراء: 0-غير معتمدة، 1-معتمدة، 2-مفوترة
            $table->tinyInteger('action_status')->default(0)->comment('الإجراء: 0-غير معتمدة، 1-معتمدة، 2-مفوترة');

            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('المدخل');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('آخر من عدل');

            $table->timestamps();
            $table->softDeletes();

            // فهارس للبحث السريع
            $table->index('reading_number', 'idx_meter_readings_reading_number');
            $table->index('subscriber_id', 'idx_meter_readings_subscriber');
            $table->index('reading_date', 'idx_meter_readings_date');
            $table->index('reading_status', 'idx_meter_readings_status');
            $table->index('action_status', 'idx_meter_readings_action_status');
            $table->index(['subscriber_id', 'action_status', 'reading_date'], 'idx_meter_readings_subscriber_action_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
