<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_sector')) {
            return;
        }

        Schema::create('event_sector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'sector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_sector');
    }
};
