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
        // حذف الـ foreign key بـ raw SQL (آمن لو مش موجود)
        $fkExists = DB::select("
            SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'minimum_charge_rules'
              AND CONSTRAINT_NAME = 'minimum_charge_rules_operator_id_foreign'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        if ($fkExists[0]->cnt > 0) {
            DB::statement('ALTER TABLE minimum_charge_rules DROP FOREIGN KEY minimum_charge_rules_operator_id_foreign');
        }

        // حذف الـ unique index الجديد
        $uxExists = DB::select("
            SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'minimum_charge_rules'
              AND CONSTRAINT_NAME = 'minimum_charge_rules_operator_id_ampere_phase_type_unique'
        ");
        if ($uxExists[0]->cnt > 0) {
            DB::statement('ALTER TABLE minimum_charge_rules DROP INDEX minimum_charge_rules_operator_id_ampere_phase_type_unique');
        }

        if (Schema::hasColumn('minimum_charge_rules', 'operator_id')) {
            // نبقي فقط سجل واحد لكل ampere+phase_type (أقل id)
            DB::statement("
                DELETE FROM minimum_charge_rules
                WHERE id NOT IN (
                    SELECT min_id FROM (
                        SELECT MIN(id) as min_id FROM minimum_charge_rules GROUP BY ampere, phase_type
                    ) as keeper
                )
            ");

            Schema::table('minimum_charge_rules', function (Blueprint $table) {
                $table->dropColumn('operator_id');
            });
        }

        // إعادة الـ unique القديم
        $oldUxExists = DB::select("
            SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'minimum_charge_rules'
              AND CONSTRAINT_NAME = 'minimum_charge_rules_ampere_phase_type_unique'
        ");
        if ($oldUxExists[0]->cnt == 0) {
            Schema::table('minimum_charge_rules', function (Blueprint $table) {
                $table->unique(['ampere', 'phase_type']);
            });
        }
    }
};
