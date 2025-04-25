<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Link Telegram') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            You can manage certain functions, report volunteer hours and get notifications via telegram by linking your account.
            You can also unlink your account from here too, or by sending the <span class="font-mono wrap-break-word text-purple-700">/unlink</span> command to the bot.
        </p>
    </header>

    @if(Auth::user()->telegram_id)
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Your volunteer account is currently linked to telegram account: <span class="font-mono wrap-break-word text-blue-700">{{Auth::user()->telegram_id}}</span>
        </p>
        <a href=""
        class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-400 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Unlink Telegram Account
        </a>
    @else
        @php
            $token = Auth::user()->generateTelegramLinkToken();
        @endphp
            <a href="https://t.me/MNFursVolBot?start={{ $token }}"
            class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-green/80 active:bg-brand-green focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Link Telegram Account
            </a>
    @endif

    {{-- <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button> --}}
</section>
