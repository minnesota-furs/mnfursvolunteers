<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('Authorize Application') }} - {{ app_name() }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ app_favicon() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
            <a href="/">
                <img src="{{ app_logo() }}" alt="{{ app_name() }}" class="h-16 w-auto">
            </a>

            <div class="mt-8 w-full max-w-md rounded-lg bg-white shadow sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <h1 class="text-lg font-medium text-gray-900">
                        {{ __(':client is requesting access', ['client' => $client->name]) }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('This will let :client sign you in and access your :app account.', ['client' => $client->name, 'app' => app_name()]) }}
                    </p>

                    @if (count($scopes) > 0)
                        <ul class="mt-6 space-y-3">
                            @foreach ($scopes as $scope)
                                <li class="flex items-start gap-2 text-sm text-gray-700">
                                    <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-brand-green" aria-hidden="true" />
                                    <span>{{ $scope->description }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-8 flex items-center gap-3">
                        <form method="post" action="{{ route('passport.authorizations.approve') }}" class="flex-1">
                            @csrf

                            <input type="hidden" name="state" value="{{ $request->state }}">
                            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                                {{ __('Authorize') }}
                            </button>
                        </form>

                        <form method="post" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                            @csrf
                            @method('DELETE')

                            <input type="hidden" name="state" value="{{ $request->state }}">
                            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-gray-500">
                {{ __('Signed in as :email', ['email' => $user->email]) }}
            </p>
        </div>
    </body>
</html>
