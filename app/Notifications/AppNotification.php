<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $title,
        private string $message,
        private ?string $url = null
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (method_exists($notifiable, 'hasTelegramLinked') && $notifiable->hasTelegramLinked()) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }

    public function toTelegram(object $notifiable): string
    {
        $text = '<b>' . e($this->title) . '</b>';

        if ($this->message) {
            $text .= "\n" . e($this->message);
        }

        if ($this->url) {
            $text .= "\n" . url($this->url);
        }

        return $text;
    }
}
