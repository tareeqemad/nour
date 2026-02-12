<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_violation_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->nullable()->constrained('constant_details')->nullOnDelete()->comment('المحافظة');
            $table->string('subscription_number')->nullable()->comment('رقم الاشتراك');
            $table->string('subscriber_name')->comment('اسم المشترك');
            $table->string('beneficiary_name')->nullable()->comment('اسم المنتفع');
            $table->date('case_date')->comment('تاريخ القضية');
            $table->tinyInteger('status')->default(1)->comment('1-ادخال 2-محكومة 3-منتهية');
            $table->text('statement')->nullable()->comment('بيان القضية/التعدي');
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
            $table->foreignId('generation_unit_id')->nullable()->constrained('generation_units')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['operator_id', 'status']);
            $table->index(['subscription_number', 'subscriber_name'], 'ivc_sub_subscriber_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_violation_cases');
    }
};
