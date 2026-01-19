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
        Schema::create('permission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // المستخدم الذي تم تعديل صلاحياته
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete(); // المستخدم الذي قام بالتعديل
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete(); // الصلاحية التي تم تعديلها
            $table->enum('action', ['granted', 'revoked']); // نوع العملية: منح أو إلغاء
            $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_logs');
    }
};
