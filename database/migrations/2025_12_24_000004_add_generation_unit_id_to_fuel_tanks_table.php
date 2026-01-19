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
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->foreignId('generation_unit_id')
                ->constrained('generation_units')->cascadeOnDelete();
            $table->index('generation_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_tanks', function (Blueprint $table) {
            $table->dropForeign(['generation_unit_id']);
            $table->dropIndex(['generation_unit_id']);
            $table->dropColumn('generation_unit_id');
        });
    }
};

