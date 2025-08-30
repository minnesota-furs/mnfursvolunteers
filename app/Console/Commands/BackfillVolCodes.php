<?php

// app/Console/Commands/BackfillVolCodes.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BackfillVolCodes extends Command
{
    protected $signature = 'users:backfill-vol-codes {--chunk=500}';
    protected $description = 'Generate a unique vol_code for users missing one';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');

        User::query()
            ->whereNull('vol_code')
            ->orderBy('id')
            ->chunkById($chunk, function ($users) {
                foreach ($users as $user) {
                    DB::transaction(function () use ($user) {
                        // set and save; trait method ensures uniqueness
                        $user->vol_code = User::newUniqueVolCode();
                        $user->save();
                    }, 3);
                }
                $this->info("Processed {$users->count()} users…");
            });

        $this->info('Backfill complete.');
        return self::SUCCESS;
    }
}
