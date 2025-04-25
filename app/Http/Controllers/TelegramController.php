<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Telegram\Bot\Api;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $telegram = new Api(config('services.telegram.bot_token'));
        $update = $telegram->getWebhookUpdate();

        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        \Log::info('Telegram message received', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);

        // respond
        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "You said: $text"
        ]);
    }
}
