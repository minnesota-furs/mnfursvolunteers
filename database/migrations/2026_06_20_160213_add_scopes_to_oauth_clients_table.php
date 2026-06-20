<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->json('scopes')->nullable()->after('redirect');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn('scopes');
        });
    }
};
