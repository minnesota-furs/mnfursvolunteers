<x-app-layout>
    @section('title', $shift->name . ' — ' . $event->name)
    <x-slot name="header">
        {{ $shift->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('volunteer.events.show', $event) }}"
            class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors">
            <x-heroicon-m-arrow-left class="w-4 h-4"/>
            Back to {{ $event->name }}
        </a>
    </x-slot>

    <div class="space-y-5">

        {{-- ── Assignment summary card ──────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">

                {{-- Left: time + meta --}}
                <div class="space-y-3">
                    {{-- Date / time --}}
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <x-heroicon-m-calendar class="w-4 h-4 flex-shrink-0 text-gray-400"/>
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $shift->start_time->format('l, F j, Y') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <x-heroicon-m-clock class="w-4 h-4 flex-shrink-0 text-gray-400"/>
                        <span>{{ $shift->start_time->format('g:i A') }} – {{ $shift->end_time->format('g:i A') }}</span>
                        <span class="text-gray-400 dark:text-gray-500">·</span>
                        <span>{{ round($shift->durationInHours(), 1) }} {{ Str::plural('hour', $shift->durationInHours()) }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <x-heroicon-m-calendar-days class="w-4 h-4 flex-shrink-0 text-gray-400"/>
                        <a href="{{ route('volunteer.events.show', $event) }}" class="text-brand-green hover:underline font-medium">
                            {{ $event->name }}
                        </a>
                    </div>

                    {{-- Badges --}}
                    <div class="flex flex-wrap gap-2 pt-1">
                        @if($shift->double_hours)
                            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-400">
                                <x-heroicon-m-star class="w-3.5 h-3.5"/>
                                Double Hours
                            </span>
                        @endif
                        @if($isPast)
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                                <x-heroicon-m-clock class="w-3.5 h-3.5"/>
                                Past
                            </span>
                        @elseif($signedUp)
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-900 px-2.5 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">
                                <x-heroicon-m-check class="w-3.5 h-3.5"/>
                                You're Signed Up
                            </span>
                        @elseif($isFull)
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-900/20 px-2.5 py-1 text-xs font-medium text-red-600 dark:text-red-400 ring-1 ring-inset ring-red-200 dark:ring-red-800">
                                <x-heroicon-m-x-circle class="w-3.5 h-3.5"/>
                                Full
                            </span>
                        @endif

                        {{-- Shift tags --}}
                        @foreach($shift->tags as $tag)
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                style="background-color:{{ $tag->color }}22; color:{{ $tag->color }}; border-color:{{ $tag->color }}44;">
                                @if($tag->color)
                                    <span class="inline-block w-2 h-2 rounded-full mr-1.5" style="background-color:{{ $tag->color }}"></span>
                                @endif
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Right: action button --}}
                <div class="flex-shrink-0">
                    @if($isPast)
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">
                            <x-heroicon-m-clock class="w-4 h-4"/>
                            Assignment Passed
                        </span>
                    @elseif($signedUp)
                        <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 hover:bg-red-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors"
                                onclick="return confirm('Cancel your signup for {{ addslashes($shift->name) }}?')">
                                <x-heroicon-m-x-mark class="w-4 h-4"/>
                                Cancel Signup
                            </button>
                        </form>
                    @elseif($isFull)
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400">
                            <x-heroicon-m-x-circle class="w-4 h-4"/>
                            Assignment Full
                        </span>
                    @elseif($hasConflict)
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400">
                                <x-heroicon-m-exclamation-circle class="w-4 h-4"/>
                                Schedule Conflict
                            </span>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                @foreach($conflictingShifts as $cs)
                                    <p>Conflicts with: <span class="font-medium">{{ $cs->name }}</span>
                                    @if($cs->event) ({{ $cs->event->name }})@endif</p>
                                @endforeach
                            </div>
                        </div>
                    @elseif(!$event->signup_open_date || $event->signup_open_date->isPast())
                        @if($canSignUp)
                            <form action="{{ route('shifts.signup', $shift) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-green hover:bg-green-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors">
                                    <x-heroicon-m-plus class="w-4 h-4"/>
                                    Sign Up
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="text-right rounded-lg bg-gray-100 dark:bg-gray-700 px-4 py-2.5">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Signups open</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200 font-semibold"
                                title="{{ $event->signup_open_date->format('F j, Y g:i A') }}">
                                {{ $event->signup_open_date->diffForHumans() }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($shift->description)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $shift->description }}</p>
                </div>
            @endif
        </div>

        {{-- ── Capacity ─────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <x-heroicon-m-user-group class="w-4 h-4 text-gray-400"/>
                Volunteers
            </h2>

            @php
                $filled = $shift->users->count();
                $total  = $shift->max_volunteers;
                $pct    = $total > 0 ? min(100, round($filled / $total * 100)) : 0;
                $open   = max(0, $total - $filled);
            @endphp

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    <div class="h-full rounded-full {{ $isFull ? 'bg-red-400 dark:bg-red-500' : 'bg-brand-green' }} transition-all"
                        style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                    {{ $filled }} / {{ $total }}
                    @if(!$isFull)
                        <span class="text-gray-400 dark:text-gray-500 font-normal">· {{ $open }} {{ Str::plural('spot', $open) }} left</span>
                    @endif
                </span>
            </div>

            @if($shift->users->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">No one has signed up yet. Be the first!</p>
            @else
                <ul class="space-y-2">
                    @foreach($shift->users as $vol)
                        <li class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-brand-green/20 dark:bg-brand-green/30 flex items-center justify-center text-xs font-semibold text-brand-green flex-shrink-0">
                                {{ strtoupper(substr($vol->name ?? '?', 0, 1)) }}
                            </div>
                            <span class="text-sm text-gray-800 dark:text-gray-200">
                                {{ $vol->name }}
                                @if($vol->id === auth()->id())
                                    <span class="text-xs text-blue-500 dark:text-blue-400 font-medium">(you)</span>
                                @endif
                            </span>
                            @if($vol->pivot->hours_logged_at)
                                <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-0.5 text-xs text-green-700 dark:text-green-400">
                                    <x-heroicon-m-check class="w-3 h-3"/>
                                    Hours logged
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</x-app-layout>
