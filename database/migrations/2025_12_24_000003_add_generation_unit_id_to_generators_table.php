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
        Schema::table('generators', function (Blueprint $table) {
            $table->foreignId('generation_unit_id')->nullable()
                ->constrained('generation_units')->cascadeOnDelete();
            $table->index(['generation_unit_id', 'status_id'], 'idx_generators_unit_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->dropForeign(['generation_unit_id']);
            $table->dropIndex('idx_generators_unit_status');
            $table->dropColumn('generation_unit_id');
        });
    }
};

