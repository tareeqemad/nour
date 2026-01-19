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
        Schema::create('authorized_phones', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique()->comment('رقم الجوال المصرح به');
            $table->string('name')->nullable()->comment('اسم صاحب الرقم');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->boolean('is_active')->default(true)->comment('حالة التفعيل');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // فهارس
            $table->index('phone');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorized_phones');
    }
};
