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
        // حذف القيود الفريدة لرقم الهوية والجوال
        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->dropUnique('unique_subscriber_id_number');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->dropUnique('unique_subscriber_phone');
            });
        } catch (\Exception $e) {}

        // حذف القيد الفريد لرقم العداد إن وجد
        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->dropUnique('unique_subscriber_meter_number');
            });
        } catch (\Exception $e) {}

        // إضافة حقل أمبير الاشتراك وقراءة العداد الافتتاحية
        Schema::table('subscribers', function (Blueprint $table) {
            $table->decimal('ampere', 8, 2)->nullable()->after('meter_number')->comment('قيمة أمبير الاشتراك');
            $table->decimal('opening_reading', 10, 2)->nullable()->after('ampere')->comment('قراءة العداد الافتتاحية');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['ampere', 'opening_reading']);
        });

        // إعادة القيود الفريدة
        Schema::table('subscribers', function (Blueprint $table) {
            $table->unique('subscriber_id_number', 'unique_subscriber_id_number');
            $table->unique('phone', 'unique_subscriber_phone');
            $table->unique('meter_number', 'unique_subscriber_meter_number');
        });
    }
};
