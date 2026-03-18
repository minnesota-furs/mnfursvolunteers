<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_perk_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('volunteer_perk_id')->constrained('volunteer_perks')->cascadeOnDelete();
            $table->timestamp('redeemed_at');
            $table->timestamps();
            $table->unique(['user_id', 'volunteer_perk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_perk_redemptions');
    }
};
