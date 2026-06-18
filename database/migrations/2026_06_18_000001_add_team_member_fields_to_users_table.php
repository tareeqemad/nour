<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * دعم "عضو فريق بدون حساب مستخدم":
     *  - has_login_account: هل للعضو حساب دخول للنظام (false = ضمن الفريق فقط)
     *  - job_title: المسمى الوظيفي (فني، مشرف، مراقب، عامل...) للاستفادة في المهام والتقارير
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_login_account')->default(true)->after('password');
            $table->string('job_title')->nullable()->after('name_en');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_login_account', 'job_title']);
        });
    }
};
