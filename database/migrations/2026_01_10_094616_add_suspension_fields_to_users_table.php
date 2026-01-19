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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('status')->comment('تاريخ تعطيل الحساب');
            $table->text('suspended_reason')->nullable()->after('suspended_at')->comment('سبب التعطيل');
            $table->foreignId('suspended_by')->nullable()->after('suspended_reason')->constrained('users')->nullOnDelete()->comment('المستخدم الذي قام بالتعطيل');
            $table->index('suspended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropIndex(['suspended_at']);
            $table->dropColumn(['suspended_at', 'suspended_reason', 'suspended_by']);
        });
    }
};
