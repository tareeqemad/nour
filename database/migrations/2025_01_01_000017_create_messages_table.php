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
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->string('attachment')->nullable()->comment('مسار الصورة المرفقة');
            $table->enum('type', ['operator_to_operator', 'operator_to_staff', 'admin_to_operator', 'admin_to_all'])->default('operator_to_operator');

            // إعادة التوجيه
            $table->unsignedBigInteger('forwarded_from_id')->nullable();
            $table->foreign('forwarded_from_id')->references('id')->on('messages')->onDelete('set null');

            // CC/BCC
            $table->boolean('is_cc')->default(false);
            $table->boolean('is_bcc')->default(false);
            $table->unsignedBigInteger('original_message_id')->nullable();
            $table->foreign('original_message_id')->references('id')->on('messages')->onDelete('set null');

            $table->boolean('is_read')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->boolean('deleted_by_sender')->default(false);
            $table->boolean('deleted_by_receiver')->default(false);
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
