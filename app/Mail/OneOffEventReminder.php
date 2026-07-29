<?php

namespace App\Mail;

use App\Models\OneOffEvent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneOffEventReminder extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public OneOffEvent $event;
    public string $timing;

    /**
     * @param  string  $timing  'morning' or 'hour_before'
     */
    public function __construct(User $user, OneOffEvent $event, string $timing)
    {
        $this->user = $user;
        $this->event = $event;
        $this->timing = $timing;
    }

    public function envelope(): Envelope
    {
        $subject = $this->timing === 'hour_before'
            ? "Reminder: {$this->event->name} starts in about an hour"
            : "Reminder: {$this->event->name} is today";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.one-off-event-reminder',
            with: [
                'user' => $this->user,
                'event' => $this->event,
                'timing' => $this->timing,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
