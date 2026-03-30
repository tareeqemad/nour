<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة عمود operator_id إذا لم يكن موجوداً
        if (!Schema::hasColumn('minimum_charge_rules', 'operator_id')) {
            Schema::table('minimum_charge_rules', function (Blueprint $table) {
                $table->unsignedBigInteger('operator_id')->nullable()->after('id');
            });
        }

        // 2. نسخ القواعد الحالية لكل مشغل (فقط إذا لم يتم النسخ مسبقاً)
        $hasOrphanRules = DB::table('minimum_charge_rules')->whereNull('operator_id')->exists();
        if ($hasOrphanRules) {
            $operatorIds = DB::table('operators')->pluck('id');
            $existingRules = DB::table('minimum_charge_rules')->whereNull('operator_id')->get();

            foreach ($operatorIds as $operatorId) {
                foreach ($existingRules as $rule) {
                    DB::table('minimum_charge_rules')->insert([
                        'operator_id'    => $operatorId,
                        'ampere'         => $rule->ampere,
                        'phase_type'     => $rule->phase_type,
                        'minimum_charge' => $rule->minimum_charge,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }

            // 3. حذف القواعد القديمة (بدون operator)
            DB::table('minimum_charge_rules')->whereNull('operator_id')->delete();
        }

        // 4. تحديث القيد والفهارس
        Schema::table('minimum_charge_rules', function (Blueprint $table) {
            // حذف الـ unique القديم إذا موجود
            try {
                $table->dropUnique(['ampere', 'phase_type']);
            } catch (\Exception $e) {
                // قد يكون محذوف مسبقاً
            }

            $table->unsignedBigInteger('operator_id')->nullable(false)->change();
            $table->foreign('operator_id')->references('id')->on('operators')->cascadeOnDelete();
            $table->unique(['operator_id', 'ampere', 'phase_type']);
        });
    }

    public function down(): void
    {
        Schema::table('minimum_charge_rules', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropUnique(['operator_id', 'ampere', 'phase_type']);
            $table->dropColumn('operator_id');
            $table->unique(['ampere', 'phase_type']);
        });
    }
};
