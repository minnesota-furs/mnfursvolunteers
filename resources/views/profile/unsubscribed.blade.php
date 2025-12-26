<x-app-layout>
    <x-slot name="header">
        {{ __('Unsubscribed') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/20">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                        You've been unsubscribed
                    </h3>
                    
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        You will no longer receive {{ $preferenceType }} emails.
                    </p>
                    
                    @auth
                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            You can manage all your email preferences from your 
                            <a href="{{ route('profile.edit') }}" class="text-brand-green hover:text-green-600 font-medium">
                                profile settings
                            </a>.
                        </p>
                    @else
                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            If you'd like to re-subscribe or manage other email preferences, please 
                            <a href="{{ route('login') }}" class="text-brand-green hover:text-green-600 font-medium">
                                log in to your account
                            </a> and visit your profile settings.
                        </p>
                    @endauth
                    
                    <div class="mt-6">
                        <a href="{{ url('/') }}" 
                           class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
