<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('constant_masters', function (Blueprint $table) {
            $table->id();
            $table->integer('constant_number')->unique();
            $table->string('constant_name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('constant_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('constant_master_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_detail_id')->nullable()->constrained('constant_details')->nullOnDelete();
            $table->string('label');
            $table->string('code')->nullable();
            $table->string('value')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constant_details');
        Schema::dropIfExists('constant_masters');
    }
};
