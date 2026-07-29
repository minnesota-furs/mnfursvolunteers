<?php

namespace App\Console\Commands;

use App\Mail\OneOffEventReminder;
use App\Models\OneOffEvent;
use App\Models\OneOffEventRsvp;
use App\Models\User;
use App\Services\TelegramService;
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
    protected $description = 'Send opt-in email/Telegram reminders to volunteers RSVP\'d for Simple Volunteer Events';

    public function __construct(private TelegramService $telegram)
    {
        parent::__construct();
    }

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
            ->where(function ($query) {
                $query->where('remind_morning_of_email', true)
                    ->orWhere('remind_morning_of_telegram', true);
            })
            ->whereNull('morning_reminder_sent_at')
            ->whereHas('event', function ($query) use ($today, $endOfDay) {
                $query->where('start_time', '>=', $today)
                    ->where('start_time', '<=', $endOfDay);
            })
            ->get();

        $this->sendReminders($rsvps, 'morning', 'morning_reminder_sent_at', 'remind_morning_of_email', 'remind_morning_of_telegram');
    }

    protected function sendHourBeforeReminders(): void
    {
        $now = now();
        $windowEnd = now()->addHour();

        $rsvps = OneOffEventRsvp::with(['user', 'event'])
            ->where(function ($query) {
                $query->where('remind_hour_before_email', true)
                    ->orWhere('remind_hour_before_telegram', true);
            })
            ->whereNull('hour_before_reminder_sent_at')
            ->whereHas('event', function ($query) use ($now, $windowEnd) {
                $query->where('start_time', '>=', $now)
                    ->where('start_time', '<=', $windowEnd);
            })
            ->get();

        $this->sendReminders($rsvps, 'hour_before', 'hour_before_reminder_sent_at', 'remind_hour_before_email', 'remind_hour_before_telegram');
    }

    protected function sendReminders($rsvps, string $timing, string $sentAtColumn, string $emailColumn, string $telegramColumn): void
    {
        if ($rsvps->isEmpty()) {
            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($rsvps as $rsvp) {
            $user = $rsvp->user;
            $event = $rsvp->event;

            if (!$user || !$event) {
                continue;
            }

            $delivered = false;

            if ($rsvp->$emailColumn && $user->email) {
                try {
                    Mail::to($user->email)->send(new OneOffEventReminder($user, $event, $timing));
                    $delivered = true;
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("Failed to email {$timing} reminder to {$user->email}: {$e->getMessage()}");
                }
            }

            if ($rsvp->$telegramColumn && $user->hasTelegramLinked()) {
                $telegramSent = $this->telegram->sendMessage(
                    $user->telegram_chat_id,
                    $this->telegramReminderText($user, $event, $timing),
                    $this->telegramReminderKeyboard($event)
                );

                if ($telegramSent) {
                    $delivered = true;
                } else {
                    $failed++;
                    $this->error("Failed to send Telegram {$timing} reminder to user #{$user->id}");
                }
            }

            if ($delivered) {
                $rsvp->update([$sentAtColumn => now()]);
                $sent++;
            }
        }

        $this->info("Sent {$sent} '{$timing}' reminder(s)" . ($failed ? ", {$failed} failed." : '.'));
    }

    protected function telegramReminderText(User $user, OneOffEvent $event, string $timing): string
    {
        $intro = $timing === 'hour_before'
            ? "You're RSVP'd for an event starting in about an hour!"
            : "You're RSVP'd for an event happening today!";

        $text = "<b>Event Reminder</b>\n{$intro}\n\n<b>" . e($event->name) . '</b>';
        $text .= "\n" . $event->start_time->format('g:i A') . ($event->end_time ? ' - ' . $event->end_time->format('g:i A') : '');

        if ($event->location) {
            $text .= "\n" . e($event->location);
        }

        return $text;
    }

    protected function telegramReminderKeyboard(OneOffEvent $event): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => 'View Details', 'url' => route('simple-volunteer-events.show', $event)],
                ],
                [
                    ['text' => 'Cancel RSVP', 'callback_data' => "cancel_rsvp:{$event->id}"],
                ],
            ],
        ];
    }
}
