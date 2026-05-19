<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');                                   // عنوان الإعلان
            $table->text('description');                               // نص التوضيح / وصف الإعلان
            $table->date('announcement_date');                         // تاريخ الإعلان
            $table->date('start_date');                                // من تاريخ
            $table->date('end_date');                                  // إلى تاريخ
            $table->boolean('is_featured')->default(false);            // مميز
            $table->boolean('is_visible')->default(true);              // إظهار / إخفاء
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // فلترة الواجهة العامة بتعتمد على الحقول التالية بشكل دائم
            $table->index(['is_visible', 'start_date', 'end_date'], 'idx_announcements_active_window');
            $table->index('is_featured', 'idx_announcements_featured');
            $table->index('announcement_date', 'idx_announcements_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
