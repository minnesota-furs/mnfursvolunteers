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
        Schema::table('shift_signups', function (Blueprint $table) {
            $table->boolean('no_show')->default(false)->after('hours_logged_at');
            $table->timestamp('no_show_marked_at')->nullable()->after('no_show');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_signups', function (Blueprint $table) {
            $table->dropColumn(['no_show', 'no_show_marked_at']);
        });
    }
};
