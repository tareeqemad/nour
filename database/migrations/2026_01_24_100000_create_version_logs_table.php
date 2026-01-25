<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جدول سجل الإصدارات والتغييرات
     */
    public function up(): void
    {
        Schema::create('version_logs', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20); // رقم الإصدار (مثل: 1.0.0)
            $table->string('title'); // عنوان الإصدار
            $table->text('description')->nullable(); // وصف الإصدار
            $table->json('changes')->nullable(); // التغييرات (ميزات جديدة، إصلاحات، تحسينات)
            $table->enum('type', ['major', 'minor', 'patch'])->default('patch'); // نوع الإصدار
            $table->date('release_date'); // تاريخ الإصدار
            $table->boolean('is_current')->default(false); // هل هو الإصدار الحالي
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete(); // من أصدره
            $table->timestamps();
            
            $table->index('version');
            $table->index('is_current');
            $table->index('release_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_logs');
    }
};
