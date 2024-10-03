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
        Schema::create('fiscal_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // e.g., "Fiscal Year 2023", "Q1 2024"
            $table->date('start_date');  // When the fiscal period starts
            $table->date('end_date');    // When the fiscal period ends
            $table->timestamps();
        });

        // Modify the volunteer_hours table to link to fiscal_ledgers
        Schema::table('volunteer_hours', function (Blueprint $table) {
            $table->unsignedBigInteger('fiscal_ledger_id')->nullable()->after('user_id');

            // Adding the foreign key constraint
            $table->foreign('fiscal_ledger_id')->references('id')->on('fiscal_ledgers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key and column from volunteer_hours
        Schema::table('volunteer_hours', function (Blueprint $table) {
            $table->dropForeign(['fiscal_ledger_id']);
            $table->dropColumn('fiscal_ledger_id');
        });

        // Drop the fiscal_ledgers table
        Schema::dropIfExists('fiscal_ledgers');
    }
};
