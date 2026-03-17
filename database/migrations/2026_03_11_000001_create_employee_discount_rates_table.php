<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_discount_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('discount_rate', 5, 2);  // نسبة الخصم مثل 10.00 أو 20.00
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_date', 'end_date', 'is_active'], 'emp_discount_rate_search_idx');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_discount_rates');
    }
};
