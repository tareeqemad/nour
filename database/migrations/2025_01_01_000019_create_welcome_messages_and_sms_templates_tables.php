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
        Schema::create('welcome_messages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'welcome', 'quick_guide', 'important_info'
            $table->string('title'); // عنوان الرسالة
            $table->text('subject'); // موضوع الرسالة
            $table->text('body'); // محتوى الرسالة
            $table->integer('order')->default(0); // ترتيب الرسالة
            $table->boolean('is_active')->default(true); // تفعيل/إلغاء تفعيل
            $table->timestamps();
            $table->softDeletes();

            $table->index('key');
            $table->index('is_active');
        });

        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'user_credentials', 'operator_credentials', etc.
            $table->string('name'); // اسم القالب
            $table->text('template'); // قالب الرسالة (يدعم placeholders: {name}, {username}, {password}, {role}, {login_url})
            $table->integer('max_length')->default(160); // الحد الأقصى لطول الرسالة (160 حرف لرسائل SMS القصيرة)
            $table->boolean('is_active')->default(true); // تفعيل/إلغاء تفعيل
            $table->timestamps();
            $table->softDeletes();

            $table->index('key');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
        Schema::dropIfExists('welcome_messages');
    }
};
