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
        Schema::create('sector_one_off_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('one_off_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('sector_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['one_off_event_id', 'sector_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sector_one_off_event');
    }
};
