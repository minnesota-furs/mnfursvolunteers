<x-app-layout>
    @section('title', 'OAuth Provider Setup')
    <x-slot name="header">
        {{ __('OAuth Provider Setup') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('settings.index') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            &larr; Back to Settings
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('status') === 'oauth-client-created')
            <div class="px-4 py-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-sm text-green-800 dark:text-green-200 font-medium">OAuth client app created.</p>
                <p class="text-sm text-green-800 dark:text-green-200 mt-1">
                    Client ID: <span class="font-mono">{{ session('new_client_id') }}</span>
                    — this client has no secret and must authenticate using the Authorization Code grant with PKCE.
                </p>
            </div>
        @elseif(session('status') === 'oauth-client-deleted')
            <div class="px-4 py-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-sm text-green-800 dark:text-green-200">OAuth client app revoked.</p>
            </div>
        @elseif(session('status') === 'oauth-tokens-revoked')
            <div class="px-4 py-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-sm text-green-800 dark:text-green-200">All active tokens for that client have been revoked.</p>
            </div>
        @endif

        <!-- Create Client -->
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Register a New OAuth Client App</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Register a trusted application that should be allowed to offer "Sign in with {{ app_setting('app_name', config('app.name')) }}".
                Clients are created as public (PKCE) clients with no client secret.
            </p>

            <form method="POST" action="{{ route('settings.oauth-setup.store') }}" class="space-y-4 max-w-xl">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Application Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="OpenVolunteer App" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="redirect" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Redirect URI(s)</label>
                    <textarea name="redirect" id="redirect" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="https://example.com/oauth/callback" required>{{ old('redirect') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Comma-separated list of allowed redirect URIs the client may be sent back to after authorization.</p>
                    @error('redirect')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Allowed Scopes</span>
                    <p class="text-xs text-gray-500 mb-2">Limit what this client can request access to. Leave all checked to allow any defined scope.</p>
                    <div class="space-y-2">
                        @foreach($availableScopes as $scope)
                            <label class="flex items-start gap-2">
                                <input type="checkbox" name="scopes[]" value="{{ $scope->id }}"
                                    {{ in_array($scope->id, old('scopes', $availableScopes->pluck('id')->all())) ? 'checked' : '' }}
                                    class="mt-1 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-mono">{{ $scope->id }}</span> &mdash; {{ $scope->description }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('scopes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green/90">
                    Register Client App
                </button>
            </form>
        </div>

        <!-- Existing Clients -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h3 class="text-lg font-semibold mb-4">Registered OAuth Client Apps</h3>

                @if($clients->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No OAuth client apps have been registered yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($clients as $client)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $client->name }}</h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $client->revoked ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' }}">
                                            {{ $client->revoked ? 'Revoked' : 'Active' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-sm font-medium">
                                        @unless($client->revoked)
                                            @if($client->active_token_count > 0)
                                                <form action="{{ route('settings.oauth-setup.revoke-tokens', $client) }}" method="POST"
                                                    onsubmit="return confirm('Revoke all active tokens issued to this client? Signed-in users of this app will be signed out and need to re-authorize.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-yellow-700 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                                        Revoke Tokens
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('settings.oauth-setup.destroy', $client) }}" method="POST"
                                                onsubmit="return confirm('Revoke this OAuth client app? It will immediately stop being able to sign users in.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Revoke
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </div>

                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Client ID</dt>
                                        <dd class="font-mono text-gray-700 dark:text-gray-300">{{ $client->id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Active Tokens</dt>
                                        <dd class="text-gray-700 dark:text-gray-300">{{ $client->active_token_count }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Redirect URI(s)</dt>
                                        <dd class="text-gray-700 dark:text-gray-300 space-y-0.5">
                                            @foreach(explode(',', $client->redirect) as $uri)
                                                <div class="break-all">{{ $uri }}</div>
                                            @endforeach
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Scopes</dt>
                                        <dd>
                                            @if(is_array($client->scopes))
                                                <div class="flex flex-wrap gap-1 mt-0.5">
                                                    @foreach($client->scopes as $scope)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $scope }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-700 dark:text-gray-300">All scopes</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-slot name="right">
        <p class="py-4">
            Registering an app here lets it use {{ app_setting('app_name', config('app.name')) }} as an OAuth 2.0 identity provider
            via the Authorization Code grant with PKCE, no client secret required.
        </p>
        <ul class="text-sm space-y-1 list-disc list-inside text-gray-600 dark:text-gray-400">
            <li>Authorization endpoint: <code>/oauth/authorize</code></li>
            <li>Token endpoint: <code>/oauth/token</code></li>
            <li>User identity endpoint: <code>/api/oauth/user</code></li>
        </ul>
    </x-slot>
</x-app-layout>
