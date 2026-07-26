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
        Schema::table('shift_waitlists', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('user_id');
            $table->boolean('auto_assign')->default(false)->after('notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_waitlists', function (Blueprint $table) {
            $table->dropColumn(['notified_at', 'auto_assign']);
        });
    }
};
