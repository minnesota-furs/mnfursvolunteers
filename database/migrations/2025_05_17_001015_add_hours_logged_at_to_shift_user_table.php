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
            $table->timestamp('hours_logged_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_signups', function (Blueprint $table) {
            $table->dropColumn('hours_logged_at');
        });
    }
};
