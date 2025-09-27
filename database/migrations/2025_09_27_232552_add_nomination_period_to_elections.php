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
        Schema::table('elections', function (Blueprint $table) {
            // Add nomination period fields
            $table->datetime('nomination_start_date')->nullable()->after('description');
            $table->datetime('nomination_end_date')->nullable()->after('nomination_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['nomination_start_date', 'nomination_end_date']);
        });
    }
};