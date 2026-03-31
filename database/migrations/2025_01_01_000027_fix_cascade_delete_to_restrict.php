<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إصلاح: تغيير cascadeOnDelete إلى restrictOnDelete
     * لمنع حذف سجلات الصيانة والسلامة عند حذف ثابت بالخطأ
     */
    public function up(): void
    {
        // إصلاح maintenance_records.maintenance_type_id
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropForeign(['maintenance_type_id']);
            $table->foreign('maintenance_type_id')
                ->references('id')->on('constant_details')
                ->restrictOnDelete();
        });

        // إصلاح compliance_safeties.safety_certificate_status_id
        Schema::table('compliance_safeties', function (Blueprint $table) {
            $table->dropForeign(['safety_certificate_status_id']);
            $table->foreign('safety_certificate_status_id')
                ->references('id')->on('constant_details')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropForeign(['maintenance_type_id']);
            $table->foreign('maintenance_type_id')
                ->references('id')->on('constant_details')
                ->cascadeOnDelete();
        });

        Schema::table('compliance_safeties', function (Blueprint $table) {
            $table->dropForeign(['safety_certificate_status_id']);
            $table->foreign('safety_certificate_status_id')
                ->references('id')->on('constant_details')
                ->cascadeOnDelete();
        });
    }
};
