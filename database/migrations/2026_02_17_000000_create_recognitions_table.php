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
        Schema::create('recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->nullOnDelete();
            $table->string('name');
            $table->enum('type', ['General', 'Physical Award', 'Shoutout', 'Other'])->default('General');
            $table->date('date');
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('sector_id');
            $table->index('is_private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recognitions');
    }
};
