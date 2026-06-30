<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مثال (دور مبرمج مضمون): جدول "بوالص الضمان".
 * كل أسماء جداول مضمون تبدأ بـ madmoun_ ، وترتبط بنواة نور عبر مفاتيح أجنبية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('madmoun_policies', function (Blueprint $table) {
            $table->id();
            // ربط بالمشغّل من نواة نور (نفس قاعدة البيانات) — اختياري للعرض:
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
            $table->string('policy_number');          // رقم البوليصة
            $table->string('beneficiary');            // المستفيد
            $table->decimal('amount', 12, 2)->default(0); // قيمة الضمان
            $table->string('status')->default('active');  // active / expired
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('madmoun_policies');
    }
};
