<x-app-layout>
    @section('title', 'Manager Dashboard')
    <x-slot name="header">
        <span class="flex items-center gap-2">
            <x-heroicon-s-signal class="w-5 h-5 text-green-400 animate-pulse"/>
            Manager Dashboard — Live Coverage
        </span>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            All Events
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

    {{-- ── Top stats bar ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $activeShifts->count() }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Active Shifts</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $upcomingShifts->count() }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upcoming (3 hrs)</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-2xl font-bold
                @if($coveragePct >= 80) text-green-600 dark:text-green-400
                @elseif($coveragePct >= 50) text-yellow-600 dark:text-yellow-400
                @else text-red-600 dark:text-red-400
                @endif">
                {{ $coveragePct }}%
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Overall Coverage</div>
            <div class="cov-bar mt-2">
                <div class="cov-fill
                    @if($coveragePct >= 80) bg-green-500
                    @elseif($coveragePct >= 50) bg-yellow-400
                    @else bg-red-500
                    @endif"
                    style="width: {{ $coveragePct }}%">
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="text-2xl font-bold {{ $emptyShifts > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                {{ $emptyShifts }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unstaffed Shifts</div>
        </div>
    </div>

    {{-- ── Active events summary chips ── --}}
    @if($activeEvents->isNotEmpty())
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach($activeEvents as $event)
            <a href="{{ route('admin.events.manager-dashboard', $event) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-green/10 text-brand-green border border-brand-green/30 hover:bg-brand-green/20 transition">
                <x-heroicon-s-signal class="w-3.5 h-3.5"/>
                {{ $event->name }}
                <span class="ml-1 bg-brand-green/20 rounded-full px-1.5">
                    {{ $event->shifts->count() }} shifts
                </span>
            </a>
        @endforeach
    </div>
    @endif

    {{-- ── Auto-refresh bar ── --}}
    @include('admin.partials.manager-auto-refresh')

    {{-- ── Three-column board ── --}}
    @php $showEventName = true; @endphp
    @include('admin.partials.manager-board')

    {{-- ── No-events fallback ── --}}
    @if($activeShifts->isEmpty() && $upcomingShifts->isEmpty() && $recentShifts->isEmpty())
        <div class="mt-10 text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <x-heroicon-o-signal-slash class="w-14 h-14 mx-auto text-gray-300 dark:text-gray-600 mb-4"/>
            <p class="text-lg font-semibold text-gray-600 dark:text-gray-400">No active event coverage right now</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">This dashboard shows shifts from the past 2 hours through the next 3 hours.</p>
            <a href="{{ route('admin.events.index') }}"
               class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-green text-white text-sm font-semibold hover:bg-brand-green/90">
                <x-heroicon-m-calendar class="w-4 h-4"/> Browse Events
            </a>
        </div>
    @endif

</x-app-layout>

