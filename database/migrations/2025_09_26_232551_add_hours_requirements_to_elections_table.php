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
            $table->decimal('min_candidate_hours', 8, 2)->default(0)->after('requires_approval');
            $table->decimal('min_voter_hours', 8, 2)->default(0)->after('min_candidate_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['min_candidate_hours', 'min_voter_hours']);
        });
    }
};
