<?php

namespace App\Console\Commands;

use App\Services\ConcatSyncService;
use Illuminate\Console\Command;

class SyncConcatRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concat:sync-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile ConCat role grants against current sector/department membership';

    /**
     * Execute the console command.
     */
    public function handle(ConcatSyncService $syncService): int
    {
        $result = $syncService->syncAll();

        $this->info("ConCat sync complete: {$result['granted']} granted, {$result['revoked']} revoked, {$result['unmatched']} unmatched.");

        return self::SUCCESS;
    }
}
