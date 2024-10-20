<x-guest-layout>
    @if (Route::has('login'))
    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
        @auth
            <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

            {{-- @if (Route::has('register'))
                <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
            @endif --}}
        @endauth
    </div>
    @endif

    <div class="flex flex-col items-center justify-center py-4 sm:px-6 lg:px-8 pb-12">
        <div class="max-w-md w-full space-y-8">
            <div>
                @auth
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-200">
                    Welcome Back!
                </h2>
                @else
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-200">
                    Hello!
                </h2>
                @endauth
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 focus:outline focus:outline-2 focus:rounded-sm">Go to the dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 focus:outline focus:outline-2 focus:rounded-sm">Login with your account</a>
                    @endauth
                </p>
            </div>
        </div>
</x-guest-layout>