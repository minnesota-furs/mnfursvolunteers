<x-app-layout>
    @section('title', 'Event - ' . $event->name)
    <x-slot name="header">
        {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('volunteer.events.index') }}"
            class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors">
            <x-heroicon-m-arrow-left class="w-4 h-4"/>
            Back
        </a>
        @if($event->faq)
            <a href="{{ route('volunteer.events.faq', $event) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-white/10 px-3 py-2 text-sm font-medium text-white hover:bg-white/20 transition-colors">
                <x-heroicon-m-question-mark-circle class="w-4 h-4"/>
                FAQ
            </a>
        @endif
        <a href="{{ route('volunteer.events.my-shifts', $event) }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-green shadow-sm hover:bg-gray-100 transition-colors">
            <x-heroicon-m-list-bullet class="w-4 h-4"/>
            My Assignments
        </a>
    </x-slot>

    @php
        $userTagIds    = auth()->user()->tags()->pluck('tags.id')->toArray();
        $requiredTagIds = $event->requiredTags->pluck('id')->toArray();
        $hasAllTags    = empty(array_diff($requiredTagIds, $userTagIds));

        $userDeptIds        = auth()->user()->departments()->pluck('departments.id')->toArray();
        $requiredDeptIds    = $event->requiredDepartments->pluck('id')->toArray();
        $hasRequiredDepartment = $event->requiredDepartments->isEmpty()
            || !empty(array_intersect($requiredDeptIds, $userDeptIds));

        $canSignUp = $hasAllTags && $hasRequiredDepartment;

        // Group shifts by calendar date
        $shiftsByDay = $shifts->groupBy(fn($s) => $s->start_time->format('Y-m-d'));

        // Serialised data for client-side filtering
        $filterShifts = $shifts->map(fn($s) => [
            'id'    => $s->id,
            'full'  => $s->users->count() >= $s->max_volunteers,
            'hours' => round($s->durationInHours(), 2),
            'day'   => $s->start_time->format('Y-m-d'),
        ])->values();

        $eventDays = $shiftsByDay->keys()->map(fn($d) => [
            'value' => $d,
            'label' => \Carbon\Carbon::parse($d)->format('l, M j'),
        ])->values();
    @endphp

    <div class="space-y-6">

        {{-- ── Event meta card ─────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400 mb-3">
                <span class="flex items-center gap-1.5">
                    <x-heroicon-m-calendar class="w-4 h-4 text-gray-400"/>
                    @if($event->isMultiDay())
                        {{ $event->start_date->format('M j') }} – {{ $event->end_date->format('M j, Y') }}
                    @else
                        {{ $event->start_date->format('l, F j, Y') }}
                    @endif
                </span>
                <span class="flex items-center gap-1.5">
                    <x-heroicon-m-clock class="w-4 h-4 text-gray-400"/>
                    {{ $event->start_date->format('g:i A') }} – {{ $event->end_date->format('g:i A') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <x-heroicon-m-user-group class="w-4 h-4 text-gray-400"/>
                    {{ $shifts->count() }} {{ Str::plural('assignment', $shifts->count()) }}
                </span>
                @if($event->perks->isNotEmpty())
                    <span class="flex items-center gap-1.5">
                        <x-heroicon-m-gift class="w-4 h-4 text-gray-400"/>
                        Earns Perks
                    </span>
                @endif
            </div>
            @if($event->description)
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">{!! \Parsedown::instance()->text($event->description) !!}</div>
            @endif
        </div>

        {{-- ── Restriction / requirement banners ───────────────────────── --}}
        @if($event->requiredDepartments->isNotEmpty())
            <div class="flex items-start gap-3 rounded-xl border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20 p-4">
                <x-heroicon-s-exclamation-triangle class="w-5 h-5 flex-shrink-0 mt-0.5 text-yellow-600 dark:text-yellow-400"/>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">Department Restriction</p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-2">Signups are limited to members of:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($event->requiredDepartments as $dept)
                            <span class="inline-flex items-center rounded-md bg-yellow-100 dark:bg-yellow-900/40 px-2.5 py-1 text-xs font-medium text-yellow-800 dark:text-yellow-200 ring-1 ring-inset ring-yellow-300 dark:ring-yellow-700">
                                {{ $dept->name }}
                            </span>
                        @endforeach
                    </div>
                    @if(!$hasRequiredDepartment)
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-2 font-medium">
                            You are not assigned to any of the required departments and cannot sign up for shifts.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        @if($event->requiredTags->isNotEmpty())
            <div class="flex items-start gap-3 rounded-xl border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20 p-4">
                <x-heroicon-s-exclamation-triangle class="w-5 h-5 flex-shrink-0 mt-0.5 text-yellow-600 dark:text-yellow-400"/>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">Tag Requirement</p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-2">Volunteers must have all of the following tags:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($event->requiredTags as $tag)
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                style="background-color:{{ $tag->color }}22; color:{{ $tag->color }}; border-color:{{ $tag->color }}44;">
                                @if($tag->color)
                                    <span class="inline-block w-2 h-2 rounded-full mr-1.5" style="background-color:{{ $tag->color }}"></span>
                                @endif
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                    @if(!$hasAllTags)
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-2 font-medium">
                            You do not have all required tags and cannot sign up for shifts.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── No-show warning for this event ──────────────────────────── --}}
        @php
            $eventNoShows = $userShifts
                ->filter(fn($s) => $s->pivot->no_show)
                ->each(fn($s) => $s->setRelation('event', $event));
        @endphp
        <x-no-show-warning :recentNoShows="$eventNoShows" :timeframe="null" />

        {{-- ── Your signed-up shifts ────────────────────────────────────── --}}
        @if($userShifts->isNotEmpty())
            <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-s-check-circle class="w-5 h-5 text-blue-600 dark:text-blue-400"/>
                    <h2 class="font-semibold text-blue-900 dark:text-blue-100">Your Assignments</h2>
                </div>
                <p class="text-sm text-blue-700 dark:text-blue-300 mb-4">
                    Thanks for signing up!
                    @if($event->auto_credit_hours)
                        Your hours will be credited automatically after the event.
                    @else
                        Your hours will be credited after the event.
                    @endif
                </p>
                <div class="space-y-2">
                    @foreach($userShifts as $s)
                        <div class="flex items-center justify-between gap-4 rounded-lg px-4 py-3 shadow-sm border
                            {{ $s->pivot->no_show
                                ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700'
                                : 'bg-white dark:bg-gray-800 border-blue-200 dark:border-blue-700' }}"
                        >
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $s->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    @if($event->isMultiDay())
                                        {{ $s->start_time->format('l, g:i A') }} – {{ $s->end_time->format('g:i A') }}
                                    @else
                                        {{ $s->start_time->format('g:i A') }} – {{ $s->end_time->format('g:i A') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                @if($s->pivot->no_show)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-red-100 dark:bg-red-900/40 px-2.5 py-1 text-xs font-medium text-red-700 dark:text-red-300">
                                        <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5"/>
                                        No show
                                    </span>
                                @elseif($s->start_time->isPast())
                                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 dark:bg-gray-700 px-2.5 py-1 text-xs text-gray-500 dark:text-gray-400">
                                        <x-heroicon-m-clock class="w-3.5 h-3.5"/>
                                        Past
                                    </span>
                                @else
                                    <form action="{{ route('shifts.cancel', $s) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-md bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:hover:bg-red-900/60 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 transition-colors"
                                            onclick="return confirm('Cancel your signup for {{ addslashes($s->name) }}?')">
                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5"/>
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Openings, grouped by day ─────────────────────────────────── --}}
        <div x-data="{
            hideFull: true,
            maxHours: 0,
            filterDay: '',
            shifts: {{ $filterShifts->toJson() }},
            shiftVisible(id) {
                const s = this.shifts.find(x => x.id === id);
                if (!s) return true;
                if (this.hideFull && s.full) return false;
                if (this.maxHours > 0 && s.hours > this.maxHours) return false;
                if (this.filterDay && s.day !== this.filterDay) return false;
                return true;
            },
            dayVisible(day) {
                return this.shifts.some(s => s.day === day && this.shiftVisible(s.id));
            },
            dayShiftCount(day) {
                return this.shifts.filter(s => s.day === day && this.shiftVisible(s.id)).length;
            },
            get visibleCount() {
                return this.shifts.filter(s => this.shiftVisible(s.id)).length;
            },
            get activeFilters() {
                return (!this.hideFull ? 1 : 0) + (this.maxHours > 0 ? 1 : 0) + (this.filterDay ? 1 : 0);
            },
            clearFilters() {
                this.hideFull = true;
                this.maxHours = 0;
                this.filterDay = '';
            }
        }">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Available Assignments</h2>
            </div>

            @if($shifts->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-12 text-center">
                    <x-heroicon-o-calendar-days class="w-10 h-10 text-gray-400 mb-3"/>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No openings are currently available.</p>
                </div>
            @else
                {{-- ── Filter bar ──────────────────────────────────────────── --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm px-4 py-3 mb-5">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">

                        {{-- Show/hide full toggle --}}
                        <button type="button"
                            x-on:click="hideFull = !hideFull"
                            :class="hideFull
                                ? 'bg-brand-green text-white border-transparent'
                                : 'text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-all">
                            <x-heroicon-m-eye-slash class="w-3.5 h-3.5" x-show="!hideFull"/>
                            <x-heroicon-m-eye class="w-3.5 h-3.5" x-show="hideFull"/>
                            <span x-text="hideFull ? 'Hide Full' : 'Show Full'"></span>
                        </button>

                        <div class="hidden sm:block w-px h-4 bg-gray-200 dark:bg-gray-700"></div>

                        {{-- Max length pill group --}}
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Max length:</span>
                            @php $durationOptions = [0 => 'Any', 1 => '≤ 1h', 2 => '≤ 2h', 3 => '≤ 3h', 4 => '≤ 4h']; @endphp
                            @foreach($durationOptions as $val => $label)
                                <button type="button"
                                    x-on:click="maxHours = {{ $val }}"
                                    :class="maxHours === {{ $val }}
                                        ? 'bg-brand-green text-white border-transparent'
                                        : 'text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                                    class="rounded-full border px-2.5 py-1 text-xs font-medium transition-all">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Day filter — only shown for multi-day events --}}
                        @if($shiftsByDay->count() > 1)
                            <div class="hidden sm:block w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Day:</span>
                                <select x-model="filterDay"
                                    class="rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-300 px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent cursor-pointer">
                                    <option value="">All days</option>
                                    @foreach($eventDays as $day)
                                        <option value="{{ $day['value'] }}">{{ $day['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Count + clear --}}
                        <div class="ml-auto flex items-center gap-3">
                            <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                                <span x-text="visibleCount"></span> / {{ $shifts->count() }} shown
                            </span>
                            <button type="button"
                                x-show="activeFilters > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                x-on:click="clearFilters()"
                                class="text-xs font-medium text-brand-green hover:text-green-700 hover:underline">
                                Clear all
                            </button>
                        </div>
                    </div>
                </div>

                {{-- No-results state --}}
                <div x-show="visibleCount === 0"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-10 text-center mb-4">
                    <x-heroicon-o-funnel class="w-8 h-8 text-gray-400 mb-2"/>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No assignments match your filters.</p>
                    <button x-on:click="clearFilters()" class="mt-2 text-xs text-brand-green hover:underline font-medium">Clear filters</button>
                </div>

                {{-- Shift groups by day --}}
                <div class="space-y-8">
                    @foreach($shiftsByDay as $dateKey => $dayShifts)
                        <div x-show="dayVisible('{{ $dateKey }}')"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0">

                            {{-- Day heading --}}
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex-shrink-0 flex flex-col items-center justify-center w-12 h-12 rounded-lg bg-brand-green/10 dark:bg-brand-green/20 text-brand-green">
                                    <span class="text-xs font-semibold uppercase leading-none">{{ \Carbon\Carbon::parse($dateKey)->format('M') }}</span>
                                    <span class="text-xl font-bold leading-tight">{{ \Carbon\Carbon::parse($dateKey)->format('j') }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($dateKey)->format('l, F j') }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"
                                        x-text="dayShiftCount('{{ $dateKey }}') + (dayShiftCount('{{ $dateKey }}') === 1 ? ' assignment' : ' assignments')"></p>
                                </div>
                            </div>

                            {{-- Shifts for this day --}}
                            <div class="space-y-3 pl-1 border-l-2 border-gray-200 dark:border-gray-700 ml-6">
                                @foreach($dayShifts as $shift)
                                    @php
                                        $isFull      = $shift->users->count() >= $shift->max_volunteers;
                                        $signedUp    = $shift->users->contains(auth()->id());
                                        $isPast      = $shift->start_time->isPast();
                                        $hasConflict = isset($shiftConflicts[$shift->id]);
                                        $conflictingShift = $hasConflict ? $shiftConflicts[$shift->id]->first() : null;
                                        $openSpots   = $shift->max_volunteers - $shift->users->count();
                                        $shiftUserIds = $shift->users->pluck('id')->all();
                                        $hasFavorite = !empty(array_intersect($shiftUserIds, $favoritedIds ?? []));
                                        $hasAvoided  = !empty(array_intersect($shiftUserIds, $avoidedIds ?? []));
                                    @endphp

                                    <div class="ml-4 rounded-xl border shadow-sm transition-shadow hover:shadow-md
                                        {{ $signedUp ? 'border-blue-300 dark:border-blue-700 bg-blue-50/60 dark:bg-blue-900/10'
                                                     : ($isPast || $isFull ? 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 opacity-70'
                                                                           : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800') }}"
                                        x-show="shiftVisible({{ $shift->id }})"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0">

                                        <div class="flex items-start gap-4 p-4">

                                            {{-- Time column --}}
                                            <div class="flex-shrink-0 w-20 text-center pt-0.5">
                                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $shift->start_time->format('g:i') }}<span class="text-xs font-normal ml-0.5">{{ $shift->start_time->format('A') }}</span></p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $shift->end_time->format('g:i A') }}</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ round($shift->durationInHours(), 1) }}h</p>
                                            </div>

                                            {{-- Details column --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 {{ $isPast || $isFull ? 'text-gray-400 dark:text-gray-500' : '' }}">
                                                        <a href="{{ route('volunteer.shifts.show', [$event, $shift]) }}" class="hover:underline">{{ $shift->name }}</a>
                                                    </h4>
                                                    @if($signedUp)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 dark:bg-blue-900 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-300">
                                                            <x-heroicon-m-check class="w-3 h-3"/>
                                                            Signed Up
                                                        </span>
                                                    @endif
                                                    @if($shift->double_hours)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:text-yellow-400">
                                                            <x-heroicon-m-star class="w-3 h-3"/>
                                                            2× Hours
                                                        </span>
                                                    @endif
                                                    @feature('volunteer_relationships')
                                                    @if($hasFavorite)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 dark:bg-yellow-900/20 px-2 py-0.5 text-xs font-medium text-yellow-600 dark:text-yellow-400" title="A favorited volunteer is signed up">
                                                            <x-heroicon-s-star class="w-3 h-3"/>
                                                            Favorite here
                                                        </span>
                                                    @endif
                                                    @if($hasAvoided)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 dark:bg-orange-900/20 px-2 py-0.5 text-xs font-medium text-orange-600 dark:text-orange-400" title="An avoided volunteer is signed up">
                                                            <x-heroicon-s-hand-raised class="w-3 h-3"/>
                                                            Avoid here
                                                        </span>
                                                    @endif
                                                    @endfeature
                                                </div>

                                                @if($shift->description)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 leading-snug">{{ $shift->description }}</p>
                                                @endif

                                                {{-- Capacity bar --}}
                                                @php $fillPct = $shift->max_volunteers > 0 ? min(100, round($shift->users->count() / $shift->max_volunteers * 100)) : 0; @endphp
                                                <div class="flex items-center gap-2 mt-1">
                                                    <div class="flex-1 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden max-w-[120px]">
                                                        <div class="h-full rounded-full transition-all
                                                            {{ $isFull ? 'bg-red-400 dark:bg-red-500' : 'bg-brand-green' }}"
                                                            style="width: {{ $fillPct }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $shift->users->pluck('name')->join(', ') ?: 'No one signed up yet' }}">
                                                        {{ $shift->users->count() }}/{{ $shift->max_volunteers }}
                                                        @if($isFull) · Full @elseif($openSpots === 1) · 1 spot left @else · {{ $openSpots }} spots left @endif
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- Action column --}}
                                            <div class="flex-shrink-0 flex items-start pt-0.5">
                                                @if($isPast)
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400">
                                                        <x-heroicon-m-clock class="w-3.5 h-3.5"/>
                                                        Past
                                                    </span>
                                                @elseif($signedUp)
                                                    <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-md bg-red-600 hover:bg-red-700 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition-colors"
                                                            onclick="return confirm('Cancel signup for {{ addslashes($shift->name) }}?')">
                                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5"/>
                                                            Cancel
                                                        </button>
                                                    </form>
                                                @elseif($isFull)
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400">
                                                        <x-heroicon-m-x-circle class="w-3.5 h-3.5"/>
                                                        Full
                                                    </span>
                                                @elseif($hasConflict)
                                                    <div class="text-right">
                                                        <span class="inline-flex items-center gap-1 rounded-md bg-gray-200 dark:bg-gray-700 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                            <x-heroicon-m-exclamation-circle class="w-3.5 h-3.5"/>
                                                            Conflict
                                                        </span>
                                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-[140px] text-right leading-snug">
                                                            {{ $conflictingShift->name }}
                                                        </p>
                                                    </div>
                                                @elseif(!$event->signup_open_date || $event->signup_open_date->isPast())
                                                    @if($canSignUp)
                                                        <form action="{{ route('shifts.signup', $shift) }}" method="POST">
                                                            @csrf
                                                            <button type="submit"
                                                                class="inline-flex items-center gap-1 rounded-md bg-brand-green hover:bg-green-700 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition-colors">
                                                                <x-heroicon-m-plus class="w-3.5 h-3.5"/>
                                                                Sign Up
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <div class="text-right">
                                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Opens</p>
                                                        <p class="text-xs text-gray-400 dark:text-gray-500" title="{{ $event->signup_open_date->format('F j, Y g:i A') }}">
                                                            {{ $event->signup_open_date->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
