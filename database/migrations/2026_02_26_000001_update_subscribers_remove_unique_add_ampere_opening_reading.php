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
        $cols = array_filter(['ampere', 'opening_reading'], fn($c) => Schema::hasColumn('subscribers', $c));
        if ($cols) {
            Schema::table('subscribers', function (Blueprint $table) use ($cols) {
                $table->dropColumn(array_values($cols));
            });
        }

        // حذف المكررات قبل إضافة القيود الفريدة
        foreach (['subscriber_id_number', 'phone', 'meter_number'] as $col) {
            \DB::statement("
                DELETE s1 FROM subscribers s1
                INNER JOIN subscribers s2
                WHERE s1.id > s2.id AND s1.{$col} = s2.{$col} AND s1.{$col} IS NOT NULL
            ");
        }

        // إعادة القيود الفريدة
        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->unique('subscriber_id_number', 'unique_subscriber_id_number');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->unique('phone', 'unique_subscriber_phone');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('subscribers', function (Blueprint $table) {
                $table->unique('meter_number', 'unique_subscriber_meter_number');
            });
        } catch (\Exception $e) {}
    }
};
