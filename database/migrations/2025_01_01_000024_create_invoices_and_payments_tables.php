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
        // جدول الفواتير
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // الرابط الأساسي
            $table->string('invoice_number', 30)->nullable()->unique()->comment('رقم الفاتورة (يُنشأ عند الإصدار النهائي)');
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

            // الإصدار النهائي
            $table->unsignedBigInteger('issued_by')->nullable()->comment('معرّف المستخدم الذي أصدر الفاتورة نهائياً');
            $table->timestamp('issued_at')->nullable()->comment('تاريخ ووقت الإصدار النهائي للفاتورة');
            $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // فهارس
            $table->index('subscriber_id', 'idx_invoices_subscriber');
            $table->index('invoice_date', 'idx_invoices_date');
            $table->index('invoice_status', 'idx_invoices_status');
            $table->index('due_date', 'idx_invoices_due_date');
        });

        // جدول تسلسل أرقام الفواتير
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->string('prefix', 20)->primary()->comment('مفتاح التسلسل مثال: INV-202603');
            $table->unsignedInteger('last_seq')->default(0)->comment('آخر رقم تسلسلي مُستخدم');
        });

        // جدول المدفوعات
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->comment('الفاتورة المرتبطة');
            $table->unsignedBigInteger('subscriber_id')->comment('المشترك');
            $table->date('payment_date')->comment('تاريخ السداد');
            $table->decimal('amount_paid', 10, 2)->comment('قيمة الدفعة المسددة');
            $table->decimal('credit_applied', 10, 2)->default(0)->comment('رصيد دائن تم تطبيقه من حساب المشترك');
            $table->decimal('overpayment', 10, 2)->default(0)->comment('مبلغ زائد عن الفاتورة يُحوّل لرصيد دائن');
            $table->string('payment_method', 30)->default('cash')->comment('طريقة الدفع: cash|bank_transfer|cheque|online');
            $table->string('receipt_number', 60)->nullable()->comment('رقم الإيصال');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->unsignedBigInteger('created_by')->nullable()->comment('المستخدم الذي سجّل العملية');
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->restrictOnDelete();
            $table->foreign('subscriber_id')->references('id')->on('subscribers')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('last_updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_sequences');
        Schema::dropIfExists('invoices');
    }
};
