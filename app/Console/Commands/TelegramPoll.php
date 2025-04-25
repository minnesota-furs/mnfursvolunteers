<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;

class TelegramPoll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Telegram for new messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $telegram = new Api(config('services.telegram.bot_token'));
        $offset = 0;

        while (true) {
            $updates = $telegram->getUpdates([
                'offset' => $offset,
                'timeout' => 10,
            ]);

            foreach ($updates as $update) {
                $message = $update['message'] ?? null;

                if ($message) {
                    $chatId = $message['chat']['id'];
                    $text = $message['text'] ?? '';

                    \Log::info('Telegram message received', [
                        'chat_id' => $chatId,
                        'text' => $text,
                    ]);

                    // Simple response
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "You said: $text",
                    ]);
                }

                $offset = $update['update_id'] + 1;
            }

            sleep(1); // avoid rapid-fire polling
        }
    }
}
