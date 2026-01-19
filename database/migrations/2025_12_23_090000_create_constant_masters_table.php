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
        Schema::create('constant_masters', function (Blueprint $table) {
            $table->id();
            $table->integer('constant_number')->unique(); // رقم الثابت (1, 2, 3...)
            $table->string('constant_name'); // اسم الثابت (المحافظة، المدينة، إلخ)
            $table->text('description')->nullable(); // وصف الثابت
            $table->boolean('is_active')->default(true); // هل الثابت نشط؟
            $table->integer('order')->default(0); // ترتيب العرض
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constant_masters');
    }
};





