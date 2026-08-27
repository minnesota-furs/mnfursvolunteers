<x-app-layout>
    @section('title', 'Event - ' . $event->name)
    <x-slot name="header">
        {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a id="tour-back-to-events-btn" href="{{ route('volunteer.events.index') }}"
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

    <style>
        .va-agenda-grid {
            display: grid;
            grid-template-columns: 70px 1fr;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }

        .dark .va-agenda-grid {
            background: #1f2937;
            border-color: #374151;
        }

        .va-time-label {
            grid-column: 1;
            padding: 8px;
            font-size: 0.7rem;
            color: #6b7280;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #f3f4f6;
            text-align: right;
            font-weight: 500;
            background: #f9fafb;
        }

        .dark .va-time-label {
            background: #111827;
            color: #9ca3af;
            border-color: #374151;
        }

        .va-day-header {
            padding: 8px 12px;
            font-weight: 600;
            font-size: 0.75rem;
            border-bottom: 2px solid #d1d5db;
            background: #f3f4f6;
            color: #1f2937;
        }

        .dark .va-day-header {
            background: #374151;
            color: #f3f4f6;
            border-color: #4b5563;
        }

        .va-time-slot {
            border-bottom: 1px solid #f3f4f6;
            position: relative;
            min-height: 70px;
        }

        .dark .va-time-slot {
            border-color: #374151;
        }

        .va-shift-block {
            position: absolute;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            z-index: 1;
        }

        .va-shift-block:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            z-index: 50 !important;
        }

        .va-shift-open {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 2px solid #10b981;
            color: #065f46;
        }

        .dark .va-shift-open {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            border-color: #10b981;
            color: #d1fae5;
        }

        .va-shift-full {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border: 2px solid #9ca3af;
            color: #4b5563;
            opacity: 0.75;
        }

        .dark .va-shift-full {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            border-color: #6b7280;
            color: #9ca3af;
            opacity: 0.75;
        }

        .va-shift-signedup {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
            color: #1e3a8a;
        }

        .dark .va-shift-signedup {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-color: #60a5fa;
            color: #dbeafe;
        }

        .va-shift-name {
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .va-shift-time {
            font-size: 0.62rem;
            opacity: 0.85;
            margin-bottom: 2px;
        }

        .va-shift-meta {
            font-size: 0.65rem;
            font-weight: 600;
        }
    </style>

    @php
        $userTagIds    = auth()->user()->tags()->pluck('tags.id')->toArray();
        $requiredTagIds = $event->requiredTags->pluck('id')->toArray();
        $hasAllTags    = empty(array_diff($requiredTagIds, $userTagIds));

        $hasRequiredDepartment = $event->userMeetsDepartmentRequirement(auth()->user());

        $canSignUp = $hasAllTags && $hasRequiredDepartment;

        // Group shifts by calendar date
        $shiftsByDay = $shifts->groupBy(fn($s) => $s->start_time->format('Y-m-d'));

        // Serialised data for client-side filtering
        $filterShifts = $shifts->map(fn($s) => [
            'id'         => $s->id,
            'full'       => $s->users->count() >= $s->max_volunteers,
            'hours'      => round($s->durationInHours(), 2),
            'day'        => $s->start_time->format('Y-m-d'),
            'categories' => $s->categories->pluck('id')->all(),
        ])->values();

        $eventDays = $shiftsByDay->keys()->map(fn($d) => [
            'value' => $d,
            'label' => \Carbon\Carbon::parse($d)->format('l, M j'),
        ])->values();

        $shiftCategories = $shifts->flatMap(fn($s) => $s->categories)->unique('id')->sortBy('name')->values();
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

        @if($accessibilityConflicts->isNotEmpty())
            <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <x-heroicon-s-exclamation-triangle class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400"/>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">Accessibility concerns identified</p>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        {{ $accessibilityConflicts->count() }}
                        {{ Str::plural('shift', $accessibilityConflicts->count()) }}
                        may conflict with accessibility needs in your profile. Review the highlighted shift details before signing up.
                    </p>
                </div>
            </div>
        @endif

        {{-- ── Restriction / requirement banners ───────────────────────── --}}
        @if($event->requiredDepartments->isNotEmpty() || $event->requiredSectors->isNotEmpty())
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
                        @foreach($event->requiredSectors as $sector)
                            <span class="inline-flex items-center rounded-md bg-yellow-100 dark:bg-yellow-900/40 px-2.5 py-1 text-xs font-medium text-yellow-800 dark:text-yellow-200 ring-1 ring-inset ring-yellow-300 dark:ring-yellow-700">
                                Any department in {{ $sector->name }}
                            </span>
                        @endforeach
                    </div>
                    @if(!$hasRequiredDepartment)
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-2 font-medium">
                            You are not assigned to any of the required departments or sectors and cannot sign up for shifts.
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
            view: 'list',
            hideFull: true,
            maxHours: 0,
            filterDay: '',
            filterCategory: '',
            shifts: {{ $filterShifts->toJson() }},
            shiftVisible(id) {
                const s = this.shifts.find(x => x.id === id);
                if (!s) return true;
                if (this.hideFull && s.full) return false;
                if (this.maxHours > 0 && s.hours > this.maxHours) return false;
                if (this.filterDay && s.day !== this.filterDay) return false;
                if (this.filterCategory && !s.categories.includes(Number(this.filterCategory))) return false;
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
                return (!this.hideFull ? 1 : 0) + (this.maxHours > 0 ? 1 : 0) + (this.filterDay ? 1 : 0) + (this.filterCategory ? 1 : 0);
            },
            clearFilters() {
                this.hideFull = true;
                this.maxHours = 0;
                this.filterDay = '';
                this.filterCategory = '';
            }
        }">
            <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Available Assignments</h2>

                @if($shifts->isNotEmpty())
                    <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden shrink-0">
                        <button type="button"
                            x-on:click="view = 'list'"
                            :class="view === 'list'
                                ? 'bg-brand-green text-white'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium transition-colors">
                            <x-heroicon-m-list-bullet class="w-3.5 h-3.5"/>
                            List
                        </button>
                        <button type="button"
                            x-on:click="view = 'agenda'"
                            :class="view === 'agenda'
                                ? 'bg-brand-green text-white'
                                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium transition-colors border-l border-gray-200 dark:border-gray-700">
                            <x-heroicon-m-calendar-days class="w-3.5 h-3.5"/>
                            Agenda
                        </button>
                    </div>
                @endif
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

                        {{-- Category filter — only shown if any shift has a category --}}
                        @if($shiftCategories->isNotEmpty())
                            <div class="hidden sm:block w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Category:</span>
                                <select x-model="filterCategory"
                                    class="rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-300 px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-green focus:border-transparent cursor-pointer">
                                    <option value="">All categories</option>
                                    @foreach($shiftCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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

                {{-- Shift groups by day (List view) --}}
                <div x-show="view === 'list'" class="space-y-8">
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
                            <div class="space-y-3 pl-1 border-l-2 border-gray-200 dark:border-gray-700 ml-2 sm:ml-6">
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
                                        $shiftAccessibilityConflicts = $accessibilityConflicts->get($shift->id, []);
                                    @endphp

                                    <div class="ml-2 sm:ml-4 rounded-xl border shadow-sm transition-shadow hover:shadow-md
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

                                        <div class="flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4 p-3 sm:p-4">

                                            {{-- Time column --}}
                                            <div class="flex items-baseline gap-x-2 gap-y-0.5 flex-wrap sm:flex-shrink-0 sm:w-20 sm:flex-col sm:items-center sm:gap-x-0 sm:text-center sm:pt-0.5">
                                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $shift->start_time->format('g:i') }}<span class="text-xs font-normal ml-0.5">{{ $shift->start_time->format('A') }}</span></p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $shift->end_time->format('g:i A') }}</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ round($shift->durationInHours(), 1) }}h</p>
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

                                                    @foreach($shift->categories as $category)
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                                            style="background-color:{{ $category->color }}22; color:{{ $category->color }}; border-color:{{ $category->color }}44;">
                                                            @if($category->color)
                                                                <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color:{{ $category->color }}"></span>
                                                            @endif
                                                            {{ $category->name }}
                                                        </span>
                                                    @endforeach
                                                </div>

                                                @if($shift->description)
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 leading-snug">{{ $shift->description }}</p>
                                                @endif

                                                @if($shiftAccessibilityConflicts)
                                                    <div class="mb-2 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                                                        <x-heroicon-s-exclamation-triangle class="mt-0.5 h-4 w-4 flex-shrink-0"/>
                                                        <p class="text-xs leading-snug">
                                                            <span class="font-semibold">May conflict with your accessibility needs:</span>
                                                            {{ implode(', ', $shiftAccessibilityConflicts) }}
                                                        </p>
                                                    </div>
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
                                            <div class="flex-shrink-0 flex items-start sm:pt-0.5">
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
                                                            <button type="submit" data-tour="tour-signup-btn" data-shift-id="{{ $shift->id }}"
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

                {{-- Agenda view --}}
                <div x-show="view === 'agenda'" class="space-y-4">
                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded-sm bg-emerald-100 dark:bg-emerald-900 border-2 border-emerald-500"></span>
                            Open slots
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded-sm bg-blue-100 dark:bg-blue-900 border-2 border-blue-500"></span>
                            You're signed up
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-block w-3 h-3 rounded-sm bg-gray-200 dark:bg-gray-700 border-2 border-gray-400 dark:border-gray-500"></span>
                            Full
                        </span>

                        @if($shiftCategories->isNotEmpty())
                            <div class="hidden sm:block w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                            @foreach($shiftCategories as $category)
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $category->color ?: '#9CA3AF' }}"></span>
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        @endif
                    </div>

                    <div class="space-y-6">
                        @foreach($shiftsByDay as $dateKey => $dayShifts)
                            <div x-show="dayVisible('{{ $dateKey }}')"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">
                                    {{ \Carbon\Carbon::parse($dateKey)->format('l, F j') }}
                                </h3>

                                <div class="va-agenda-grid">
                                    <div class="va-time-label" style="border-bottom: 2px solid #d1d5db;"></div>
                                    <div class="va-day-header">Shifts</div>

                                    @for($hour = $earliestHour; $hour < $latestHour; $hour++)
                                        <div class="va-time-label">
                                            {{ \Carbon\Carbon::createFromTime($hour, 0)->format('g:00 A') }}
                                        </div>
                                        <div class="va-time-slot">
                                            @foreach($dayShifts as $shift)
                                                @php
                                                    $shiftStart = $shift->start_time;
                                                    $shiftEnd = $shift->end_time;
                                                    $slotStart = \Carbon\Carbon::parse($dateKey)->setTime($hour, 0, 0);
                                                    $slotEnd = \Carbon\Carbon::parse($dateKey)->setTime($hour + 1, 0, 0);

                                                    $overlaps = $shiftStart->lt($slotEnd) && $shiftEnd->gt($slotStart);

                                                    $topPercent = 0;
                                                    $heightPx = 0;
                                                    $leftPercent = 0;
                                                    $widthPercent = 100;
                                                    $blockClass = '';

                                                    $agendaIsFull   = $shift->users->count() >= $shift->max_volunteers;
                                                    $agendaSignedUp = $shift->users->contains(auth()->id());
                                                    $agendaOpen     = max(0, $shift->max_volunteers - $shift->users->count());
                                                    $agendaAccessibilityConflicts = $accessibilityConflicts->get($shift->id, []);

                                                    if ($overlaps) {
                                                        $startMinute = max(0, $shiftStart->diffInMinutes($slotStart));
                                                        $topPercent = ($startMinute / 60) * 100;

                                                        $totalDurationHours = $shiftEnd->diffInMinutes($shiftStart) / 60;
                                                        $heightPx = $totalDurationHours * 70; // 70px per hour row

                                                        if (isset($shiftPositions[$shift->id])) {
                                                            $position = $shiftPositions[$shift->id];
                                                            $columnCount = $position['columns'];
                                                            $columnIndex = $position['column'];
                                                            $widthPercent = (100 / $columnCount) - 0.5;
                                                            $leftPercent = ($columnIndex / $columnCount) * 100;
                                                        } else {
                                                            $widthPercent = 99.5;
                                                            $leftPercent = 0;
                                                        }

                                                        $blockClass = $agendaSignedUp
                                                            ? 'va-shift-signedup'
                                                            : ($agendaIsFull ? 'va-shift-full' : 'va-shift-open');
                                                    }
                                                @endphp

                                                @if($overlaps && ($shiftStart->hour == $hour || ($shiftStart->lt($slotStart) && $hour == $earliestHour)))
                                                    <div class="va-shift-block {{ $blockClass }}"
                                                        x-show="shiftVisible({{ $shift->id }})"
                                                        style="top: {{ $topPercent }}%; height: {{ $heightPx }}px; left: {{ $leftPercent }}%; width: {{ $widthPercent }}%;"
                                                        onclick="window.location='{{ route('volunteer.shifts.show', [$event, $shift]) }}'">
                                                        <div class="va-shift-name">{{ $shift->name }}</div>
                                                        <div class="va-shift-time">{{ $shift->start_time->format('g:i A') }} – {{ $shift->end_time->format('g:i A') }}</div>
                                                        <div class="va-shift-meta">
                                                            @if($agendaSignedUp)
                                                                <x-heroicon-m-check class="w-3 h-3 inline -mt-0.5"/> Signed up
                                                            @elseif($agendaIsFull)
                                                                Full
                                                            @else
                                                                {{ $shift->users->count() }}/{{ $shift->max_volunteers }} · {{ $agendaOpen }} open
                                                            @endif
                                                        </div>
                                                        @if($shift->double_hours)
                                                            <x-heroicon-m-star class="w-3 h-3 inline mt-1" title="Double Hours"/>
                                                        @endif
                                                        @if($agendaAccessibilityConflicts)
                                                            <x-heroicon-s-exclamation-triangle
                                                                class="mt-1 inline h-3 w-3 text-amber-700 dark:text-amber-300"
                                                                title="May conflict with your accessibility needs: {{ implode(', ', $agendaAccessibilityConflicts) }}"/>
                                                        @endif
                                                        @if($shift->categories->isNotEmpty())
                                                            <div class="flex flex-wrap gap-1 mt-1">
                                                                @foreach($shift->categories as $category)
                                                                    <span class="inline-block w-2 h-2 rounded-full flex-shrink-0"
                                                                        style="background-color: {{ $category->color ?: '#9CA3AF' }}"
                                                                        title="{{ $category->name }}"></span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
