<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop the old enum column and recreate with new values
            DB::statement("ALTER TABLE events MODIFY COLUMN visibility ENUM('public', 'unlisted', 'internal', 'draft') DEFAULT 'draft'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Revert back to original enum values
            DB::statement("ALTER TABLE events MODIFY COLUMN visibility ENUM('public', 'unlisted', 'draft') DEFAULT 'draft'");
        });
    }
};
