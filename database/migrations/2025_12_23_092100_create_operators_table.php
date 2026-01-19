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
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            
            // البيانات الأساسية
            $table->string('name'); // اسم المشغل (العربي)
            
            // العلاقة مع المستخدم (المالك)
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            
            // تتبع المستخدمين (من TracksUser trait)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();

            // بيانات المالك والمشغل
            $table->string('owner_name')->nullable(); // اسم المالك (قد يكون مختلف عن owner_id)
            $table->string('owner_id_number')->nullable(); // رقم هوية المالك
            $table->string('operator_id_number')->nullable(); // رقم هوية المشغل

            // الحالة العامة
            $table->string('status')->default('active'); // active, inactive
            $table->boolean('is_approved')->default(false)->comment('حالة الاعتماد - المشغل يحتاج موافقة Admin/Super Admin');
            $table->boolean('profile_completed')->default(false);

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index('name', 'idx_operators_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
