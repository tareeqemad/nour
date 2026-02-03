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
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('governorate_name')->nullable()->after('phone')->comment('اسم المحافظة (مأخوذ من بيانات المشغل)');
            $table->index('governorate_name', 'idx_subscribers_governorate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropIndex('idx_subscribers_governorate');
            $table->dropColumn('governorate_name');
        });
    }
};
