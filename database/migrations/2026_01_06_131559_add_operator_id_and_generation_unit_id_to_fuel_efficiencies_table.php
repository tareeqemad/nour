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
        // التحقق من وجود الأعمدة أولاً
        $columns = Schema::getColumnListing('fuel_efficiencies');
        
        if (!in_array('operator_id', $columns)) {
            Schema::table('fuel_efficiencies', function (Blueprint $table) {
                // إضافة الأعمدة كـ nullable أولاً
                $table->unsignedBigInteger('operator_id')->nullable()->after('generator_id');
            });
        }
        
        if (!in_array('generation_unit_id', $columns)) {
            Schema::table('fuel_efficiencies', function (Blueprint $table) {
                $table->unsignedBigInteger('generation_unit_id')->nullable()->after('operator_id');
            });
        }
        
        // ملء البيانات من generator وتصحيح القيم غير الصحيحة
        \DB::statement('
            UPDATE fuel_efficiencies fe
            INNER JOIN generators g ON fe.generator_id = g.id
            LEFT JOIN operators o ON fe.operator_id = o.id
            SET fe.operator_id = g.operator_id,
                fe.generation_unit_id = g.generation_unit_id
            WHERE fe.operator_id IS NULL OR o.id IS NULL
        ');
        
        // التحقق من وجود foreign keys
        $foreignKeys = \DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'fuel_efficiencies' 
            AND CONSTRAINT_NAME LIKE '%operator_id%'
        ");
        
        if (empty($foreignKeys)) {
            Schema::table('fuel_efficiencies', function (Blueprint $table) {
                // جعل الأعمدة required وإضافة foreign keys
                $table->unsignedBigInteger('operator_id')->nullable(false)->change();
                $table->foreign('operator_id')->references('id')->on('operators')->onDelete('cascade');
            });
        }
        
        $foreignKeys = \DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'fuel_efficiencies' 
            AND CONSTRAINT_NAME LIKE '%generation_unit_id%'
        ");
        
        if (empty($foreignKeys)) {
            Schema::table('fuel_efficiencies', function (Blueprint $table) {
                $table->foreign('generation_unit_id')->references('id')->on('generation_units')->onDelete('set null');
            });
        }
        
        // إضافة فهارس للبحث السريع
        Schema::table('fuel_efficiencies', function (Blueprint $table) {
            if (!$this->indexExists('fuel_efficiencies', 'idx_fuel_efficiencies_operator_date')) {
                $table->index(['operator_id', 'consumption_date'], 'idx_fuel_efficiencies_operator_date');
            }
            if (!$this->indexExists('fuel_efficiencies', 'idx_fuel_efficiencies_generation_unit_date')) {
                $table->index(['generation_unit_id', 'consumption_date'], 'idx_fuel_efficiencies_generation_unit_date');
            }
        });
    }
    
    private function indexExists(string $table, string $index): bool
    {
        $indexes = \DB::select("
            SELECT INDEX_NAME 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$table, $index]);
        
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_efficiencies', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropForeign(['generation_unit_id']);
            $table->dropIndex('idx_fuel_efficiencies_operator_date');
            $table->dropIndex('idx_fuel_efficiencies_generation_unit_date');
            $table->dropColumn(['operator_id', 'generation_unit_id']);
        });
    }
};

