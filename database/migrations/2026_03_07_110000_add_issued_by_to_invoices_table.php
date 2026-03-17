<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // تسجيل من أصدر الفاتورة ومتى (ضابط دورة الحياة #5)
            $table->unsignedBigInteger('issued_by')->nullable()->after('last_updated_by')
                  ->comment('معرّف المستخدم الذي أصدر الفاتورة نهائياً');
            $table->timestamp('issued_at')->nullable()->after('issued_by')
                  ->comment('تاريخ ووقت الإصدار النهائي للفاتورة');

            $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['issued_by']);
            $table->dropColumn(['issued_by', 'issued_at']);
        });
    }
};
