<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('one_off_event_rsvps', function (Blueprint $table) {
            $table->boolean('remind_morning_of_email')->default(false)->after('user_id');
            $table->boolean('remind_morning_of_telegram')->default(false)->after('remind_morning_of_email');
            $table->boolean('remind_hour_before_email')->default(false)->after('remind_morning_of_telegram');
            $table->boolean('remind_hour_before_telegram')->default(false)->after('remind_hour_before_email');
        });

        // Backfill: the old single remind_morning_of/remind_hour_before columns were email-only
        // reminder toggles; remind_via_telegram was a single flag that rode along with whichever
        // of those timings was already on. Split them into independent per-channel columns.
        DB::table('one_off_event_rsvps')->orderBy('id')->chunk(200, function ($rsvps) {
            foreach ($rsvps as $rsvp) {
                DB::table('one_off_event_rsvps')->where('id', $rsvp->id)->update([
                    'remind_morning_of_email' => (bool) $rsvp->remind_morning_of,
                    'remind_morning_of_telegram' => (bool) $rsvp->remind_morning_of && (bool) $rsvp->remind_via_telegram,
                    'remind_hour_before_email' => (bool) $rsvp->remind_hour_before,
                    'remind_hour_before_telegram' => (bool) $rsvp->remind_hour_before && (bool) $rsvp->remind_via_telegram,
                ]);
            }
        });

        Schema::table('one_off_event_rsvps', function (Blueprint $table) {
            $table->dropColumn(['remind_morning_of', 'remind_hour_before', 'remind_via_telegram']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_off_event_rsvps', function (Blueprint $table) {
            $table->boolean('remind_morning_of')->default(false)->after('user_id');
            $table->boolean('remind_hour_before')->default(false)->after('remind_morning_of');
            $table->boolean('remind_via_telegram')->default(false)->after('remind_hour_before');
        });

        DB::table('one_off_event_rsvps')->orderBy('id')->chunk(200, function ($rsvps) {
            foreach ($rsvps as $rsvp) {
                DB::table('one_off_event_rsvps')->where('id', $rsvp->id)->update([
                    'remind_morning_of' => (bool) $rsvp->remind_morning_of_email,
                    'remind_hour_before' => (bool) $rsvp->remind_hour_before_email,
                    'remind_via_telegram' => (bool) $rsvp->remind_morning_of_telegram || (bool) $rsvp->remind_hour_before_telegram,
                ]);
            }
        });

        Schema::table('one_off_event_rsvps', function (Blueprint $table) {
            $table->dropColumn([
                'remind_morning_of_email',
                'remind_morning_of_telegram',
                'remind_hour_before_email',
                'remind_hour_before_telegram',
            ]);
        });
    }
};
