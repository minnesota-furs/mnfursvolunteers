<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TelegramController;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

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
        $controller = new TelegramController();
        $offset = 0;

        $this->info('Polling Telegram for new messages...');
        $this->line('Press Ctrl+C to stop.');
        $this->line('==========================');
        while (true) {
            $updates = $telegram->getUpdates([
                'offset' => $offset,
                'timeout' => 10,
            ]);

            foreach ($updates as $data) {
                $this->info('New update received: ' . $data->getUpdateId());
                $update = new Update($data);

                $controller->handleUpdate($update);

                $offset = $update->getUpdateId() + 1;
            }

            sleep(1);
        }
    }
}
