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
        // جدول قواعد الحد الأدنى للفوترة
        Schema::create('minimum_charge_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            $table->unsignedTinyInteger('ampere')->comment('قيمة الأمبير: 2, 6, 10, 16');
            $table->unsignedTinyInteger('phase_type')->comment('نوع الفاز: 1=أحادي, 2=ثلاثي');
            $table->decimal('minimum_charge', 10, 2)->comment('الحد الأدنى بالشيكل');
            $table->timestamps();

            $table->unique(['operator_id', 'ampere', 'phase_type']);
        });

        // جدول نسب خصم الموظفين
        Schema::create('employee_discount_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('discount_rate', 5, 2);  // نسبة الخصم مثل 10.00 أو 20.00
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_date', 'end_date', 'is_active'], 'emp_discount_rate_search_idx');
            $table->index('created_by');
        });

        // جدول قيود حسابات المشتركين
        Schema::create('subscriber_account_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entry_type', 20); // debit, credit, reverse
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2)->nullable();
            $table->text('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subscriber_id', 'entry_type']);
            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriber_account_entries');
        Schema::dropIfExists('employee_discount_rates');
        Schema::dropIfExists('minimum_charge_rules');
    }
};
