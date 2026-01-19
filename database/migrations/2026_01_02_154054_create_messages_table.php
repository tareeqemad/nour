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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete(); // null = رسالة لجميع موظفي المشغل
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete(); // للمشغلين: null = لجميع المشغلين
            $table->string('subject');
            $table->text('body');
            $table->enum('type', ['operator_to_operator', 'operator_to_staff', 'admin_to_operator', 'admin_to_all'])->default('operator_to_operator');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // فهارس
            $table->index(['sender_id', 'created_at']);
            $table->index(['receiver_id', 'is_read', 'created_at']);
            $table->index(['operator_id', 'type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
