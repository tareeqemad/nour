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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            
            // نوع المهمة: maintenance (صيانة) أو safety_inspection (فحص سلامة)
            $table->enum('type', ['maintenance', 'safety_inspection'])->default('maintenance');
            
            // المستخدم المكلف (الفني أو الدفاع المدني)
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            
            // المستخدم الذي كلف المهمة (SuperAdmin, Admin, EnergyAuthority)
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            
            // المشغل
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            
            // وحدة التوليد
            $table->foreignId('generation_unit_id')->nullable()->constrained('generation_units')->nullOnDelete();
            
            // المولد
            $table->foreignId('generator_id')->nullable()->constrained('generators')->nullOnDelete();
            
            // حالة المهمة
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            // وصف المهمة
            $table->text('description')->nullable();
            
            // تاريخ الاستحقاق
            $table->date('due_date')->nullable();
            
            // تاريخ الإنجاز
            $table->timestamp('completed_at')->nullable();
            
            // ملاحظات
            $table->text('notes')->nullable();
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['assigned_to', 'status'], 'idx_tasks_assigned_status');
            $table->index(['type', 'status'], 'idx_tasks_type_status');
            $table->index(['operator_id', 'generator_id'], 'idx_tasks_operator_generator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
