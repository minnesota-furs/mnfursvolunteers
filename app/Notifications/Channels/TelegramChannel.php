<?php

namespace App\Notifications\Channels;

use App\Services\TelegramService;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    public function __construct(private TelegramService $telegram)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        $chatId = $notifiable->telegram_chat_id ?? null;

        if (!$chatId || !method_exists($notification, 'toTelegram')) {
            return;
        }

        $text = $notification->toTelegram($notifiable);

        if ($text) {
            $this->telegram->sendMessage($chatId, $text);
        }
    }
}
