<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildSqliteEndTime('NULL');

            return;
        }

        DB::statement('ALTER TABLE one_off_events MODIFY COLUMN end_time DATETIME NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildSqliteEndTime("NOT NULL DEFAULT '1970-01-01 00:00:00'");

            return;
        }

        DB::statement('ALTER TABLE one_off_events MODIFY COLUMN end_time DATETIME NOT NULL');
    }

    /**
     * SQLite cannot modify a column in place, so the column is rebuilt there.
     * SQLite is only used for the test database, so existing values are not preserved.
     */
    private function rebuildSqliteEndTime(string $definition): void
    {
        DB::statement('ALTER TABLE one_off_events DROP COLUMN end_time');
        DB::statement("ALTER TABLE one_off_events ADD COLUMN end_time DATETIME {$definition}");
    }
};
