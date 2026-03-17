<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // تعديل العمود ليقبل NULL - الـ unique index موجود مسبقاً ومحفوظ
        \DB::statement("ALTER TABLE invoices MODIFY invoice_number VARCHAR(30) NULL COMMENT 'رقم الفاتورة (يُنشأ عند الإصدار النهائي)'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE invoices MODIFY invoice_number VARCHAR(30) NOT NULL COMMENT 'رقم الفاتورة (يُنشأ تلقائياً عند الإصدار)'");
    }
};
