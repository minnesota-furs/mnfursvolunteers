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
        Schema::table('users', function (Blueprint $table) {

            // Adding the new columns for department and sector
            $table->boolean('active')->default(true)->after('password');
            $table->unsignedBigInteger('primary_dept_id')->after('active')->nullable();
            $table->unsignedBigInteger('primary_sector_id')->after('active')->nullable();
            $table->text('notes')->nullable()->after('email');

            // Defining the foreign key constraints
            $table->foreign('primary_dept_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('primary_sector_id')->references('id')->on('sectors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Dropping the foreign key constraints and columns
            $table->dropForeign(['primary_dept_id']);
            $table->dropForeign(['primary_sector_id']);
            $table->dropColumn('primary_dept_id');
            $table->dropColumn('primary_sector_id');
        });
    }
};
