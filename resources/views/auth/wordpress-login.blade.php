<x-guest-layout>
<div class="container">
    <h1 class="text-xl font-bold mb-4">Login with MNFurs.org Account</h1>

    @if(session('success'))
        <p class="text-green-500">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <p class="text-red-500">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('wordpress.login') }}">
        @csrf
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Username')" />
            <x-text-input id="email" class="block mt-1 w-full" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" />
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
            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="https://www.mnfurs.org/MNFurs_MNFurs_Let_Me_Come_IN/?action=lostpassword">
                    {{ __('Forgot your password?') }}
                </a>
    
                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        {{-- <div class="mb-4">
            <label>Email (WordPress Username)</label>
            <input type="text" name="email" required class="w-full border rounded p-2">
        </div>

        <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" required class="w-full border rounded p-2">
        </div> --}}

        {{-- <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
            Login with WordPress
        </button> --}}
    </form>
</div>
</x-guest-layout>