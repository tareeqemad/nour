<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_portal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('app_no')->unique();
            $table->string('applicant_id')->nullable()->index();
            $table->string('applicant_name')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
            $table->string('status')->default('success'); // success, failed, skipped
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->useCurrent();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete(); // null = automated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_portal_requests');
    }
};
