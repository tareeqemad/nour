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
        // ملء القيم الفارغة قبل تحويل العمود لـ NOT NULL
        \DB::statement("UPDATE invoices SET invoice_number = CONCAT('DRAFT-', id) WHERE invoice_number IS NULL OR invoice_number = ''");
        \DB::statement("ALTER TABLE invoices MODIFY invoice_number VARCHAR(30) NOT NULL COMMENT 'رقم الفاتورة (يُنشأ تلقائياً عند الإصدار)'");
    }
};
