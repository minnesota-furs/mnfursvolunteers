<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_perk_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_perk_id')->constrained('volunteer_perks')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['volunteer_perk_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_perk_event');
    }
};
