<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit_number', 50)->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_name')->nullable();
            $table->string('owner_id_number')->nullable();
            $table->string('operator_id_number')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_approved')->default(false);
            $table->boolean('profile_completed')->default(false);
            $table->decimal('territory_radius_km', 8, 2)->default(5.00);
            $table->timestamps();
            $table->softDeletes();
            $table->index('name', 'idx_operators_name');
        });

        // Add FK on roles.operator_id -> operators.id
        Schema::table('roles', function (Blueprint $table) {
            $table->foreign('operator_id')->references('id')->on('operators')->nullOnDelete();
        });

        Schema::create('operator_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['operator_id', 'user_id']);
        });

        Schema::create('operator_territories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->cascadeOnDelete();
            $table->decimal('center_latitude', 10, 8);
            $table->decimal('center_longitude', 11, 8);
            $table->decimal('radius_km', 8, 2)->default(1.00);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_territories');
        Schema::dropIfExists('operator_user');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
        });

        Schema::dropIfExists('operators');
    }
};
