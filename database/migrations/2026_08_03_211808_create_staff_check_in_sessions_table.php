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
        Schema::create('staff_check_in_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('scope');
            $table->foreignId('sector_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->json('checklist_items')->nullable();
            $table->boolean('collect_signature')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_check_in_sessions');
    }
};
