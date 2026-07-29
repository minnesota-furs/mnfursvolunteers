<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\OneOffEvent;
use App\Models\OneOffEventRsvp;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    public function __construct(private TelegramService $telegram)
    {
    }

    /**
     * Receive an update from Telegram. The {secret} path segment must match
     * the per-install webhook secret so only Telegram's configured webhook
     * (not an arbitrary caller) can reach this route.
     */
    public function handle(Request $request, string $secret)
    {
        $expectedSecret = ApplicationSetting::get('telegram_webhook_secret');

        if (!$expectedSecret || !hash_equals($expectedSecret, $secret)) {
            abort(404);
        }

        if ($callbackQuery = $request->input('callback_query')) {
            $this->handleCallbackQuery($callbackQuery);

            return response()->noContent();
        }

        $message = $request->input('message');
        $chatId = $message['chat']['id'] ?? null;
        $username = $message['chat']['username'] ?? null;
        $text = trim($message['text'] ?? '');

        if (!$chatId || $text === '') {
            return response()->noContent();
        }

        if (Str::startsWith($text, '/start')) {
            $this->handleStart($chatId, $username, trim(Str::after($text, '/start')));
        } elseif (Str::startsWith($text, '/unlink')) {
            $this->handleUnlink($chatId);
        } else {
            $this->telegram->sendMessage($chatId, $this->helpText());
        }

        return response()->noContent();
    }

    /**
     * Handle an inline keyboard button tap. Currently only "Cancel RSVP" (cancel_rsvp:{eventId}).
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $callbackQueryId = $callbackQuery['id'] ?? null;
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $data = $callbackQuery['data'] ?? '';

        if (!$callbackQueryId || !$chatId) {
            return;
        }

        if (!Str::startsWith($data, 'cancel_rsvp:')) {
            $this->telegram->answerCallbackQuery($callbackQueryId);

            return;
        }

        $eventId = (int) Str::after($data, 'cancel_rsvp:');

        $user = User::where('telegram_chat_id', (string) $chatId)->first();

        if (!$user) {
            $this->telegram->answerCallbackQuery($callbackQueryId, "This chat isn't linked to an account.", true);

            return;
        }

        $event = OneOffEvent::find($eventId);
        $rsvp = $event ? OneOffEventRsvp::where('one_off_event_id', $eventId)->where('user_id', $user->id)->first() : null;

        if (!$event || !$rsvp) {
            $this->telegram->answerCallbackQuery($callbackQueryId, "You're not RSVP'd to this event (maybe already cancelled).", true);

            return;
        }

        $rsvp->delete();

        $this->telegram->answerCallbackQuery($callbackQueryId, 'RSVP cancelled.');

        if ($messageId) {
            $this->telegram->editMessageText(
                (string) $chatId,
                $messageId,
                "❌ <b>RSVP cancelled</b> for \"" . e($event->name) . '".'
            );
        }
    }

    private function handleStart(string|int $chatId, ?string $username, string $token): void
    {
        if ($token === '') {
            $this->telegram->sendMessage((string) $chatId, $this->helpText());

            return;
        }

        $user = User::where('telegram_link_token', $token)->first();

        if (!$user || !$user->hasValidTelegramLinkToken()) {
            $this->telegram->sendMessage((string) $chatId,
                "This link code is invalid or has expired. Generate a new one from your profile page and try again.");

            return;
        }

        $user->completeTelegramLink((string) $chatId, $username);

        $appName = ApplicationSetting::get('app_name', config('app.name'));
        $this->telegram->sendMessage((string) $chatId,
            "You're linked! You'll now receive notifications from {$appName} here. Send /unlink at any time to disconnect.");
    }

    private function handleUnlink(string|int $chatId): void
    {
        $user = User::where('telegram_chat_id', (string) $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage((string) $chatId, "This chat isn't linked to an account.");

            return;
        }

        $user->unlinkTelegram();
        $this->telegram->sendMessage((string) $chatId, "Your account has been unlinked. You won't receive further notifications here.");
    }

    private function helpText(): string
    {
        $appName = ApplicationSetting::get('app_name', config('app.name'));

        return "This bot delivers notifications from {$appName}. To link your account, generate a link from your profile page and open it, or send /unlink to disconnect an already-linked account.";
    }
}
