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
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generator_id')->constrained('generators')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            $table->unsignedInteger('sequence')->nullable(); // تسلسل السجل لكل مولد
            
            // تتبع المستخدمين
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // تاريخ ووقت التشغيل
            $table->date('operation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('operation_duration_hours', 8, 2)->nullable()->comment('مدة التشغيل بالساعات (محسوبة تلقائياً)');

            // نسبة التحميل
            $table->decimal('load_percentage', 5, 2)->nullable(); // نسبة التحميل وقت التشغيل

            // قراءات عداد الوقود
            $table->decimal('fuel_meter_start', 10, 2)->nullable(); // قراءة عداد الوقود عند البدء
            $table->decimal('fuel_meter_end', 10, 2)->nullable(); // قراءة عداد الوقود عند الانتهاء
            $table->decimal('fuel_consumed', 10, 2)->nullable(); // كمية الوقود المستهلك (لتر)

            // قراءات عداد الطاقة
            $table->decimal('energy_meter_start', 10, 2)->nullable(); // قراءة عداد الطاقة عند البدء
            $table->decimal('energy_meter_end', 10, 2)->nullable(); // قراءة عداد الطاقة عند الإيقاف
            $table->decimal('energy_produced', 10, 2)->nullable(); // كمية الطاقة المنتجة (kwh)
            $table->decimal('electricity_tariff_price', 10, 4)->nullable()->comment('سعر التعرفة الكهربائية (₪/kWh)');
            $table->decimal('efficiency', 5, 2)->nullable()->comment('الكفاءة (kWh/لتر) - محسوبة تلقائياً');

            // ملاحظات وأعطال
            $table->text('operational_notes')->nullable(); // ملاحظات تشغيلية
            $table->text('malfunctions')->nullable(); // الأعطال المسجلة

            $table->timestamps();
            $table->softDeletes();
            
            // فهارس للبحث السريع
            $table->index(['generator_id', 'sequence']);
            $table->index(['operator_id', 'operation_date'], 'idx_operation_logs_operator_date');
            $table->index(['generator_id', 'operation_date'], 'idx_operation_logs_generator_date');
            $table->index('energy_produced', 'idx_operation_logs_energy_produced');
        });
        
        // إضافة Constraints للتحقق من صحة البيانات (بعد إنشاء الجدول)
        $mysqlVersion = DB::select("SELECT VERSION() as version")[0]->version ?? '8.0.0';
        $supportsCheck = version_compare($mysqlVersion, '8.0.16', '>=');
        
        if ($supportsCheck) {
            // التحقق من أن وقت الإيقاف بعد وقت البدء
            DB::statement('
                ALTER TABLE operation_logs 
                ADD CONSTRAINT chk_operation_time_range 
                CHECK (end_time > start_time)
            ');
            
            // التحقق من أن نسبة التحميل بين 0 و 100
            DB::statement('
                ALTER TABLE operation_logs 
                ADD CONSTRAINT chk_load_percentage_range 
                CHECK (load_percentage IS NULL OR (load_percentage >= 0 AND load_percentage <= 100))
            ');
            
            // التحقق من أن الطاقة المنتجة إيجابية
            DB::statement('
                ALTER TABLE operation_logs 
                ADD CONSTRAINT chk_energy_positive 
                CHECK (energy_produced IS NULL OR energy_produced >= 0)
            ');
            
            // التحقق من أن الوقود المستهلك إيجابي
            DB::statement('
                ALTER TABLE operation_logs 
                ADD CONSTRAINT chk_fuel_positive 
                CHECK (fuel_consumed IS NULL OR fuel_consumed >= 0)
            ');
            
            // التحقق من أن سعر التعرفة بين 0 و 500 (يدعم أسعار غزة العالية - قد يصل إلى 30+ شيكل)
            DB::statement('
                ALTER TABLE operation_logs 
                ADD CONSTRAINT chk_tariff_price_range 
                CHECK (electricity_tariff_price IS NULL OR (electricity_tariff_price >= 0 AND electricity_tariff_price <= 500))
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
};
