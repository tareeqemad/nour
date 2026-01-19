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
        Schema::create('compliance_safeties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();

            // شهادة السلامة - تخزن ID من constant_details، ثابت Master رقم 13 (حالة شهادة السلامة)
            $table->foreignId('safety_certificate_status_id')
                ->constrained('constant_details')->cascadeOnDelete()
                ->comment('ID من constant_details - ثابت Master رقم 13 (حالة شهادة السلامة)');

            // تاريخ آخر زيارة تفقدية
            $table->date('last_inspection_date')->nullable();

            // الجهة المنفذة
            $table->string('inspection_authority')->nullable(); // الجهة المنفذة

            // نتيجة الزيارة
            $table->text('inspection_result')->nullable(); // نتيجة الزيارة

            // المخالفات المسجلة
            $table->text('violations')->nullable(); // المخالفات المسجلة

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_safeties');
    }
};
