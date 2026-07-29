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
        Schema::table('one_off_event_rsvps', function (Blueprint $table) {
            $table->boolean('remind_morning_of')->default(false)->after('user_id');
            $table->boolean('remind_hour_before')->default(false)->after('remind_morning_of');
            $table->timestamp('morning_reminder_sent_at')->nullable()->after('remind_hour_before');
            $table->timestamp('hour_before_reminder_sent_at')->nullable()->after('morning_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_off_event_rsvps', function (Blueprint $table) {
            $table->dropColumn([
                'remind_morning_of',
                'remind_hour_before',
                'morning_reminder_sent_at',
                'hour_before_reminder_sent_at',
            ]);
        });
    }
};
