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
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'email', 'sms', etc.
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('status')->default('sent'); // 'sent', 'failed', 'pending'
            $table->text('metadata')->nullable(); // JSON field for additional data
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
