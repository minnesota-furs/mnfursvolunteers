<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volunteer_perks', function (Blueprint $table) {
            $table->boolean('has_pass')->default(false)->after('is_active');
            $table->string('pass_label')->nullable()->after('has_pass');
            $table->boolean('has_physical_reward')->default(false)->after('pass_label');
            $table->string('reward_label')->nullable()->after('has_physical_reward');
        });
    }

    public function down(): void
    {
        Schema::table('volunteer_perks', function (Blueprint $table) {
            $table->dropColumn(['has_pass', 'pass_label', 'has_physical_reward', 'reward_label']);
        });
    }
};
