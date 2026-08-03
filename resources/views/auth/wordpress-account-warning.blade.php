<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Create a new volunteer account?</h1>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                This MNFurs.org login is not connected to an existing volunteer account, so continuing will create a new account.
            </p>

            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                If you already have a volunteer record, go back and sign in with its email address instead to avoid potentially creating a second account.
            </p>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <form method="POST" action="{{ route('wordpress.cancel-account') }}">
                @csrf
                <x-secondary-button type="submit" class="w-full justify-center sm:w-auto">
                    Go back to login
                </x-secondary-button>
            </form>

            <form method="POST" action="{{ route('wordpress.create-account') }}">
                @csrf
                <x-primary-button class="w-full justify-center sm:w-auto">
                    Create New Account
                </x-primary-button>
            </form>
        </div>
    </div>
</x-guest-layout>
