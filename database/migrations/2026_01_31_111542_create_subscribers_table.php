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
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            
            // البيانات الأساسية
            $table->string('subscription_number')->unique()->comment('رقم الاشتراك');
            $table->string('subscriber_id_number')->comment('رقم هوية المشترك');
            $table->string('subscriber_name')->comment('اسم المشترك');
            $table->string('phone')->comment('رقم الجوال');
            $table->text('address')->comment('عنوان المشترك');
            $table->date('subscription_date')->comment('تاريخ الاشتراك');
            
            // التصنيفات (1-منزلي، 2-تجاري، 3-خدماتي، 4-صناعي)
            $table->tinyInteger('subscription_category')->default(1)->comment('تصنيف الاشتراك: 1-منزلي، 2-تجاري، 3-خدماتي، 4-صناعي');
            
            // نوع الفاز (1-1فاز، 2-3 فاز)
            $table->tinyInteger('phase_type')->default(1)->comment('نوع الفاز: 1-1فاز، 2-3 فاز');
            
            // حالة الاشتراك (1-نشط، 2-موقوف، 3-مغلق)
            $table->tinyInteger('subscription_status')->default(1)->comment('حالة الاشتراك: 1-نشط، 2-موقوف، 3-مغلق');
            
            // العداد
            $table->string('meter_number')->nullable()->comment('رقم العداد');
            
            // نوع الخدمة (1-مولد، 2-شمسي، 3-هجين)
            $table->tinyInteger('service_type')->default(1)->comment('نوع الخدمة: 1-مولد، 2-شمسي، 3-هجين');
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('المدخل');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('آخر من عدل');
            
            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index('subscription_number', 'idx_subscribers_subscription_number');
            $table->index('subscriber_id_number', 'idx_subscribers_id_number');
            $table->index('subscription_status', 'idx_subscribers_status');
            $table->index('subscription_category', 'idx_subscribers_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
