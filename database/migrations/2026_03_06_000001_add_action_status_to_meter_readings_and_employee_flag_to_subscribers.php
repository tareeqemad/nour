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
        // إضافة حقل الإجراء لجدول قراءات العدادات
        Schema::table('meter_readings', function (Blueprint $table) {
            // حقل الإجراء: 0-غير معتمدة، 1-معتمدة، 2-مفوترة
            $table->tinyInteger('action_status')->default(0)->after('reading_status')
                ->comment('الإجراء: 0-غير معتمدة، 1-معتمدة، 2-مفوترة');

            // تعديل تعليق حالة القراءة (1-طبيعية، 2-غير طبيعية)
            $table->tinyInteger('reading_status')->default(1)->comment('حالة القراءة: 1-طبيعية، 2-غير طبيعية')->change();

            // فهرس للإجراء
            $table->index('action_status', 'idx_meter_readings_action_status');
        });

        // إضافة حقل تمييز اشتراك موظف الشركة لجدول المشتركين
        Schema::table('subscribers', function (Blueprint $table) {
            $table->boolean('is_employee_subscription')->default(false)->after('service_type')
                ->comment('هل الاشتراك خاص بموظف الشركة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropIndex('idx_meter_readings_action_status');
            $table->dropColumn('action_status');
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn('is_employee_subscription');
        });
    }
};
