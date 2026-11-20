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
        $this->setVisibilityOptions(['public', 'unlisted', 'internal', 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setVisibilityOptions(['public', 'unlisted', 'draft']);
    }

    /**
     * SQLite cannot modify a column in place, so the column is rebuilt there.
     * SQLite is only used for the test database, so existing values are not preserved.
     *
     * @param  list<string>  $options
     */
    private function setVisibilityOptions(array $options): void
    {
        $quotedOptions = implode(', ', array_map(fn (string $option): string => "'{$option}'", $options));

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE events DROP COLUMN visibility');
            DB::statement("ALTER TABLE events ADD COLUMN visibility VARCHAR NOT NULL DEFAULT 'draft' CHECK (visibility IN ({$quotedOptions}))");

            return;
        }

        DB::statement("ALTER TABLE events MODIFY COLUMN visibility ENUM({$quotedOptions}) DEFAULT 'draft'");
    }
};
