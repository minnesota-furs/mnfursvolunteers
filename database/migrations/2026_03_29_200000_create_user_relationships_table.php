<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['favorite', 'avoid']);
            $table->timestamps();

            $table->unique(['user_id', 'target_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_relationships');
    }
};
