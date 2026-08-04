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
        Schema::create('staff_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_check_in_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('completed_items')->nullable();
            $table->longText('signature_data')->nullable();
            $table->foreignId('checked_in_by')->constrained('users');
            $table->timestamp('checked_in_at');
            $table->timestamps();

            $table->unique(['staff_check_in_session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_check_ins');
    }
};
