<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'VolunteerApp');
        $this->migrator->add('general.site_active', true);

        $this->migrator->add('postings.postings_active', true);
        $this->migrator->add('postings.inqury_active', true);

        $this->migrator->add('wordpress.wp_enabled', false);
    }
};
