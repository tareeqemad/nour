<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // أولاً: ملء القيم الفارغة بقيم افتراضية
        DB::table('subscribers')
            ->whereNull('phone')
            ->update(['phone' => 'غير محدد']);

        DB::table('subscribers')
            ->whereNull('address')
            ->update(['address' => 'غير محدد']);

        // ثانياً: تعديل الأعمدة لتصبح إجبارية
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('phone')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }
};
