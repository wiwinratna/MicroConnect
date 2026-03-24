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
        Schema::table('piutang', function (Blueprint $table) {
            $table->time('reminder_send_time')->nullable()->default('09:00:00')->after('catatan');
            $table->boolean('email_reminder_enabled')->default(false)->after('reminder_send_time');
            $table->timestamp('last_email_reminder_sent_at')->nullable()->after('email_reminder_enabled');
            $table->integer('email_reminder_count')->default(0)->after('last_email_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piutang', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_send_time',
                'email_reminder_enabled',
                'last_email_reminder_sent_at',
                'email_reminder_count'
            ]);
        });
    }
};
