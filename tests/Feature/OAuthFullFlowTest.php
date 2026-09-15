<?php

namespace Tests\Feature;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class OAuthFullFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_pkce_authorization_code_flow_returns_identity(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Volunteer',
            'first_name' => 'Jane',
            'last_name' => 'Volunteer',
            'email' => 'jane@example.com',
            'admin' => false,
        ]);

        $clients = app(ClientRepository::class);
        $client = $clients->create(null, 'Test App', 'https://example.com/callback', null, false, false, false);

        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $authorizeParams = [
            'client_id' => $client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'identity volunteer-info',
            'state' => 'xyz',
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ];

        // First load the consent screen (GET) to store the auth request in
        // the session, then approve it (POST) — mirrors the real two-step
        // browser flow.
        $this->actingAs($user)->get('/oauth/authorize?'.http_build_query($authorizeParams))->assertStatus(200);
        $authorize = $this->actingAs($user)->post('/oauth/authorize', $authorizeParams);

        $authorize->assertStatus(302);
        $location = $authorize->headers->get('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $params);
        $this->assertArrayHasKey('code', $params);

        $token = $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'redirect_uri' => 'https://example.com/callback',
            'code' => $params['code'],
            'code_verifier' => $verifier,
        ]);

        $token->assertStatus(200);
        $accessToken = $token->json('access_token');
        $this->assertNotEmpty($accessToken);

        $me = $this->withHeader('Authorization', "Bearer {$accessToken}")->get('/api/oauth/user');
        $me->assertStatus(200);
        $me->assertJson([
            'name' => 'Jane Volunteer',
            'email' => 'jane@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_oauth_user_endpoint_reports_sectors_from_department_memberships(): void
    {
        // Mirrors real accounts (often admins) that belong to departments
        // but have no single "primary" department/sector designated.
        $user = User::factory()->create([
            'primary_dept_id' => null,
            'primary_sector_id' => null,
        ]);

        $sector = Sector::factory()->create(['name' => 'Frolic']);
        $operations = Department::factory()->create(['name' => 'Operations', 'sector_id' => $sector->id]);
        $programming = Department::factory()->create(['name' => 'Programming', 'sector_id' => $sector->id]);
        $user->departments()->attach([$operations->id, $programming->id]);

        $clients = app(ClientRepository::class);
        $client = $clients->create(null, 'Test App', 'https://example.com/callback', null, false, false, true);

        $authorizeParams = [
            'client_id' => $client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'identity volunteer-info',
            'state' => 'xyz',
        ];

        $this->actingAs($user)->get('/oauth/authorize?'.http_build_query($authorizeParams))->assertStatus(200);
        $authorize = $this->actingAs($user)->post('/oauth/authorize', $authorizeParams);

        parse_str(parse_url($authorize->headers->get('Location'), PHP_URL_QUERY), $params);

        $tokenResponse = $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'redirect_uri' => 'https://example.com/callback',
            'code' => $params['code'],
        ]);

        $accessToken = $tokenResponse->json('access_token');

        $me = $this->withHeader('Authorization', "Bearer {$accessToken}")->get('/api/oauth/user');

        $me->assertOk();
        $me->assertJson([
            'sector' => null,
            'department' => null,
            'departments' => ['Operations', 'Programming'],
            'sectors' => ['Frolic'],
        ]);
    }

    public function test_oauth_user_endpoint_reports_active_custom_field_values(): void
    {
        $user = User::factory()->create();

        $tshirtField = CustomField::create([
            'name' => 'T-Shirt Size', 'field_key' => 'tshirt_size', 'field_type' => 'select',
            'options' => ['Small', 'Medium', 'Large'], 'is_active' => true, 'sort_order' => 1,
        ]);
        $retiredField = CustomField::create([
            'name' => 'Old Field', 'field_key' => 'old_field', 'field_type' => 'text',
            'is_active' => false, 'sort_order' => 2,
        ]);

        CustomFieldValue::create(['user_id' => $user->id, 'custom_field_id' => $tshirtField->id, 'value' => 'Large']);
        CustomFieldValue::create(['user_id' => $user->id, 'custom_field_id' => $retiredField->id, 'value' => 'should not appear']);

        $clients = app(ClientRepository::class);
        $client = $clients->create(null, 'Test App', 'https://example.com/callback', null, false, false, true);

        $authorizeParams = [
            'client_id' => $client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => 'identity volunteer-info',
            'state' => 'xyz',
        ];

        $this->actingAs($user)->get('/oauth/authorize?'.http_build_query($authorizeParams))->assertStatus(200);
        $authorize = $this->actingAs($user)->post('/oauth/authorize', $authorizeParams);

        parse_str(parse_url($authorize->headers->get('Location'), PHP_URL_QUERY), $params);

        $tokenResponse = $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'redirect_uri' => 'https://example.com/callback',
            'code' => $params['code'],
        ]);

        $accessToken = $tokenResponse->json('access_token');

        $me = $this->withHeader('Authorization', "Bearer {$accessToken}")->get('/api/oauth/user');

        $me->assertOk();
        $me->assertJson(['custom_fields' => ['tshirt_size' => 'Large']]);
        $me->assertJsonMissingPath('custom_fields.old_field');
    }
}
