<?php

namespace App\Console\Commands;

use App\Mail\OneOffEventReminder;
use App\Models\OneOffEventRsvp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOneOffEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple-events:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send opt-in email reminders to volunteers RSVP\'d for Simple Volunteer Events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendMorningReminders();
        $this->sendHourBeforeReminders();

        return 0;
    }

    protected function sendMorningReminders(): void
    {
        $today = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        $rsvps = OneOffEventRsvp::with(['user', 'event'])
            ->where('remind_morning_of', true)
            ->whereNull('morning_reminder_sent_at')
            ->whereHas('event', function ($query) use ($today, $endOfDay) {
                $query->where('start_time', '>=', $today)
                    ->where('start_time', '<=', $endOfDay);
            })
            ->get();

        $this->sendReminders($rsvps, 'morning', 'morning_reminder_sent_at');
    }

    protected function sendHourBeforeReminders(): void
    {
        $now = now();
        $windowEnd = now()->addHour();

        $rsvps = OneOffEventRsvp::with(['user', 'event'])
            ->where('remind_hour_before', true)
            ->whereNull('hour_before_reminder_sent_at')
            ->whereHas('event', function ($query) use ($now, $windowEnd) {
                $query->where('start_time', '>=', $now)
                    ->where('start_time', '<=', $windowEnd);
            })
            ->get();

        $this->sendReminders($rsvps, 'hour_before', 'hour_before_reminder_sent_at');
    }

    protected function sendReminders($rsvps, string $timing, string $sentAtColumn): void
    {
        if ($rsvps->isEmpty()) {
            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($rsvps as $rsvp) {
            $user = $rsvp->user;
            $event = $rsvp->event;

            if (!$user || !$user->email || !$event) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new OneOffEventReminder($user, $event, $timing));
                $rsvp->update([$sentAtColumn => now()]);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $this->error("Failed to send {$timing} reminder to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} '{$timing}' reminder(s)" . ($failed ? ", {$failed} failed." : '.'));
    }
}
