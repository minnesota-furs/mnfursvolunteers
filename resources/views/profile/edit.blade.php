<x-app-layout>
    <x-slot name="header">
        {{ __('Your Profile') }}
    </x-slot>

    <div class="py-12">
        @php
            $profileSections = [
                ['id' => 'profile-information', 'label' => __('Profile Information'), 'icon' => 'heroicon-o-user'],
                ['id' => 'accessibility-needs', 'label' => __('Accessibility Needs'), 'icon' => 'heroicon-o-hand-raised'],
                ['id' => 'timezone', 'label' => __('Timezone'), 'icon' => 'heroicon-o-clock'],
                ['id' => 'email-preferences', 'label' => __('Email Preferences'), 'icon' => 'heroicon-o-envelope'],
                ['id' => 'calendar', 'label' => __('Calendar'), 'icon' => 'heroicon-o-calendar-days'],
            ];

            if (app_setting('telegram_bot_username')) {
                $profileSections[] = ['id' => 'telegram', 'label' => __('Telegram'), 'icon' => 'heroicon-o-paper-airplane'];
            }

            $profileSections[] = ['id' => 'password', 'label' => __('Password'), 'icon' => 'heroicon-o-lock-closed'];

            if (! $user->wordpress_user_id) {
                $profileSections[] = ['id' => 'delete-account', 'label' => __('Delete Account'), 'icon' => 'heroicon-o-trash'];
            }
        @endphp

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-start">
                <aside class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg lg:sticky lg:top-6" aria-label="{{ __('Profile sections') }}">
                    <h2 class="px-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ __('Profile sections') }}
                    </h2>

                    <nav class="mt-3 flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible lg:pb-0">
                        @foreach ($profileSections as $section)
                            <a
                                href="#{{ $section['id'] }}"
                                class="group flex shrink-0 items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-brand-green focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-green-400 dark:focus:ring-offset-gray-800"
                            >
                                <x-dynamic-component :component="$section['icon']" class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-brand-green dark:group-hover:text-green-400" aria-hidden="true" />
                                <span>{{ $section['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <main class="space-y-6">
                    <div id="profile-information" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <div id="accessibility-needs" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.accessibility-needs-form')
                        </div>
                    </div>

                    <div id="timezone" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        @include('profile.partials.update-timezone-form')
                    </div>

                    <div id="email-preferences" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.email-preferences-form')
                        </div>
                    </div>

                    <div id="calendar" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.calendar-subscription')
                        </div>
                    </div>

                    @if(app_setting('telegram_bot_username'))
                        <div id="telegram" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <div class="max-w-xl">
                                @include('profile.partials.telegram-link')
                            </div>
                        </div>
                    @endif

                    <div id="password" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        @if(!$user->wordpress_user_id)
                            <div class="max-w-xl">
                                @include('profile.partials.update-password-form')
                            </div>
                        @else
                            <div class="max-w-xl">
                                <header>
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        Update Password on MNFurs.org
                                    </h2>

                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        You logged in with a MNFurs.org account. You must login to <a class="text-blue-500" href="https://mnfurs.org/">MNFurs.org</a> and manage your password there under your profile settings page.
                                    </p>
                                </header>
                            </div>
                        @endif
                    </div>

                    {{-- <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            @include('profile.partials.wordpress-link-form')
                        </div>
                    </div> --}}

                    @if(!$user->wordpress_user_id)
                        <div id="delete-account" class="scroll-mt-6 p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <div class="max-w-xl">
                                @include('profile.partials.delete-user-form')
                            </div>
                        </div>
                    @endif

                    <p class="px-4 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-0">
                        {{ __('Want a refresher?') }}
                        <a
                            href="{{ route('onboarding.index', ['step' => 1]) }}"
                            class="font-medium underline decoration-gray-300 underline-offset-4 transition hover:text-brand-green hover:decoration-brand-green focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2 dark:decoration-gray-600 dark:hover:text-green-400 dark:hover:decoration-green-400 dark:focus:ring-offset-gray-900"
                        >
                            {{ __('Run through the onboarding wizard again.') }}
                        </a>
                    </p>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
