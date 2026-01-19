<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الصلاحية (مثل: users.view, generators.create)
            $table->string('label'); // التسمية بالعربية (مثل: عرض المستخدمين)
            $table->string('group'); // المجموعة (مثل: users, generators, operation_logs)
            $table->string('group_label'); // تسمية المجموعة بالعربية
            $table->text('description')->nullable(); // وصف الصلاحية
            $table->integer('order')->default(0); // ترتيب العرض
            $table->timestamps();
            $table->softDeletes();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
