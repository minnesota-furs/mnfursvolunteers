<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volunteer_perks', function (Blueprint $table) {
            $table->foreignId('perk_set_id')->nullable()->after('id')
                ->constrained('volunteer_perk_sets')->nullOnDelete();
            $table->dropForeign(['fiscal_ledger_id']);
            $table->dropColumn('fiscal_ledger_id');
        });
    }

    public function down(): void
    {
        Schema::table('volunteer_perks', function (Blueprint $table) {
            $table->dropForeign(['perk_set_id']);
            $table->dropColumn('perk_set_id');
            $table->foreignId('fiscal_ledger_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
