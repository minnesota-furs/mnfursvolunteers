{{--
    Manager Board — three-column live coverage grid.

    Expects variables in scope:
        $activeShifts   — Collection of shifts currently happening
        $upcomingShifts — Collection of shifts starting within 3 hours
        $recentShifts   — Collection of shifts that ended within 2 hours (sorted desc by end_time)
        $showEventName  — (bool, optional, default true) show the event name subtitle on each card
                          Set to false on the event-level dashboard where the event is already in context.
--}}
@php $showEventName = $showEventName ?? true; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── RECENT ── --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="section-badge bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600">
                <x-heroicon-s-check-circle class="w-4 h-4"/>
                Recently Ended
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500">(last 2 hrs)</span>
        </div>

        @forelse($recentShifts as $shift)
            @php
                $count   = $shift->users->count();
                $max     = $shift->max_volunteers;
                $pct     = $max > 0 ? round(($count / $max) * 100) : 0;
                $noShows = $shift->users->filter(fn($u) => $u->pivot->no_show)->count();
            @endphp
            <div class="shift-card mb-3 rounded-xl border bg-white dark:bg-gray-800 p-4 shadow-sm border-gray-200 dark:border-gray-700 opacity-75">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $shift->name }}</span>
                        @if($showEventName)
                            <div class="text-xs text-gray-400 mt-0.5">
                                <x-heroicon-m-calendar class="w-3 h-3 inline"/> {{ $shift->event->name }}
                            </div>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                        {{ $shift->start_time->format('g:i') }}–{{ $shift->end_time->format('g:i A') }}
                    </span>
                </div>

                {{-- Coverage bar --}}
                <div class="flex items-center gap-2 mb-3">
                    <div class="cov-bar flex-1">
                        <div class="cov-fill
                            @if($pct >= 100) bg-green-500
                            @elseif($pct > 0) bg-yellow-400
                            @else bg-red-500
                            @endif"
                            style="width: {{ min($pct, 100) }}%">
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $count }}/{{ $max }}</span>
                </div>

                {{-- Volunteers --}}
                <div class="flex flex-wrap gap-1">
                    @forelse($shift->users as $user)
                        <span class="vol-pill {{ $user->pivot->no_show ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 line-through' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                            @if($user->pivot->hours_logged_at)
                                <x-heroicon-m-check-badge class="w-3 h-3 text-green-500"/>
                            @endif
                            {{ $user->name }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-400 italic">No volunteers</span>
                    @endforelse
                </div>

                @if($noShows > 0)
                    <div class="mt-2 text-xs text-red-500 flex items-center gap-1">
                        <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5"/>
                        {{ $noShows }} no-show{{ $noShows > 1 ? 's' : '' }}
                    </div>
                @endif

                <div class="mt-3 flex gap-2">
                    <a href="{{ route('admin.events.shifts.edit', [$shift->event, $shift]) }}"
                       class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <x-heroicon-m-pencil class="w-3 h-3"/> Manage
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm italic">
                No shifts ended recently
            </div>
        @endforelse
    </div>

    {{-- ── ACTIVE NOW ── --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="section-badge bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 border border-green-300 dark:border-green-700">
                <span class="w-2 h-2 rounded-full bg-green-500 ring-pulse inline-block"></span>
                Active Now
            </span>
        </div>

        @forelse($activeShifts as $shift)
            @php
                $count       = $shift->users->count();
                $max         = $shift->max_volunteers;
                $pct         = $max > 0 ? round(($count / $max) * 100) : 0;
                $isEmpty     = $count === 0;
                $isFull      = $count >= $max;
                $minutesLeft = (int) $shift->end_time->diffInMinutes(now());
                $endingSoon  = $minutesLeft <= 15;
            @endphp
            <div class="shift-card mb-3 rounded-xl border p-4 shadow-sm
                @if($isEmpty) bg-red-50 dark:bg-red-950/30 border-red-300 dark:border-red-700
                @elseif($isFull) bg-green-50 dark:bg-green-950/30 border-green-300 dark:border-green-700
                @else bg-yellow-50 dark:bg-yellow-950/20 border-yellow-300 dark:border-yellow-700
                @endif">

                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-bold
                            @if($isEmpty) text-red-800 dark:text-red-300
                            @elseif($isFull) text-green-800 dark:text-green-300
                            @else text-yellow-800 dark:text-yellow-300
                            @endif">
                            {{ $shift->name }}
                        </span>
                        @if($showEventName)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                <x-heroicon-m-calendar class="w-3 h-3 inline"/> {{ $shift->event->name }}
                            </div>
                        @endif
                    </div>
                    <div class="ml-2 text-right shrink-0">
                        <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $shift->start_time->format('g:i') }}–{{ $shift->end_time->format('g:i A') }}
                        </div>
                        @if($endingSoon)
                            <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 flex items-center justify-end gap-0.5 mt-0.5">
                                <x-heroicon-m-clock class="w-3 h-3"/> Ends in {{ $minutesLeft }}m
                            </span>
                        @else
                            <span class="text-xs text-gray-400 whitespace-nowrap">
                                Ends {{ $shift->end_time->diffForHumans(['parts' => 1]) }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Coverage bar --}}
                <div class="flex items-center gap-2 mb-3">
                    <div class="cov-bar flex-1">
                        <div class="cov-fill
                            @if($pct >= 100) bg-green-500
                            @elseif($pct > 0) bg-yellow-400
                            @else bg-red-500
                            @endif"
                            style="width: {{ min($pct, 100) }}%">
                        </div>
                    </div>
                    <span class="text-xs font-bold
                        @if($isEmpty) text-red-600 dark:text-red-400
                        @elseif($isFull) text-green-700 dark:text-green-400
                        @else text-yellow-700 dark:text-yellow-400
                        @endif whitespace-nowrap">
                        {{ $count }}/{{ $max }}
                    </span>
                </div>

                {{-- Volunteers --}}
                <div class="flex flex-wrap gap-1 mb-3">
                    @forelse($shift->users as $user)
                        <span class="vol-pill
                            {{ $user->pivot->no_show
                                ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 line-through'
                                : ($isFull
                                    ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300'
                                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600') }}">
                            @if($user->pivot->hours_logged_at)
                                <x-heroicon-m-check-badge class="w-3 h-3 text-green-500"/>
                            @endif
                            {{ $user->name }}
                        </span>
                    @empty
                        <span class="vol-pill bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400">
                            <x-heroicon-m-exclamation-triangle class="w-3 h-3"/> No volunteers!
                        </span>
                    @endforelse

                    @if(!$isFull && $count > 0)
                        @for($i = 0; $i < ($max - $count); $i++)
                            <span class="vol-pill bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-dashed border-gray-300 dark:border-gray-600">
                                Open slot
                            </span>
                        @endfor
                    @endif
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.events.shifts.edit', [$shift->event, $shift]) }}"
                       class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <x-heroicon-m-pencil class="w-3.5 h-3.5"/> Manage
                    </a>
                    @if($showEventName)
                        <a href="{{ route('admin.events.allShifts', $shift->event) }}"
                           class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:underline flex items-center gap-1">
                            <x-heroicon-m-queue-list class="w-3.5 h-3.5"/> Event Overview
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                <x-heroicon-o-clock class="w-10 h-10 mx-auto mb-2 opacity-50"/>
                <p class="text-sm italic">No shifts happening right now</p>
            </div>
        @endforelse
    </div>

    {{-- ── UPCOMING ── --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <span class="section-badge bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-300 dark:border-indigo-700">
                <x-heroicon-s-arrow-right-circle class="w-4 h-4"/>
                Coming Up
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500">(next 3 hrs)</span>
        </div>

        @forelse($upcomingShifts as $shift)
            @php
                $count     = $shift->users->count();
                $max       = $shift->max_volunteers;
                $pct       = $max > 0 ? round(($count / $max) * 100) : 0;
                $isFull    = $count >= $max;
                $isEmpty   = $count === 0;
                $startsIn  = $shift->start_time->diffInMinutes(now());
                $veryClose = $startsIn <= 30;
            @endphp
            <div class="shift-card mb-3 rounded-xl border p-4 shadow-sm bg-white dark:bg-gray-800
                @if($isEmpty && $veryClose) border-red-300 dark:border-red-700
                @elseif($isEmpty) border-orange-200 dark:border-orange-800
                @elseif($isFull) border-green-200 dark:border-green-800
                @else border-gray-200 dark:border-gray-700
                @endif">

                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1 min-w-0">
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $shift->name }}</span>
                        @if($showEventName)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                <x-heroicon-m-calendar class="w-3 h-3 inline"/> {{ $shift->event->name }}
                            </div>
                        @endif
                    </div>
                    <div class="ml-2 text-right shrink-0">
                        <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $shift->start_time->format('g:i') }}–{{ $shift->end_time->format('g:i A') }}
                        </div>
                        @if($veryClose)
                            <span class="text-xs font-semibold text-orange-600 dark:text-orange-400 flex items-center justify-end gap-0.5 mt-0.5">
                                <x-heroicon-m-bell class="w-3 h-3"/> In {{ $startsIn }}m
                            </span>
                        @else
                            <span class="text-xs text-indigo-500 dark:text-indigo-400 whitespace-nowrap">
                                {{ $shift->start_time->diffForHumans(['parts' => 1]) }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Coverage bar --}}
                <div class="flex items-center gap-2 mb-3">
                    <div class="cov-bar flex-1">
                        <div class="cov-fill
                            @if($pct >= 100) bg-green-500
                            @elseif($pct > 0) bg-yellow-400
                            @else bg-red-400
                            @endif"
                            style="width: {{ min($pct, 100) }}%">
                        </div>
                    </div>
                    <span class="text-xs font-semibold
                        @if($isEmpty) text-red-500 dark:text-red-400
                        @elseif($isFull) text-green-600 dark:text-green-400
                        @else text-yellow-600 dark:text-yellow-400
                        @endif whitespace-nowrap">
                        {{ $count }}/{{ $max }}
                    </span>
                </div>

                {{-- Volunteers --}}
                <div class="flex flex-wrap gap-1 mb-3">
                    @forelse($shift->users as $user)
                        <span class="vol-pill bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700">
                            {{ $user->name }}
                        </span>
                    @empty
                        @if($veryClose)
                            <span class="vol-pill bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400">
                                <x-heroicon-m-exclamation-triangle class="w-3 h-3"/> No volunteers assigned!
                            </span>
                        @else
                            <span class="text-xs text-gray-400 italic">No volunteers yet</span>
                        @endif
                    @endforelse

                    @if(!$isFull && $count > 0)
                        @for($i = 0; $i < ($max - $count); $i++)
                            <span class="vol-pill bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-dashed border-gray-300 dark:border-gray-600">
                                Open slot
                            </span>
                        @endfor
                    @endif
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.events.shifts.edit', [$shift->event, $shift]) }}"
                       class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <x-heroicon-m-pencil class="w-3.5 h-3.5"/> Manage
                    </a>
                    @if($showEventName)
                        <a href="{{ route('admin.events.allShifts', $shift->event) }}"
                           class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:underline flex items-center gap-1">
                            <x-heroicon-m-queue-list class="w-3.5 h-3.5"/> Event Overview
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                <x-heroicon-o-calendar class="w-10 h-10 mx-auto mb-2 opacity-50"/>
                <p class="text-sm italic">No upcoming shifts in the next 3 hours</p>
            </div>
        @endforelse
    </div>

</div>{{-- end three-column --}}
