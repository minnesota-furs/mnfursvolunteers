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
            $table->boolean('remind_via_telegram')->default(false)->after('remind_hour_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_off_event_rsvps', function (Blueprint $table) {
            $table->dropColumn('remind_via_telegram');
        });
    }
};
