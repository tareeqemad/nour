<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minimum_charge_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('ampere')->comment('قيمة الأمبير: 2, 6, 10, 16');
            $table->unsignedTinyInteger('phase_type')->comment('نوع الفاز: 1=أحادي, 2=ثلاثي');
            $table->decimal('minimum_charge', 10, 2)->comment('الحد الأدنى بالشيكل');
            $table->timestamps();

            $table->unique(['ampere', 'phase_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minimum_charge_rules');
    }
};
