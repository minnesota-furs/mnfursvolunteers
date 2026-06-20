<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class OAuthSetupSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_oauth_setup_page(): void
    {
        $admin = User::factory()->create(['admin' => true]);

        $response = $this->actingAs($admin)->get('/settings/oauth-setup');

        $response->assertStatus(200);
        $response->assertSee('Register a New OAuth Client App');
    }

    public function test_admin_can_create_and_delete_oauth_client(): void
    {
        $admin = User::factory()->create(['admin' => true]);

        $response = $this->actingAs($admin)->post('/settings/oauth-setup', [
            'name' => 'Test App',
            'redirect' => 'https://example.com/callback',
        ]);

        $response->assertRedirect(route('settings.oauth-setup'));
        $client = Client::where('name', 'Test App')->first();
        $this->assertNotNull($client);
        $this->assertNull($client->secret);
        $this->assertFalse($client->confidential());

        $delete = $this->actingAs($admin)->delete("/settings/oauth-setup/{$client->id}");
        $delete->assertRedirect(route('settings.oauth-setup'));
        $this->assertDatabaseHas('oauth_clients', ['id' => $client->id, 'revoked' => true]);
    }

    public function test_non_admin_cannot_access_oauth_setup(): void
    {
        $user = User::factory()->create(['admin' => false]);

        $response = $this->actingAs($user)->get('/settings/oauth-setup');

        $response->assertStatus(403);
    }

    public function test_oauth_authorize_endpoint_requires_login(): void
    {
        User::factory()->create(); // avoid the setup-wizard redirect when no users exist

        $clients = app(ClientRepository::class);
        $client = $clients->create(null, 'Test App', 'https://example.com/callback', null, false, false, false);

        $response = $this->get('/oauth/authorize?'.http_build_query([
            'client_id' => $client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => '',
            'code_challenge' => 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            'code_challenge_method' => 'S256',
        ]));

        $response->assertRedirect('/login');
    }
}
