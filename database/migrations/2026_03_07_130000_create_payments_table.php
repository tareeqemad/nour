<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
