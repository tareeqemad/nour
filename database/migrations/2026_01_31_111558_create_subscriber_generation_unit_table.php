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
        Schema::create('subscriber_generation_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('subscribers')->cascadeOnDelete();
            $table->foreignId('generation_unit_id')->constrained('generation_units')->cascadeOnDelete();
            $table->timestamps();
            
            // فهرس فريد لمنع التكرار
            $table->unique(['subscriber_id', 'generation_unit_id'], 'idx_subscriber_generation_unit_unique');
            $table->index('subscriber_id', 'idx_subscriber_generation_unit_subscriber');
            $table->index('generation_unit_id', 'idx_subscriber_generation_unit_generation_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriber_generation_unit');
    }
};
