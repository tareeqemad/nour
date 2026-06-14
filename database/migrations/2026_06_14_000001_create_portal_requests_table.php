<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نسخة محلية من طلبات البوابة (e.services.gov.ps) ليتم الاستعلام عنها
     * بدون الاتصال بالـ API في كل مرة. تُملأ عبر الأمر: php artisan portal:sync-requests
     */
    public function up(): void
    {
        Schema::create('portal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('app_no')->unique();                  // رقم الطلب (المفتاح من الـ API)
            $table->string('ser_no')->nullable();                // رقم الخدمة
            $table->string('applicant_id')->nullable()->index(); // رقم هوية مقدم الطلب
            $table->string('applicant_name')->nullable();        // اسم مقدم الطلب (من pure_data)
            $table->string('app_status')->nullable()->index();   // كود حالة الطلب
            $table->string('desc_app_status')->nullable();       // وصف حالة الطلب
            $table->text('status_note')->nullable();             // ملاحظة الحالة
            $table->dateTime('inserted_at')->nullable()->index();// تاريخ تقديم الطلب
            $table->dateTime('changed_at')->nullable();          // آخر تعديل على الطلب
            $table->json('pure_data')->nullable();               // كامل بيانات الطلب كما وردت من الـ API
            $table->timestamp('synced_at')->nullable()->index(); // وقت آخر مزامنة لهذا السجل
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_requests');
    }
};
