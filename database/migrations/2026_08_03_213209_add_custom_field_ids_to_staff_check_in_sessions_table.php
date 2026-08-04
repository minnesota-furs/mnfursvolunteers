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
        Schema::table('staff_check_in_sessions', function (Blueprint $table) {
            $table->json('custom_field_ids')->nullable()->after('checklist_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_check_in_sessions', function (Blueprint $table) {
            $table->dropColumn('custom_field_ids');
        });
    }
};
