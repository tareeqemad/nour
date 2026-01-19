<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * إنشاء جدول ملخص استهلاك الوقود للتقارير السريعة
     * يتم تحديثه عبر Jobs أو Events
     */
    public function up(): void
    {
        Schema::create('fuel_consumption_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            
            // تاريخ الملخص (يومي، أسبوعي، شهري)
            $table->date('summary_date');
            $table->enum('summary_type', ['daily', 'weekly', 'monthly'])->default('daily');
            
            // إحصائيات التشغيل
            $table->decimal('total_fuel_consumed', 12, 2)->default(0)->comment('إجمالي الوقود المستهلك (لتر)');
            $table->decimal('total_energy_produced', 12, 2)->default(0)->comment('إجمالي الطاقة المنتجة (kWh)');
            $table->decimal('avg_efficiency', 5, 2)->nullable()->comment('متوسط الكفاءة (kWh/لتر)');
            $table->decimal('operation_hours', 8, 2)->default(0)->comment('ساعات التشغيل');
            $table->integer('operation_count')->default(0)->comment('عدد مرات التشغيل');
            
            // إحصائيات إضافية
            $table->decimal('avg_load_percentage', 5, 2)->nullable()->comment('متوسط نسبة التحميل');
            $table->decimal('max_energy_produced', 10, 2)->nullable()->comment('أقصى طاقة منتجة في جلسة واحدة');
            $table->decimal('min_energy_produced', 10, 2)->nullable()->comment('أقل طاقة منتجة في جلسة واحدة');
            
            // التكاليف (إذا كانت متوفرة)
            $table->decimal('total_fuel_cost', 12, 2)->nullable()->comment('إجمالي تكلفة الوقود');
            $table->decimal('avg_fuel_price', 10, 2)->nullable()->comment('متوسط سعر الوقود للتر');
            
            // الإيرادات (إذا كانت متوفرة)
            $table->decimal('total_revenue', 12, 2)->nullable()->comment('إجمالي الإيرادات من بيع الكهرباء');
            $table->decimal('avg_tariff_price', 10, 4)->nullable()->comment('متوسط سعر التعرفة');
            
            $table->timestamps();
            
            // فهارس
            $table->unique(['generator_id', 'summary_date', 'summary_type'], 'unique_generator_summary');
            $table->index(['operator_id', 'summary_date'], 'idx_summary_operator_date');
            $table->index(['summary_date', 'summary_type'], 'idx_summary_date_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_consumption_summary');
    }
};
