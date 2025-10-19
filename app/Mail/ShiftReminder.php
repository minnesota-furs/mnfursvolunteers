<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Models\User;

class ShiftReminder extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Collection $shifts;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Collection $shifts)
    {
        $this->user = $user;
        $this->shifts = $shifts;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $shiftCount = $this->shifts->count();
        $subject = $shiftCount === 1 
            ? 'Reminder: You have a shift coming up today' 
            : "Reminder: You have {$shiftCount} shifts coming up today";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.shift-reminder',
            with: [
                'user' => $this->user,
                'shifts' => $this->shifts,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
