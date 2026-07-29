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
        Schema::create('one_off_event_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('one_off_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('remind_morning_of_email')->default(false);
            $table->boolean('remind_morning_of_telegram')->default(false);
            $table->boolean('remind_hour_before_email')->default(false);
            $table->boolean('remind_hour_before_telegram')->default(false);
            $table->timestamp('morning_reminder_sent_at')->nullable();
            $table->timestamp('hour_before_reminder_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['one_off_event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_off_event_reminders');
    }
};
