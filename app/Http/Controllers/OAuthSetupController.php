<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class OAuthSetupController extends Controller
{
    public function index()
    {
        $clients = Client::where('personal_access_client', false)
            ->where('password_client', false)
            ->orderBy('name')
            ->get()
            ->each(function (Client $client) {
                $client->active_token_count = Token::where('client_id', $client->id)
                    ->where('revoked', false)
                    ->count();
            });

        $availableScopes = Passport::scopes();

        return view('settings.oauth-setup', compact('clients', 'availableScopes'));
    }

    public function store(Request $request, ClientRepository $clients)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'redirect' => ['required', 'string'],
            'scopes' => ['array'],
            'scopes.*' => ['string', 'in:'.implode(',', Passport::scopeIds())],
        ]);

        $redirects = collect(explode(',', $validated['redirect']))
            ->map(fn ($url) => trim($url))
            ->filter()
            ->implode(',');

        $client = $clients->create(
            userId: null,
            name: $validated['name'],
            redirect: $redirects,
            confidential: false,
        );

        // An empty/missing selection is treated as "all scopes" (column
        // left null) rather than "no scopes", since unchecking everything
        // by accident shouldn't silently break the client.
        if (! empty($validated['scopes'])) {
            $client->forceFill(['scopes' => $validated['scopes']])->save();
        }

        return redirect()->route('settings.oauth-setup')
            ->with('status', 'oauth-client-created')
            ->with('new_client_id', $client->id);
    }

    public function destroy(Client $client, ClientRepository $clients)
    {
        // Passport clients are revoked rather than removed so existing
        // issued tokens can still be looked up and invalidated.
        $clients->delete($client);

        return redirect()->route('settings.oauth-setup')
            ->with('status', 'oauth-client-deleted');
    }

    public function revokeTokens(Client $client)
    {
        Token::where('client_id', $client->id)
            ->where('revoked', false)
            ->update(['revoked' => true]);

        return redirect()->route('settings.oauth-setup')
            ->with('status', 'oauth-tokens-revoked');
    }
}
