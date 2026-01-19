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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم الدور (مثل: super_admin, company_owner)
            $table->string('label'); // التسمية بالعربية (مثل: مدير النظام)
            $table->text('description')->nullable(); // وصف الدور
            $table->boolean('is_system')->default(false); // هل هو دور نظامي لا يمكن حذفه؟
            // operator_id سيتم إضافته بعد إنشاء جدول operators
            $table->integer('order')->default(0); // ترتيب العرض
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
