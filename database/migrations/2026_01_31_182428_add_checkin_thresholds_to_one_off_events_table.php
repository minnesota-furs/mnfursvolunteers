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
        Schema::table('one_off_events', function (Blueprint $table) {
            $table->unsignedTinyInteger('checkin_hours_before')->default(1)->after('auto_credit_hours');
            $table->unsignedTinyInteger('checkin_hours_after')->default(12)->after('checkin_hours_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('one_off_events', function (Blueprint $table) {
            $table->dropColumn(['checkin_hours_before', 'checkin_hours_after']);
        });
    }
};
