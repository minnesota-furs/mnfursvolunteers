<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\TelegramLinkToken;
use App\Models\User;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $update = app('telegram')->getWebhookUpdate();
        $this->handleUpdate($update);

        return response()->json(['ok' => true]);
    }

    public function handleUpdate(Update $update)
    {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        if (Str::startsWith($text, '/start ')) {
            $token = trim(Str::after($text, '/start '));
        
            $link = TelegramLinkToken::where('token', $token)->first();
        
            if ($link && $link->user) {
                $link->user->update([
                    'telegram_id' => $chatId,
                ]);
        
                $link->delete(); // optional cleanup
        
                return app('telegram')->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ You're now linked to your account!",
                ]);
            }
        
            return app('telegram')->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Invalid or expired link code.",
            ]);
        }

        if (Str::startsWith($text, '/unlink')) {
            $user = User::where('telegram_id', $chatId)->first();
        
            if ($user) {
                $user->update([
                    'telegram_id' => null,
                ]);
        
                return app('telegram')->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "🗑️ Your Telegram account has been unlinked from your user account.",
                ]);
            }
        
            return app('telegram')->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ This Telegram account isn't currently linked to any user.",
            ]);
        }

        // fallback
        if (trim($text) === '/start') {
            return app('telegram')->sendMessage([
                'chat_id' => $chatId,
                'text' => "👋 Welcome! To link your account, please use the button on the website.",
            ]);
        }
    }
}
