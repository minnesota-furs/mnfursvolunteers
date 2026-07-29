<section x-data="telegramLink({
        linked: {{ $user->hasTelegramLinked() ? 'true' : 'false' }},
        username: @js($user->telegram_username),
        justGenerated: {{ session('status') === 'telegram-link-generated' ? 'true' : 'false' }},
        deepLink: @js($user->getTelegramLinkUrl()),
        statusUrl: '{{ route('profile.telegram-status') }}',
    })" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Telegram') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Link your Telegram account to receive your notifications there too.
        </p>
    </header>

    <template x-if="linked">
        <div class="mt-4 flex items-center justify-between gap-4 rounded-md border border-gray-200 dark:border-gray-700 px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 shrink-0" />
                <span>
                    Linked<template x-if="username"><span> as <span class="font-medium" x-text="'@' + username"></span></span></template>.
                </span>
            </div>
            <form method="POST" action="{{ route('profile.unlink-telegram') }}" onsubmit="return confirm('Unlink Telegram? You will stop receiving notifications there.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                    Unlink
                </button>
            </form>
        </div>
    </template>

    <template x-if="!linked">
        <div class="mt-4">
            <template x-if="!deepLink">
                <form method="POST" action="{{ route('profile.link-telegram') }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        Link Telegram
                    </button>
                </form>
            </template>

            <template x-if="deepLink">
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Scan the QR code with your phone, or open the link below, then press <strong>Start</strong> in Telegram. This code expires in 15 minutes.
                    </p>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <canvas x-ref="qrCanvas" class="rounded border border-gray-200 dark:border-gray-700"></canvas>
                        <div class="space-y-2">
                            <a :href="deepLink" target="_blank" class="inline-flex items-center gap-1.5 rounded-md bg-blue-500 px-3 py-2 text-sm font-medium text-white hover:bg-blue-600">
                                Open in Telegram
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-show="polling">
                                Waiting for you to confirm in Telegram&hellip;
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.link-telegram') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Generate a new code
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </template>
</section>

<script>
    function telegramLink({ linked, username, justGenerated, deepLink, statusUrl }) {
        return {
            linked,
            username,
            deepLink: justGenerated ? deepLink : null,
            polling: false,
            pollTimer: null,

            init() {
                if (this.deepLink) {
                    this.$nextTick(() => this.renderQr());
                    this.startPolling();
                }
            },

            renderQr() {
                if (this.$refs.qrCanvas) {
                    QRCode.toCanvas(this.$refs.qrCanvas, this.deepLink, { width: 220, margin: 2 });
                }
            },

            startPolling() {
                this.polling = true;
                this.pollTimer = setInterval(async () => {
                    const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    const data = await response.json();
                    if (data.linked) {
                        this.linked = true;
                        this.username = data.username;
                        this.deepLink = null;
                        this.polling = false;
                        clearInterval(this.pollTimer);
                    }
                }, 3000);
            },
        };
    }
</script>
