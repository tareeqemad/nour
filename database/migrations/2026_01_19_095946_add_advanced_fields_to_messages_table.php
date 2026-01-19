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
        Schema::table('messages', function (Blueprint $table) {
            // إعادة التوجيه
            $table->unsignedBigInteger('forwarded_from_id')->nullable()->after('type');
            $table->foreign('forwarded_from_id')->references('id')->on('messages')->onDelete('set null');
            
            // CC/BCC
            $table->boolean('is_cc')->default(false)->after('forwarded_from_id');
            $table->boolean('is_bcc')->default(false)->after('is_cc');
            $table->unsignedBigInteger('original_message_id')->nullable()->after('is_bcc');
            $table->foreign('original_message_id')->references('id')->on('messages')->onDelete('set null');
            
            // تحديد الرسائل المهمة
            $table->boolean('is_important')->default(false)->after('is_read');
            $table->boolean('is_starred')->default(false)->after('is_important');
            
            // أرشفة الرسائل
            $table->boolean('is_archived')->default(false)->after('is_starred');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['forwarded_from_id']);
            $table->dropForeign(['original_message_id']);
            $table->dropColumn([
                'forwarded_from_id',
                'is_cc',
                'is_bcc',
                'original_message_id',
                'is_important',
                'is_starred',
                'is_archived',
                'archived_at',
            ]);
        });
    }
};
