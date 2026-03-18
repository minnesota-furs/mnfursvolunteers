<x-app-layout>
    @section('title', 'Volunteer Perks')
    <x-slot name="header">
        {{ __('Volunteer Perks') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('volunteer.perks.history') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            View History
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if($sets->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                    <x-heroicon-o-gift class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400">No perks have been set up yet. Check back later!</p>
                </div>
            @else
                <div class="flex items-start gap-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg px-4 py-3 mb-6">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 mt-0.5 shrink-0 text-yellow-600 dark:text-yellow-400" />
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">
                        <span class="font-semibold">Counts are not final.</span> Hours include all shifts you are currently signed up for. No-shows and cancellations may influence your final totals.
                    </p>
                </div>

                <div class="space-y-8">
                    @foreach($sets as $item)
                        @php
                            /** @var \App\Models\VolunteerPerkSet $set */
                            $set = $item['set'];
                        @endphp

                        <div>
                            {{-- Set header --}}
                            <div class="flex items-center gap-3 mb-3 flex-wrap">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $set->name }}</h2>
                                @if($set->fiscalLedger)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $set->fiscalLedger->name }}</span>
                                @endif
                                @if($set->visible_until)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">• Ends {{ $set->visible_until->format('M j, Y') }}</span>
                                @endif
                            </div>
                            @if($set->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $set->description }}</p>
                            @endif

                            {{-- Perks --}}
                            <div class="space-y-4">
                                @foreach($item['perks'] as $perkItem)
                                    @php
                                        /** @var \App\Models\VolunteerPerk $perk */
                                        $perk            = $perkItem['perk'];
                                        $progress        = $perkItem['progress'];
                                        $percentage      = $perkItem['percentage'];
                                        $earned          = $perkItem['earned'];
                                        $onTrack         = $perkItem['on_track'];
                                        $breakdown       = $perkItem['breakdown'];
                                        $redemption      = $perkItem['redemption'];
                                        $minHours        = (float) $perk->min_hours;
                                        $completedPct    = $minHours > 0 ? min(100, $breakdown['completed'] / $minHours * 100) : 100;
                                        $upcomingPct     = $minHours > 0 ? min(100 - $completedPct, $breakdown['upcoming'] / $minHours * 100) : 0;
                                        $redeemedAtIso   = $redemption?->redeemed_at?->toIso8601String();
                                    @endphp

                                    <div x-data="perkCard(@js($redeemedAtIso), @js($perk->id))"
                                         x-init="init()"
                                         class="overflow-hidden shadow-sm sm:rounded-lg p-5
                                        @if($earned) bg-gradient-to-br from-green-100 to-green-50 dark:from-green-900/50 dark:to-gray-800 ring-2 ring-green-400 dark:ring-green-600
                                        @elseif($onTrack) bg-gradient-to-br from-yellow-100 to-yellow-50 dark:from-yellow-900/40 dark:to-gray-800 ring-2 ring-yellow-400 dark:ring-yellow-600
                                        @elseif($perk->is_mystery && !$earned) bg-gradient-to-br from-purple-50 to-gray-50 dark:from-purple-950/30 dark:to-gray-800
                                        @else bg-white dark:bg-gray-800
                                        @endif">

                                        @if($perk->is_mystery && !$earned)
                                            {{-- Mystery perk: hidden until earned --}}
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/40">
                                                    <x-heroicon-s-question-mark-circle class="w-6 h-6 text-purple-500 dark:text-purple-400" />
                                                </div>
                                                <div>
                                                    <p class="text-base font-semibold text-purple-900 dark:text-purple-200">Mystery Perk</p>
                                                    <p class="text-sm text-purple-700 dark:text-purple-400">Keep volunteering to unlock and reveal this perk!</p>
                                                </div>
                                            </div>
                                        @else
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $perk->name }}
                                                    </h3>
                                                    @if($earned)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Earned
                                                        </span>
                                                    @elseif($onTrack)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                                                            <x-heroicon-s-arrow-trending-up class="w-3.5 h-3.5" /> On Track
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($perk->description)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $perk->description }}</p>
                                                @endif
                                                @if($onTrack)
                                                    <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1.5">You're on track to earn this reward! Keep it up and make sure to complete all of your signed-up shifts!</p>
                                                @endif
                                            </div>

                                            <div class="text-right shrink-0">
                                                <p class="text-2xl font-bold {{ $earned ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-gray-100' }}">
                                                    {{ number_format($progress, 1) }}
                                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">/ {{ number_format((float)$perk->min_hours, 1) }} hrs</span>
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Progress bar --}}
                                        <div class="mt-4">
                                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                <span>Progress</span>
                                                <span>{{ number_format($percentage, 0) }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden flex">
                                                <div class="h-3 bg-green-500 transition-all duration-500"
                                                    style="width: {{ $completedPct }}%">
                                                </div>
                                                <div class="h-3 bg-yellow-400 dark:bg-yellow-500 transition-all duration-500"
                                                    style="width: {{ $upcomingPct }}%">
                                                </div>
                                            </div>
                                            {{-- Legend --}}
                                            <div class="flex items-center gap-4 mt-1.5">
                                                @if($breakdown['completed'] > 0)
                                                    <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <span class="inline-block w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                                                        {{ number_format($breakdown['completed'], 1) }} completed
                                                    </span>
                                                @endif
                                                @if($breakdown['upcoming'] > 0)
                                                    <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-400 dark:bg-yellow-500 shrink-0"></span>
                                                        {{ number_format($breakdown['upcoming'], 1) }} upcoming
                                                    </span>
                                                @endif
                                                @if(!$earned)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-auto">
                                                        {{ number_format(max(0, $minHours - $progress), 1) }} more hr(s) needed
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Pass & Redeem actions --}}
                                        @if($earned && ($perk->has_pass || $perk->has_physical_reward))
                                            <div class="mt-4 pt-4 border-t border-black/10 dark:border-white/10 flex flex-wrap items-center gap-3">

                                                {{-- Show Pass button --}}
                                                @if($perk->has_pass)
                                                    <button
                                                        @click="openPass()"
                                                        class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-green-600">
                                                        <x-heroicon-s-identification class="w-4 h-4" />
                                                        Show Pass
                                                    </button>

                                                    {{-- Pass Modal (scoped inside this perk card) --}}
                                                    <template x-teleport="body">
                                                        <div x-show="showPass" style="display:none">
                                                            {{-- Overlay --}}
                                                            <div x-show="showPass"
                                                                 x-transition:enter="ease-out duration-300"
                                                                 x-transition:enter-start="opacity-0"
                                                                 x-transition:enter-end="opacity-100"
                                                                 x-transition:leave="ease-in duration-200"
                                                                 x-transition:leave-start="opacity-100"
                                                                 x-transition:leave-end="opacity-0"
                                                                 class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm"
                                                                 @click="closePass()"></div>
                                                            {{-- Panel --}}
                                                            <div x-show="showPass"
                                                                 x-transition:enter="ease-out duration-300"
                                                                 x-transition:enter-start="opacity-0 scale-95"
                                                                 x-transition:enter-end="opacity-100 scale-100"
                                                                 x-transition:leave="ease-in duration-200"
                                                                 x-transition:leave-start="opacity-100 scale-100"
                                                                 x-transition:leave-end="opacity-0 scale-95"
                                                                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                                                 @click.self="closePass()">
                                                                <div class="w-full max-w-sm overflow-hidden rounded-2xl shadow-2xl">
                                                                    {{-- Top stripe --}}
                                                                    <div class="bg-green-600 px-6 py-4 text-center">
                                                                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-green-200">Volunteer Access Pass</p>
                                                                        <p class="mt-1 text-lg font-bold text-white">{{ $perk->pass_label ?? $perk->name }}</p>
                                                                    </div>
                                                                    {{-- Body --}}
                                                                    <div class="bg-white px-8 py-6 text-center dark:bg-gray-900">
                                                                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40">
                                                                            <x-heroicon-s-identification class="h-9 w-9 text-green-600 dark:text-green-400" />
                                                                        </div>
                                                                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                                                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $perk->name }}</p>
                                                                        <div class="mt-5 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden relative">
                                                                            {{-- Animated gradient background --}}
                                                                            <div class="absolute inset-0 pass-gradient-anim"></div>
                                                                            {{-- Content on top --}}
                                                                            <div class="relative py-3">
                                                                                <p class="text-xs uppercase tracking-widest text-white/80 drop-shadow">Current Time</p>
                                                                                <p class="mt-0.5 font-mono text-lg font-semibold text-white drop-shadow" x-text="clock"></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    {{-- Footer --}}
                                                                    <div class="bg-gray-50 px-6 py-4 dark:bg-gray-800">
                                                                        <button @click="closePass()"
                                                                            class="w-full rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600">
                                                                            Close
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                @endif

                                                {{-- Redeem physical reward --}}
                                                @if($perk->has_physical_reward)
                                                    <div x-show="!isRedeemed" class="w-full">
                                                        <div class="flex items-start gap-2 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 px-3 py-2 mb-2">
                                                            <x-heroicon-s-exclamation-triangle class="w-4 h-4 mt-0.5 shrink-0 text-amber-600 dark:text-amber-400" />
                                                            <p class="text-xs text-amber-800 dark:text-amber-300">Only press <span class="font-semibold">Redeem</span> when you are ready to collect your reward or when asked to by a staff member. This action cannot be undone.</p>
                                                        </div>
                                                        {{-- Not yet redeemed --}}
                                                        <button
                                                            @click="redeem()"
                                                            :disabled="loading"
                                                            class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-600">
                                                            <svg x-show="loading" class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                            </svg>
                                                            <x-heroicon-s-gift class="w-4 h-4" x-show="!loading" />
                                                            <span x-text="loading ? 'Redeeming...' : 'Redeem{{ $perk->reward_label ? ': ' . addslashes($perk->reward_label) : '' }}'"></span>
                                                        </button>
                                                    </div>

                                                    {{-- Active redemption window (within 10 min) --}}
                                                    <div x-show="isRedeemed && inWindow"
                                                         class="flex items-center gap-2 rounded-md bg-green-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm">
                                                        <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
                                                        <span>Redeemed</span>
                                                        <span class="font-mono text-green-200 mt-0.5" x-text="'(' + countdown + ')'"></span>
                                                    </div>

                                                    {{-- After window --}}
                                                    <div x-show="isRedeemed && !inWindow"
                                                         class="inline-flex items-center gap-1.5 rounded-md bg-gray-200 dark:bg-gray-700 px-3 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400">
                                                        <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
                                                        Already Redeemed
                                                    </div>
                                                @endif

                                            </div>
                                        @endif
                                        @endif{{-- /is_mystery --}}

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <style>
        @keyframes passGradientShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .pass-gradient-anim {
            background: linear-gradient(270deg, #16a34a, #059669, #0d9488, #0891b2, #2563eb, #7c3aed, #db2777, #dc2626, #ea580c, #16a34a);
            background-size: 600% 600%;
            animation: passGradientShift 4s ease infinite;
        }
    </style>
    <script>
        function perkCard(redeemedAtIso, perkId) {
            return {
                redeemedAt: redeemedAtIso ? new Date(redeemedAtIso).getTime() : null,
                countdown: '',
                loading: false,
                _timer: null,
                showPass: false,
                clock: '',
                _clockTimer: null,
                get isRedeemed() { return this.redeemedAt !== null; },
                get inWindow() {
                    return this.redeemedAt !== null && (Date.now() - this.redeemedAt) < 10 * 60 * 1000;
                },
                openPass() {
                    this.showPass = true;
                    this.tickClock();
                    this._clockTimer = setInterval(() => this.tickClock(), 1000);
                },
                closePass() {
                    this.showPass = false;
                    clearInterval(this._clockTimer);
                },
                tickClock() {
                    this.clock = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },
                startTimer() {
                    this.updateCountdown();
                    this._timer = setInterval(() => this.updateCountdown(), 1000);
                },
                updateCountdown() {
                    const remaining = 10 * 60 * 1000 - (Date.now() - this.redeemedAt);
                    if (remaining <= 0) {
                        clearInterval(this._timer);
                        this.countdown = '';
                        return;
                    }
                    const m = Math.floor(remaining / 60000);
                    const s = Math.floor((remaining % 60000) / 1000);
                    this.countdown = m + ':' + String(s).padStart(2, '0');
                },
                async redeem() {
                    if (this.loading) return;
                    this.loading = true;
                    try {
                        const resp = await fetch(`/volunteer/perks/${perkId}/redeem`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                        });
                        const data = await resp.json();
                        if (resp.ok) {
                            this.redeemedAt = new Date(data.redeemed_at).getTime();
                            this.startTimer();
                        } else {
                            alert(data.error ?? 'Could not redeem perk. Please try again.');
                        }
                    } catch (e) {
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                },
                init() {
                    if (this.redeemedAt && this.inWindow) {
                        this.startTimer();
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>


