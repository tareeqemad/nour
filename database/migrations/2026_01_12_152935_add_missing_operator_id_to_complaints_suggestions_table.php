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
        Schema::table('complaints_suggestions', function (Blueprint $table) {
            // التحقق من وجود العمود قبل إضافته
            if (!Schema::hasColumn('complaints_suggestions', 'operator_id')) {
                $table->foreignId('operator_id')->nullable()
                    ->after('governorate')
                    ->constrained('operators')->nullOnDelete();
            }
            
            // التحقق من وجود closed_by_operator و closed_at
            if (!Schema::hasColumn('complaints_suggestions', 'closed_by_operator')) {
                $table->boolean('closed_by_operator')->default(false)->after('responded_at');
            }
            
            if (!Schema::hasColumn('complaints_suggestions', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('closed_by_operator');
            }
            
            // إضافة الفهارس إذا لم تكن موجودة
            $indexes = DB::select("SHOW INDEX FROM complaints_suggestions WHERE Key_name = 'idx_complaints_operator_status'");
            if (empty($indexes)) {
                $table->index(['operator_id', 'status'], 'idx_complaints_operator_status');
            }
            
            $indexes = DB::select("SHOW INDEX FROM complaints_suggestions WHERE Key_name = 'idx_complaints_governorate_operator'");
            if (empty($indexes)) {
                $table->index(['governorate', 'operator_id'], 'idx_complaints_governorate_operator');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints_suggestions', function (Blueprint $table) {
            if (Schema::hasColumn('complaints_suggestions', 'operator_id')) {
                $table->dropForeign(['operator_id']);
                $table->dropIndex('idx_complaints_operator_status');
                $table->dropIndex('idx_complaints_governorate_operator');
                $table->dropColumn('operator_id');
            }
            
            if (Schema::hasColumn('complaints_suggestions', 'closed_by_operator')) {
                $table->dropColumn('closed_by_operator');
            }
            
            if (Schema::hasColumn('complaints_suggestions', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
        });
    }
};
