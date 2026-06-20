<?php

namespace Tests\Feature;

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
}
