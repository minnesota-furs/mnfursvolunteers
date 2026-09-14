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
        Schema::table('volunteer_hours', function (Blueprint $table) {
            $table->boolean('counts_toward_perks')->default(true)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_hours', function (Blueprint $table) {
            $table->dropColumn('counts_toward_perks');
        });
    }
};
