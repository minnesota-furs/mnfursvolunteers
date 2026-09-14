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
            $table->foreignId('perk_set_id')->nullable()->after('counts_toward_perks')
                ->constrained('volunteer_perk_sets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_hours', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perk_set_id');
        });
    }
};
