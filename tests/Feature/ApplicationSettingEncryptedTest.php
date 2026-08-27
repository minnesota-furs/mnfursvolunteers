<?php

use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\DB;

it('round-trips an encrypted setting value', function () {
    ApplicationSetting::set('concat_client_secret', 'super-secret-value', 'encrypted', 'ConCat OAuth client secret', 'integrations');

    expect(ApplicationSetting::get('concat_client_secret'))->toBe('super-secret-value');

    $raw = DB::table('application_settings')->where('key', 'concat_client_secret')->value('value');
    expect($raw)->not->toBe('super-secret-value');
});

it('returns null for a corrupt encrypted value instead of throwing', function () {
    DB::table('application_settings')->insert([
        'key' => 'concat_client_secret',
        'value' => 'not-a-valid-encrypted-payload',
        'type' => 'encrypted',
        'group' => 'integrations',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(ApplicationSetting::get('concat_client_secret'))->toBeNull();
});
