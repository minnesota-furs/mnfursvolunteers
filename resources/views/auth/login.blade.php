<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('Log in') }} - {{ app_name() }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ app_favicon() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php $advertiseRegistration = app_setting('advertise_registration_on_login', true); @endphp
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen grid grid-cols-1 {{ $advertiseRegistration ? 'lg:grid-cols-2' : '' }}">
            <!-- Left: Login form -->
            <div class="flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-16 xl:px-24">
                <div class="w-full max-w-sm mx-auto">
                    <a href="/">
                        <img src="{{ app_logo() }}" alt="{{ app_name() }}" class="w-16 h-auto">
                    </a>

                    <h1 class="mt-8 text-2xl font-bold tracking-tight text-gray-900">{{ __('Sign in to your account') }}</h1>

                    <!-- Session Status -->
                    <x-auth-session-status class="mt-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-8">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password')" />

                            <x-text-input id="password" class="block mt-1 w-full"
                                            type="password"
                                            name="password"
                                            required autocomplete="current-password" />

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <div class="flex gap-3">
                              <div class="flex h-6 shrink-0 items-center">
                                <div class="group grid size-4 grid-cols-1">
                                  <input id="remember_me" name="rememeber" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto">
                                  <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                    <path class="opacity-0 group-has-[:checked]:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path class="opacity-0 group-has-[:indeterminate]:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                  </svg>
                                </div>
                              </div>
                              <label for="remember-me" class="block text-sm/6 text-gray-900">Remember me</label>
                            </div>

                            <div class="text-sm/6">
                            @if (Route::has('password.request'))
                                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-primary-button class="w-full justify-center">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @feature('wordpress_integration')
                    <div class="relative mt-8">
                      <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-200"></div>
                      </div>
                      <div class="relative flex justify-center text-sm/6 font-medium">
                        <span class="bg-white px-6 text-gray-900">Or continue with</span>
                      </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4">
                      <a href="{{route('wordpress.login')}}" class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">
                        <img src="{{ app_logo() }}" alt="{{ app_name() }}" class="w-6 h-auto">
                        <span class="text-sm/6 font-semibold">{{ app_name() }} Account</span>
                      </a>
                    </div>
                    @endfeature

                    @if($advertiseRegistration)
                    <!-- Register link (visible on small screens where the right panel is hidden) -->
                    <p class="mt-8 text-center text-sm text-gray-600 lg:hidden">
                        {{ __("Don't have an account?") }}
                        <a href="{{ route('register') }}" class="font-semibold text-brand-green hover:text-green-700">{{ __('Create one') }}</a>
                    </p>
                    @endif
                </div>
            </div>

            @if($advertiseRegistration)
            @php $primaryColor = app_setting('primary_color', '#10b981'); @endphp
            <!-- Right: Register advertisement -->
            <div class="hidden lg:flex lg:flex-col lg:justify-center px-16 xl:px-24" style="background-color: {{ $primaryColor }};">
                <div class="max-w-md">
                    <h2 class="text-3xl font-bold tracking-tight text-white">{{ __('New to :app?', ['app' => app_name()]) }}</h2>
                    <p class="mt-4 text-base leading-7 text-white/80">
                        {{ __('Create an account to sign up for volunteer shifts, track your hours, and stay in the loop with upcoming events.') }}
                    </p>

                    <ul class="mt-8 space-y-4">
                        <li class="flex items-start gap-3">
                            <x-heroicon-o-check-circle class="w-6 h-6 shrink-0 text-white/80" />
                            <span class="text-sm/6 text-white">{{ __('Browse and sign up for open shifts') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <x-heroicon-o-check-circle class="w-6 h-6 shrink-0 text-white/80" />
                            <span class="text-sm/6 text-white">{{ __('Track your volunteer hours in one place') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <x-heroicon-o-check-circle class="w-6 h-6 shrink-0 text-white/80" />
                            <span class="text-sm/6 text-white">{{ __('Get notified about new opportunities') }}</span>
                        </li>
                    </ul>

                    <div class="mt-10">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-x-2 rounded-md bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" style="color: {{ $primaryColor }};">
                            {{ __('Create an account') }}
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </body>
</html>
