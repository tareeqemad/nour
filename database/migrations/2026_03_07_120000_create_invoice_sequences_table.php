<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->string('prefix', 20)->primary()->comment('مفتاح التسلسل مثال: INV-202603');
            $table->unsignedInteger('last_seq')->default(0)->comment('آخر رقم تسلسلي مُستخدم');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
