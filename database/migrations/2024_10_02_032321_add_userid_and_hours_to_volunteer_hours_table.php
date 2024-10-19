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
        Schema::table('volunteer_hours', function (Blueprint $table) {
            // Define the columns
            $table->text('description')->nullable()->after('id');
            $table->decimal('hours', 5, 2)->after('id');
            $table->unsignedBigInteger('primary_dept_id')->nullable()->after('id');
            $table->unsignedBigInteger('user_id')->after('id');
            $table->text('notes')->nullable()->after('description');
            $table->date('volunteer_date')->nullable()->after('notes');

            // Defining the foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('primary_dept_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_hours', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['primary_dept_id']);
            $table->dropColumn('hours');
            $table->dropColumn('description');
            $table->dropColumn('primary_dept_id');
            $table->dropColumn('notes');
            $table->dropColumn('volunteer_date');
        });
    }
};
