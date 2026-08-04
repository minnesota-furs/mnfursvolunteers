<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('Register') }} - {{ app_name() }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ app_favicon() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $onboardingAgreement = app_setting('onboarding_agreement');
        $inviteCodeRequired = app_setting('require_invite_code', false);
        $warningEmailDomains = \App\Support\WarningEmailDomains::parse(app_setting('warning_email_domains', ''));
    @endphp
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-6 py-12 bg-gray-100 dark:bg-gray-900">
            <a href="/">
                <img src="{{ app_logo() }}" alt="{{ app_name() }}" class="w-16 h-auto">
            </a>

            <div class="w-full max-w-2xl mt-6 bg-white dark:bg-gray-800 shadow-md rounded-lg px-6 py-8 sm:px-10">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 text-center">{{ __('Create your account') }}</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
                    {{ __('Join :app to sign up for shifts and track your volunteer hours.', ['app' => app_name()]) }}
                </p>

                @if($onboardingAgreement)
                <div class="mt-6">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Please review the following agreement before registering:') }}</p>
                    <div class="h-48 overflow-y-auto rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm text-gray-700 dark:text-gray-300 prose prose-sm dark:prose-invert max-w-none">{!! \Parsedown::instance()->text($onboardingAgreement) !!}</div>
                </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-8"
                    x-data="{
                        email: @js(old('email', '')),
                        warningDomains: @js($warningEmailDomains),
                        shouldWarn() {
                            const domain = this.email.toLowerCase().trim().split('@').pop();
                            return this.email.includes('@') && this.warningDomains.includes(domain);
                        }
                    }">
                    @csrf

                    <!-- Account -->
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Account') }}</h2>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="name" value="Name (Alias)" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" x-model="email" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div x-show="shouldWarn()" x-cloak class="mt-4 rounded-md border border-yellow-300 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900/20">
                        <h3 class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">{{ __('Are you sure?') }}</h3>
                        <p class="mt-1 text-sm text-yellow-800 dark:text-yellow-200">
                            {{ __('You should use a personal email address to create your account. This helps ensure you retain access to your volunteer records if you ever lose access to this organization domain. If you know what you are doing, you may continue, though you may be asked to change your email address.') }}
                        </p>
                        <label class="mt-3 flex items-start gap-2 text-sm text-yellow-900 dark:text-yellow-100">
                            <input type="checkbox" name="warning_email_acknowledged" value="1" :required="shouldWarn()"
                                {{ old('warning_email_acknowledged') ? 'checked' : '' }}
                                class="mt-0.5 rounded border-yellow-400 text-brand-green focus:ring-brand-green">
                            <span>{{ __('I understand and want to use this email address.') }}</span>
                        </label>
                        <x-input-error :messages="$errors->get('warning_email_acknowledged')" class="mt-2" />
                    </div>

                    <!-- Personal Info -->
                    <h2 class="mt-6 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Personal Info') }}</h2>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="first_name" value="Legal First Name" />
                            <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autocomplete="given-name" />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="last_name" value="Legal Last Name" />
                            <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Security -->
                    <h2 class="mt-6 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Security') }}</h2>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <!-- Invite Code -->
                    <div class="mt-6">
                        <x-input-label for="invite_code" :value="$inviteCodeRequired ? __('Invite Code') : __('Invite Code (optional)')" />
                        <x-text-input id="invite_code" class="block mt-1 w-full max-w-xs font-mono uppercase" type="text"
                                      name="invite_code" :value="old('invite_code', request('code'))"
                                      :required="$inviteCodeRequired"
                                      placeholder="{{ $inviteCodeRequired ? '' : 'Optional' }}" autocomplete="off" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $inviteCodeRequired ? __('An invite code is required to register.') : __('If you received an invite code, enter it here.') }}
                        </p>
                        <x-input-error :messages="$errors->get('invite_code')" class="mt-2" />
                    </div>

                    @if($onboardingAgreement)
                    <div class="mt-6">
                        <div class="flex gap-3">
                            <div class="flex h-6 shrink-0 items-center">
                                <div class="group grid size-4 grid-cols-1">
                                    <input id="agree_to_terms" name="agree_to_terms" type="checkbox" value="1" required
                                        {{ old('agree_to_terms') ? 'checked' : '' }}
                                        class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-brand-green checked:bg-brand-green indeterminate:border-brand-green indeterminate:bg-brand-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto">
                                    <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                        <path class="opacity-0 group-has-[:checked]:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                            <label for="agree_to_terms" class="text-sm text-gray-700 dark:text-gray-300">
                                {{ __('I have read and agree to the agreement above.') }}
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('agree_to_terms')" class="mt-2" />
                    </div>
                    @endif

                    <div class="mt-8">
                        <x-primary-button class="w-full justify-center">
                            {{ __('Create Account') }}
                        </x-primary-button>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="font-semibold text-brand-green hover:text-green-700">{{ __('Log in') }}</a>
                </p>
            </div>
        </div>
    </body>
</html>
