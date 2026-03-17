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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // الرابط الأساسي
            $table->string('invoice_number', 30)->unique()->comment('رقم الفاتورة (يُنشأ تلقائياً عند الإصدار)');
            $table->foreignId('subscriber_id')->constrained('subscribers')->comment('رقم الاشتراك');
            $table->foreignId('meter_reading_id')->nullable()->constrained('meter_readings')->nullOnDelete()->comment('رقم القراءة المرتبطة');

            // بيانات الفترة والاستهلاك
            $table->date('invoice_date')->comment('تاريخ إصدار الفاتورة');
            $table->unsignedSmallInteger('consumption_period_days')->default(0)->comment('فترة الاستهلاك بالأيام');
            $table->decimal('consumption_kwh', 12, 2)->default(0)->comment('كمية الاستهلاك kWh');

            // التسعير
            $table->decimal('price_per_kwh', 8, 4)->default(0)->comment('سعر الكيلوواط شيكل');
            $table->decimal('discount_rate', 5, 2)->default(0)->comment('نسبة الخصم %');
            $table->decimal('invoice_amount', 12, 2)->default(0)->comment('ثمن الاستهلاك بعد الخصم شيكل');

            // الحد الأدنى والحسابات المالية
            $table->decimal('minimum_charge', 10, 2)->default(0)->comment('الحد الأدنى للفاتورة');
            $table->decimal('previous_balance', 12, 2)->default(0)->comment('الرصيد السابق للمشترك');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('المبلغ المطلوب (الفاتورة + الرصيد السابق)');

            // الحالة والدورة
            $table->tinyInteger('invoice_status')->default(0)
                ->comment('حالة الفاتورة: 0-مسودة، 1-مُصدَرة، 2-مدفوعة جزئياً، 3-مدفوعة، 4-ملغاة');
            $table->date('due_date')->nullable()->comment('تاريخ الاستحقاق');

            // ملاحظات
            $table->text('notes')->nullable()->comment('ملاحظات اختيارية');

            // تتبع
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('last_updated_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // فهارس
            $table->index('subscriber_id', 'idx_invoices_subscriber');
            $table->index('invoice_date', 'idx_invoices_date');
            $table->index('invoice_status', 'idx_invoices_status');
            $table->index('due_date', 'idx_invoices_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
