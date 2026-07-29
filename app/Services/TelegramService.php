<?php

namespace App\Services;

use App\Models\ApplicationSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TelegramService
{
    private ?string $botToken;

    public function __construct(private Client $http = new Client())
    {
        $this->botToken = ApplicationSetting::get('telegram_bot_token');
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken);
    }

    /**
     * Send a plain-text message to a chat. Returns false on any failure
     * (unlinked/blocked chat, misconfigured token, network error, etc.)
     * so callers can fire-and-forget without crashing notification delivery.
     *
     * @param  array|null  $replyMarkup  e.g. ['inline_keyboard' => [[['text' => 'Label', 'url' => '...']]]]
     */
    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($replyMarkup) {
            $params['reply_markup'] = $replyMarkup;
        }

        try {
            $response = $this->call('sendMessage', $params);

            return (bool) ($response['ok'] ?? false);
        } catch (GuzzleException $e) {
            \Log::warning('Telegram sendMessage failed', ['chat_id' => $chatId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Update a previously-sent message's text/keyboard in place (used after a
     * button action, e.g. to show "RSVP cancelled" and remove the buttons).
     */
    public function editMessageText(string $chatId, int $messageId, string $text, ?array $replyMarkup = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => $replyMarkup ?: ['inline_keyboard' => []],
        ];

        try {
            $response = $this->call('editMessageText', $params);

            return (bool) ($response['ok'] ?? false);
        } catch (GuzzleException $e) {
            \Log::warning('Telegram editMessageText failed', ['chat_id' => $chatId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Acknowledge a button tap. Telegram shows a loading spinner on the button
     * until this is called, and shows $text as a small toast if provided.
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = $this->call('answerCallbackQuery', [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);

            return (bool) ($response['ok'] ?? false);
        } catch (GuzzleException $e) {
            \Log::warning('Telegram answerCallbackQuery failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Verify the bot token is valid and fetch the bot's identity (used to
     * confirm the configured token works and to display the bot's username).
     */
    public function getMe(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->call('getMe');

            return ($response['ok'] ?? false) ? $response['result'] : null;
        } catch (GuzzleException $e) {
            \Log::warning('Telegram getMe failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getWebhookInfo(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->call('getWebhookInfo');

            return ($response['ok'] ?? false) ? $response['result'] : null;
        } catch (GuzzleException $e) {
            \Log::warning('Telegram getWebhookInfo failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Point Telegram's webhook at this app's webhook route, secured with a
     * per-install secret so only Telegram (with the right path) can post updates.
     */
    public function setWebhook(string $url, string $secretToken): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = $this->call('setWebhook', [
                'url' => $url,
                'secret_token' => $secretToken,
                'allowed_updates' => ['message', 'callback_query'],
            ]);

            return (bool) ($response['ok'] ?? false);
        } catch (GuzzleException $e) {
            \Log::warning('Telegram setWebhook failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function deleteWebhook(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = $this->call('deleteWebhook');

            return (bool) ($response['ok'] ?? false);
        } catch (GuzzleException $e) {
            \Log::warning('Telegram deleteWebhook failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function call(string $method, array $params = []): array
    {
        $response = $this->http->post("https://api.telegram.org/bot{$this->botToken}/{$method}", [
            'json' => $params,
            'timeout' => 10,
        ]);

        return json_decode($response->getBody()->getContents(), true) ?? [];
    }
}
