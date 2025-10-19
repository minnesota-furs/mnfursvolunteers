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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_shift_reminders')->default(true)->after('active');
            $table->boolean('email_event_updates')->default(true)->after('email_shift_reminders');
            $table->boolean('email_hour_approvals')->default(true)->after('email_event_updates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_shift_reminders', 'email_event_updates', 'email_hour_approvals']);
        });
    }
};
