<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('alt_phone', 10)->nullable()->after('phone');
            $table->string('box_number', 4)->nullable()->after('meter_number');
            $table->date('request_date')->nullable()->after('subscription_date');
            $table->text('notes')->nullable()->after('is_employee_subscription');
        });
    }

    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['alt_phone', 'box_number', 'request_date', 'notes']);
        });
    }
};
