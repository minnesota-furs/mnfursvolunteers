<x-app-layout>
    @auth
        @section('title', 'Event Check-In Scanner')
        <x-slot name="header">
            {{ __('Event Check-In Scanner') }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('simple-volunteer-events.index') }}"
                class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
                <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Events
            </a>
        </x-slot>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="eventScanner()" x-init="init()">
            <!-- Standby Scanner -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-qr-code class="w-5 h-5 mr-2 text-brand-green"/>
                    Scanner
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Point a volunteer's QR code at the camera. It's always on standby &mdash; scan the next code as soon as you're ready.
                </p>

                <div class="mt-4 relative">
                    <video x-ref="video" class="w-full rounded-md bg-black aspect-square object-cover"></video>
                    <div x-show="justScanned" x-cloak x-transition.opacity
                        class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-md">
                        <x-heroicon-o-check-circle class="w-16 h-16 text-brand-green"/>
                    </div>
                </div>

                <p x-show="cameraError" x-cloak x-text="cameraError" class="mt-2 text-sm text-red-600"></p>

                <!-- Manual fallback if the camera can't be used -->
                <form class="mt-4 flex gap-2" @submit.prevent="lookup(manualCode); manualCode = ''">
                    <label for="manual-code" class="sr-only">Volunteer Code</label>
                    <input id="manual-code" x-model="manualCode" type="text" maxlength="20" placeholder="Enter Volcode manually"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm uppercase">
                    <x-secondary-button type="submit">Look Up</x-secondary-button>
                </form>
            </div>

            <!-- Quick Volunteer View -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <template x-if="loading">
                    <div class="flex items-center justify-center h-full min-h-[200px] text-gray-400 dark:text-gray-500">
                        <x-heroicon-o-arrow-path class="w-6 h-6 animate-spin mr-2"/> Looking up volunteer&hellip;
                    </div>
                </template>

                <template x-if="!loading && !volunteer && !lookupError">
                    <div class="flex flex-col items-center justify-center h-full min-h-[200px] text-center text-gray-400 dark:text-gray-500">
                        <x-heroicon-o-user-circle class="w-14 h-14 mb-2"/>
                        <p class="text-sm">Scan result will appear here.</p>
                    </div>
                </template>

                <template x-if="!loading && lookupError">
                    <div class="flex flex-col items-center justify-center h-full min-h-[200px] text-center">
                        <x-heroicon-o-x-circle class="w-14 h-14 mb-2 text-red-400"/>
                        <p class="text-sm text-red-600 dark:text-red-400" x-text="lookupError"></p>
                    </div>
                </template>

                <template x-if="!loading && volunteer">
                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="volunteer.name"></h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-show="volunteer.pronouns" x-text="volunteer.pronouns"></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-show="volunteer.department" x-text="volunteer.department"></p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-semibold bg-brand-green/10 text-brand-green tracking-widest"
                                x-text="volunteer.vol_code"></span>
                        </div>

                        <a :href="volunteer.profile_url" class="mt-2 inline-flex items-center text-sm text-brand-green hover:underline">
                            View full profile <x-heroicon-m-arrow-right class="w-3.5 h-3.5 ml-1"/>
                        </a>

                        <h3 class="mt-6 text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            RSVP'd Simple Events
                        </h3>

                        <template x-if="events.length === 0">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No current RSVPs for a Simple Volunteer Event.</p>
                        </template>

                        <ul class="mt-2 space-y-2">
                            <template x-for="event in events" :key="event.id">
                                <li class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2.5">
                                    <div class="min-w-0">
                                        <a :href="event.show_url" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-brand-green truncate block" x-text="event.name"></a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="event.start_time_human"></p>
                                    </div>
                                    <div class="shrink-0">
                                        <span x-show="event.checked_in"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400">
                                            <x-heroicon-m-check class="w-3.5 h-3.5 mr-1"/> Checked In
                                        </span>
                                        <button x-show="!event.checked_in" @click="checkIn(event)" :disabled="checkingInEventId === event.id"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-semibold bg-brand-green text-white hover:bg-emerald-600 disabled:opacity-50">
                                            <span x-show="checkingInEventId !== event.id">Check In</span>
                                            <span x-show="checkingInEventId === event.id">Checking In&hellip;</span>
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <p x-show="actionMessage" x-cloak x-text="actionMessage" class="mt-4 text-sm" :class="actionOk ? 'text-brand-green' : 'text-red-600'"></p>
                    </div>
                </template>
            </div>
        </div>
    @endauth

    @push('scripts')
    <script>
        function eventScanner() {
            return {
                scanner: null,
                cameraError: null,
                justScanned: false,
                manualCode: '',
                loading: false,
                lookupError: null,
                volunteer: null,
                events: [],
                checkingInEventId: null,
                actionMessage: null,
                actionOk: true,

                init() {
                    if (typeof QrScanner === 'undefined') {
                        this.cameraError = 'QR scanner failed to load.';
                        return;
                    }

                    this.scanner = new QrScanner(this.$refs.video, (result) => this.onDecode(result), {
                        highlightScanRegion: true,
                        highlightCodeOutline: true,
                    });

                    this.scanner.start().catch((err) => {
                        this.cameraError = 'Could not access camera: ' + err.message + ' You can still look volunteers up manually below.';
                    });
                },

                onDecode(result) {
                    const code = (result.data || '').trim();
                    if (!code || this.loading) return;

                    this.justScanned = true;
                    setTimeout(() => { this.justScanned = false; }, 600);

                    this.lookup(code);
                },

                lookup(code) {
                    code = (code || '').trim();
                    if (!code) return;

                    this.loading = true;
                    this.lookupError = null;
                    this.actionMessage = null;

                    fetch(`{{ route('simple-volunteer-events.scanner.lookup') }}?code=${encodeURIComponent(code)}`, {
                        headers: { 'Accept': 'application/json' },
                    })
                        .then((response) => response.json().then((data) => ({ status: response.status, data })))
                        .then(({ status, data }) => {
                            this.loading = false;

                            if (status !== 200 || !data.found) {
                                this.volunteer = null;
                                this.events = [];
                                this.lookupError = data.message || 'Volunteer not found.';
                                return;
                            }

                            this.volunteer = data.user;
                            this.events = data.events;
                        })
                        .catch(() => {
                            this.loading = false;
                            this.lookupError = 'Something went wrong looking up that code.';
                        });
                },

                checkIn(event) {
                    this.checkingInEventId = event.id;
                    this.actionMessage = null;

                    fetch(event.check_in_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ user_id: this.volunteer.id }),
                    })
                        .then((response) => response.json().then((data) => ({ status: response.status, data })))
                        .then(({ data }) => {
                            this.checkingInEventId = null;
                            this.actionOk = !!data.ok;
                            this.actionMessage = data.message;

                            if (data.ok) {
                                event.checked_in = true;
                            }
                        })
                        .catch(() => {
                            this.checkingInEventId = null;
                            this.actionOk = false;
                            this.actionMessage = 'Something went wrong checking that volunteer in.';
                        });
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
