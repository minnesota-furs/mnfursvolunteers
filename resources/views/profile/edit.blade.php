<x-app-layout>
    <x-slot name="header">
        {{ __('Your Profile') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
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

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.wordpress-link-form')
                </div>
            </div>

            @if(!$user->wordpress_user_id)
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
