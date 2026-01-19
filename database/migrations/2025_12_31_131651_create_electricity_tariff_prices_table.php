<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('electricity_tariff_prices')) {
            Schema::create('electricity_tariff_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
                $table->date('start_date'); // تاريخ بداية تطبيق السعر
                $table->date('end_date')->nullable(); // تاريخ نهاية تطبيق السعر (null = لا يزال ساري)
                $table->decimal('price_per_kwh', 10, 4); // سعر التعرفة لكل كيلووات ساعة
                $table->boolean('is_active')->default(true); // هل السعر نشط
                $table->text('notes')->nullable(); // ملاحظات (مثل: تغيير السعر الشهري)
                $table->timestamps();
                $table->softDeletes();
                
                // فهرس لتحسين البحث عن السعر النشط حسب التاريخ
                $table->index(['operator_id', 'start_date', 'end_date', 'is_active'], 'tariff_price_search_idx');
            });
            
            // إضافة Constraints للتحقق من صحة البيانات (بعد إنشاء الجدول)
            $mysqlVersion = DB::select("SELECT VERSION() as version")[0]->version ?? '8.0.0';
            $supportsCheck = version_compare($mysqlVersion, '8.0.16', '>=');
            
            if ($supportsCheck) {
                // التحقق من أن السعر بين 0 و 500 (يدعم أسعار غزة العالية)
                DB::statement('
                    ALTER TABLE electricity_tariff_prices 
                    ADD CONSTRAINT chk_tariff_price_range 
                    CHECK (price_per_kwh >= 0 AND price_per_kwh <= 500)
                ');
                
                // التحقق من أن تاريخ الانتهاء بعد تاريخ البدء
                DB::statement('
                    ALTER TABLE electricity_tariff_prices 
                    ADD CONSTRAINT chk_tariff_date_range 
                    CHECK (end_date IS NULL OR end_date >= start_date)
                ');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_tariff_prices');
    }
};
