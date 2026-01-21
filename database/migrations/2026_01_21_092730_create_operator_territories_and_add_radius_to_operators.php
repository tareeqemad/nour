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
        // إضافة عمود territory_radius_km إلى جدول operators
        Schema::table('operators', function (Blueprint $table) {
            $table->decimal('territory_radius_km', 8, 2)->default(5.00)->after('profile_completed')
                ->comment('نصف قطر المنطقة الجغرافية بالكيلومترات (افتراضي 5 كم)');
        });

        // إنشاء جدول operator_territories
        Schema::create('operator_territories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            
            // المركز الجغرافي للمنطقة (نقطة البداية)
            $table->decimal('center_latitude', 10, 8);
            $table->decimal('center_longitude', 11, 8);
            
            // نصف قطر المنطقة بالكيلومترات (افتراضي 5 كم)
            $table->decimal('radius_km', 8, 2)->default(5.00);
            
            // معلومات إضافية
            $table->string('name')->nullable()->comment('اسم المنطقة (اختياري)');
            $table->text('description')->nullable()->comment('وصف المنطقة');
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // فهرس للبحث السريع
            $table->index(['operator_id']);
            $table->index(['center_latitude', 'center_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف جدول operator_territories
        Schema::dropIfExists('operator_territories');
        
        // حذف عمود territory_radius_km من جدول operators
        Schema::table('operators', function (Blueprint $table) {
            $table->dropColumn('territory_radius_km');
        });
    }
};
