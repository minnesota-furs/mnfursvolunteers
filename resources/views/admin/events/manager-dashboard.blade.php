<x-app-layout>
    @section('title', 'Manager Dashboard — ' . $event->name)
    <x-slot name="header">
        <span class="flex items-center gap-2">
            <x-heroicon-s-signal class="w-5 h-5 text-green-400 animate-pulse"/>
            Live Coverage: {{ $event->name }}
        </span>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.manager-dashboard') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            Global View
        </a>
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-s-clock class="w-4 inline"/> Manage Shifts
        </a>
        <span class="text-xs text-white/60 self-center">
            Updated {{ $now->format('g:i A') }}
        </span>
        <button onclick="window.location.reload()"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-s-arrow-path class="w-4 inline"/> Refresh
        </button>
    </x-slot>

    @include('admin.partials.manager-styles')

    {{-- ── Event info banner ── --}}
    <div class="mb-5 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-0">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-semibold mb-0.5">Event</div>
            <div class="text-base font-bold text-gray-800 dark:text-gray-100 truncate">{{ $event->name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $event->start_date->format('l, F j') }}
                @if($event->start_date->format('Y-m-d') !== $event->end_date->format('Y-m-d'))
                    – {{ $event->end_date->format('l, F j') }}
                @endif
                @if($event->location)
                    &nbsp;·&nbsp; {{ $event->location }}
                @endif
            </div>
        </div>
        <div class="flex gap-3 text-center shrink-0">
            <div>
                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $activeShifts->count() }}</div>
                <div class="text-xs text-gray-400">Active</div>
            </div>
            <div class="w-px bg-gray-200 dark:bg-gray-700"></div>
            <div>
                <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $upcomingShifts->count() }}</div>
                <div class="text-xs text-gray-400">Upcoming</div>
            </div>
            <div class="w-px bg-gray-200 dark:bg-gray-700"></div>
            <div>
                <div class="text-lg font-bold
                    @if($coveragePct >= 80) text-green-600 dark:text-green-400
                    @elseif($coveragePct >= 50) text-yellow-600 dark:text-yellow-400
                    @else text-red-600 dark:text-red-400
                    @endif">{{ $coveragePct }}%</div>
                <div class="text-xs text-gray-400">Coverage</div>
            </div>
            <div class="w-px bg-gray-200 dark:bg-gray-700"></div>
            <div>
                <div class="text-lg font-bold {{ $emptyShifts > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    {{ $emptyShifts }}
                </div>
                <div class="text-xs text-gray-400">Unstaffed</div>
            </div>
            <div class="w-px bg-gray-200 dark:bg-gray-700"></div>
            <div>
                <div class="text-lg font-bold text-gray-700 dark:text-gray-300">{{ $filledSlots }}/{{ $totalSlots }}</div>
                <div class="text-xs text-gray-400">Slots</div>
            </div>
        </div>
        {{-- overall coverage bar --}}
        <div class="w-full mt-1">
            <div class="cov-bar">
                <div class="cov-fill
                    @if($coveragePct >= 80) bg-green-500
                    @elseif($coveragePct >= 50) bg-yellow-400
                    @else bg-red-500
                    @endif"
                    style="width: {{ $coveragePct }}%">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Auto-refresh bar ── --}}
    @include('admin.partials.manager-auto-refresh')

    {{-- ── Three-column live board (event name hidden since we're already scoped) ── --}}
    @php $showEventName = false; @endphp
    @include('admin.partials.manager-board')

    {{-- ── No near-term shifts fallback ── --}}
    @if($activeShifts->isEmpty() && $upcomingShifts->isEmpty() && $recentShifts->isEmpty())
        <div class="mt-4 text-center py-10 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <x-heroicon-o-clock class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"/>
            <p class="font-semibold text-gray-600 dark:text-gray-400">No shifts in the active window</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Showing ±2–3 hrs from now. Scroll down for the full schedule.</p>
        </div>
    @endif

    {{-- ── Later shifts full schedule ── --}}
    @if($laterShifts->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
            <x-heroicon-s-calendar class="w-5 h-5 text-gray-400"/>
            Later Shifts
            <span class="text-xs font-normal text-gray-400">(beyond the next 3 hours)</span>
        </h2>

        @php
            $laterByDate = $laterShifts->groupBy(fn($s) => $s->start_time->format('Y-m-d'));
        @endphp

        @foreach($laterByDate as $date => $dayShifts)
            <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-5 mb-2">
                {{ \Carbon\Carbon::parse($date)->format('l, F j') }}
            </h3>
            <div class="space-y-2">
                @foreach($dayShifts as $shift)
                    @php
                        $count  = $shift->users->count();
                        $max    = $shift->max_volunteers;
                        $pct    = $max > 0 ? round(($count / $max) * 100) : 0;
                        $isFull = $count >= $max;
                        $isEmpty = $count === 0;
                    @endphp
                    <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                        {{-- Time --}}
                        <div class="text-xs text-gray-400 dark:text-gray-500 w-28 shrink-0 text-right">
                            {{ $shift->start_time->format('g:i') }}–{{ $shift->end_time->format('g:i A') }}
                        </div>

                        {{-- Name --}}
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}"
                               class="text-sm font-semibold text-gray-800 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                {{ $shift->name }}
                            </a>
                        </div>

                        {{-- Coverage badge --}}
                        <div class="shrink-0 flex items-center gap-2">
                            <span class="text-xs font-bold
                                @if($isEmpty) text-red-500 dark:text-red-400
                                @elseif($isFull) text-green-600 dark:text-green-400
                                @else text-yellow-600 dark:text-yellow-400
                                @endif">
                                {{ $count }}/{{ $max }}
                            </span>
                            <div class="cov-bar w-16">
                                <div class="cov-fill
                                    @if($pct >= 100) bg-green-500
                                    @elseif($pct > 0) bg-yellow-400
                                    @else bg-red-400
                                    @endif"
                                    style="width: {{ min($pct, 100) }}%">
                                </div>
                            </div>
                        </div>

                        {{-- Volunteers --}}
                        <div class="hidden sm:flex flex-wrap gap-1 max-w-xs">
                            @forelse($shift->users as $user)
                                <span class="vol-pill bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    {{ $user->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">No volunteers</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    @endif

</x-app-layout>
