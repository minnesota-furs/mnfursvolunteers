<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class OAuthAuthorizeConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_consent_screen_uses_the_application_branded_view(): void
    {
        $user = User::factory()->create(['onboarded_at' => now()]);
        $client = app(ClientRepository::class)->create(null, 'Test App', 'https://example.com/callback');

        $response = $this->actingAs($user)->get('/oauth/authorize?'.http_build_query([
            'client_id' => $client->id,
            'redirect_uri' => 'https://example.com/callback',
            'response_type' => 'code',
            'scope' => '',
            'code_challenge' => 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            'code_challenge_method' => 'S256',
        ]));

        $response->assertOk();
        $response->assertSee('Test App is requesting access');
        $response->assertSee(route('passport.authorizations.approve'), false);
        $response->assertSee(route('passport.authorizations.deny'), false);
        $response->assertDontSee('passport-authorize', false);
        $response->assertDontSee('card-default', false);
    }
}
