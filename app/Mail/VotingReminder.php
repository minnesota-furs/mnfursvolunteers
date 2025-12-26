<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Election;

class VotingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Election $election;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Election $election)
    {
        $this->user = $user;
        $this->election = $election;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: Vote in {$this->election->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate unsubscribe token
        $unsubscribeToken = md5($this->user->email . config('app.key'));
        $unsubscribeUrl = route('unsubscribe.elections', [
            'user' => $this->user->id,
            'token' => $unsubscribeToken,
        ]);

        return new Content(
            view: 'emails.voting-reminder',
            with: [
                'unsubscribeUrl' => $unsubscribeUrl,
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
