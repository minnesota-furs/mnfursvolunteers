<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // fixed length since it’s always 6 chars
            $table->char('vol_code', 6)->unique()->nullable()
                ->comment('6-char alphanumeric volunteer code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['vol_code']);
            $table->dropColumn('vol_code');
        });
    }
};