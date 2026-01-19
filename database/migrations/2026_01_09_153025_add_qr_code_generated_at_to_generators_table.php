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
            $table->timestamp('qr_code_generated_at')->nullable()->after('fuel_tanks_count')
                ->comment('تاريخ توليد QR Code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->dropColumn('qr_code_generated_at');
        });
    }
};
