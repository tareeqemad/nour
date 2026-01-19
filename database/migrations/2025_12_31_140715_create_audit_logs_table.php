<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * إنشاء جدول لتتبع جميع التغييرات في النظام (Audit Trail)
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // نوع الإجراء
            $table->string('action', 50)->index(); // 'create', 'update', 'delete', 'view'
            
            // النموذج المتأثر
            $table->string('model_type', 255)->index(); // 'App\Models\Generator'
            $table->unsignedBigInteger('model_id')->index();
            
            // القيم القديمة والجديدة
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // معلومات إضافية
            $table->text('description')->nullable(); // وصف للإجراء
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('route')->nullable(); // Route name أو URL
            $table->json('request_data')->nullable(); // بيانات الطلب الكاملة
            
            $table->timestamp('created_at')->index();
            
            // فهارس مركبة للبحث السريع
            $table->index(['model_type', 'model_id'], 'idx_audit_model');
            $table->index(['user_id', 'created_at'], 'idx_audit_user_date');
            $table->index(['action', 'created_at'], 'idx_audit_action_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
