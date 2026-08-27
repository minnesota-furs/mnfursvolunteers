<x-app-layout>
    @section('title', 'Dashboard')
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>
    
    <x-slot name="actions">
        <a href="{{ route('users.show', Auth::user()->id) }}"
            class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-user class="w-4 inline" /> View Your Profile
        </a>
        @if (Auth::user()->isStaff)
        <a href="{{ route('hours.create', Auth::user()->id) }}"
            class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-clock class="w-4 inline" /> Log New Hours
        </a>
        @endif
    </x-slot>

    <x-slot name="postHeader">
        <div>
            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Hours This Year</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        {{ format_hours(Auth::user()->totalHoursForCurrentFiscalLedger()) }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Lifetime Hours</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        {{ floor(Auth::user()->totalVolunteerHours()) }}</dd>
                </div>
                @if (Auth::user()->isStaff)
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Department(s)</dt>
                    @if (Auth::user()->hasDept())
                        @php($departments = Auth::user()->departments)
                        <dd class="mt-2" @if ($departments->count() > 2) x-data="{ expanded: false }" @endif>
                            <ul id="additional-departments" class="space-y-1.5" aria-label="Your departments">
                                @foreach ($departments as $department)
                                    <li @if ($loop->index >= 2) x-show="expanded" x-cloak @endif
                                        class="flex min-w-0 items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="min-w-0 truncate font-semibold">{{ $department->name }}</span>
                                        <span class="flex-shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true">&middot;</span>
                                        <span class="min-w-0 truncate text-gray-500 dark:text-gray-400">{{ $department->sector->name }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            @if ($departments->count() > 2)
                                <button type="button"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-green hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green"
                                    x-on:click="expanded = ! expanded"
                                    x-bind:aria-expanded="expanded"
                                    aria-controls="additional-departments">
                                    <span x-text="expanded ? 'Show fewer' : 'Show {{ $departments->count() - 2 }} more'">Show {{ $departments->count() - 2 }} more</span>
                                    <x-heroicon-m-chevron-down class="h-4 w-4 transition-transform" x-bind:class="expanded && 'rotate-180'" />
                                </button>
                            @endif
                        </dd>
                    @else
                        <dd class="mt-1 text-xl font-semibold tracking-tight text-gray-300 dark:text-gray-500">No Department Assigned</dd>
                    @endif
                </div>
                @else
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Volunteer Code</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        {{Auth::user()->vol_code}}</dd>
                </div>
                @endif
            </dl>

            <x-profile-completion-notice />

            <x-concat-link-notice />

            <x-no-show-warning :recentNoShows="$recentNoShows" />

            <x-announcements-dashboard-notice :announcements="$announcements" />

            <x-elections-dashboard-notice :activeElections="$activeElections" />

            <x-applications-dashboard-notice 
                :unclaimedPendingCount="$unclaimedPendingCount" 
                :claimedApplications="$claimedApplications" 
            />

            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="text-xl font-bold mb-1 text-gray-500 dark:text-gray-400">Upcoming Volunteer Opportunities
                        ({{ $upcomingEvents->count() }})</dt>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-3">These events and departments are looking for volunteers!</p>
                    <dd class="mt-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            @forelse($upcomingEvents as $event)
                                <div class="relative" x-data="{ show: false, timer: null }"
                                     @mouseenter="timer = setTimeout(() => show = true, 300)"
                                     @mouseleave="clearTimeout(timer); show = false">
                                    <a href="{{ route('volunteer.events.show', $event) }}"
                                       class="group flex items-center gap-2.5 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-2.5 py-1.5 hover:border-brand-green dark:hover:border-brand-green hover:bg-green-50 dark:hover:bg-brand-green/10 transition-all">
                                        <div class="flex-shrink-0 text-center leading-tight rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 px-1.5 py-0.5 shadow-sm">
                                            <div class="text-[10px] font-semibold uppercase text-brand-green">{{ $event->start_date->format('M') }}</div>
                                            <div class="text-base font-bold text-gray-900 dark:text-gray-100 -mt-0.5">{{ $event->start_date->format('j') }}</div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-brand-green transition-colors truncate">{{ $event->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                @if($event->isMultiDay())
                                                    {{ $event->start_date->format('M j') }} – {{ $event->end_date->format('M j, Y') }}
                                                @else
                                                    {{ $event->start_date->format('l, g:i A') }}
                                                @endif
                                                @if($event->location)
                                                    · {{ $event->location }}
                                                @endif
                                            </p>
                                        </div>
                                        <x-heroicon-m-chevron-right class="w-4 h-4 flex-shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-brand-green transition-colors"/>
                                    </a>

                                    <x-event-hover-popover :name="$event->name" :description="$event->description">
                                        <p class="flex items-center gap-1.5">
                                            <x-heroicon-m-calendar class="w-3.5 h-3.5 flex-shrink-0"/>
                                            @if($event->isMultiDay())
                                                {{ $event->start_date->format('l, M j, Y g:i A') }} &ndash; {{ $event->end_date->format('l, M j, Y g:i A') }}
                                            @else
                                                {{ $event->start_date->format('l, M j, Y') }} at {{ $event->start_date->format('g:i A') }}
                                            @endif
                                        </p>
                                        @if($event->location)
                                            <p class="flex items-center gap-1.5">
                                                <x-heroicon-m-map-pin class="w-3.5 h-3.5 flex-shrink-0"/>
                                                {{ $event->location }}
                                            </p>
                                        @endif
                                    </x-event-hover-popover>
                                </div>
                            @empty
                                <p class="text-gray-300 dark:text-gray-500">No upcoming events in need of volunteers.</p>
                            @endforelse
                        </div>
                    </dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="text-xl font-bold mb-1 text-gray-500 dark:text-gray-400">Your Upcoming Volunteer Assignments
                        ({{ $upcomingShifts->flatten()->count() }})</dt>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-3">Shifts you've signed up for across upcoming events.</p>
                    <dd class="mt-1">
                        <div class="space-y-3">
                            @forelse($upcomingShifts as $eventName => $shifts)
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1.5">{{ $eventName }}</h3>
                                    <div class="space-y-1.5 pl-1">
                                        @foreach ($shifts->take(5) as $shift)
                                            <div class="group flex items-center gap-2.5 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-2.5 py-1.5 transition-all">
                                                <div class="flex-shrink-0 text-center leading-tight rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 px-1.5 py-0.5 shadow-sm">
                                                    <div class="text-[10px] font-semibold uppercase text-brand-green">{{ $shift->start_time->format('M') }}</div>
                                                    <div class="text-base font-bold text-gray-900 dark:text-gray-100 -mt-0.5">{{ $shift->start_time->format('j') }}</div>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $shift->name }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $shift->start_time->format('g:i A') }}</p>
                                                </div>
                                                <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{{ $shift->start_time->diffForHumans() }}</span>
                                            </div>
                                        @endforeach
                                        @if ($shifts->count() > 5)
                                            <div class="flex items-center justify-center rounded-lg border border-dashed border-gray-200 dark:border-gray-700 px-2.5 py-1.5">
                                                <p class="text-xs text-gray-400 dark:text-gray-500 italic">and {{ $shifts->count() - 5 }} more…</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-300 dark:text-gray-500">No upcoming volunteer slots.</p>
                            @endforelse
                        </div>

                        <div class="mt-4 flex gap-4">
                            <a href="{{ route('volunteer.events.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View Volunteer Opportunities</a>
                            <a href="{{ route('volunteer.events.my-shifts-all') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View Full Itinerary</a>
                        </div>
                    </dd>
                </div>
                @feature('one_off_events')
                    <div class="rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                        <dt class="text-xl font-bold mb-1 text-gray-500 dark:text-gray-400">Simple Volunteer Events You're Eligible For
                            ({{ $eligibleSimpleEvents->count() }})</dt>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mb-3">Meetings, socials, and other simple events you can check in to or RSVP for.</p>
                        <dd class="mt-1">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @forelse($eligibleSimpleEvents as $event)
                                    <div class="relative" x-data="{ show: false, timer: null }"
                                         @mouseenter="timer = setTimeout(() => show = true, 300)"
                                         @mouseleave="clearTimeout(timer); show = false">
                                        <a href="{{ route('simple-volunteer-events.show', $event) }}"
                                           class="group flex items-center gap-2.5 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-2.5 py-1.5 hover:border-brand-green dark:hover:border-brand-green hover:bg-green-50 dark:hover:bg-brand-green/10 transition-all">
                                            <div class="flex-shrink-0 text-center leading-tight rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 px-1.5 py-0.5 shadow-sm">
                                                <div class="text-[10px] font-semibold uppercase text-brand-green">{{ $event->start_time->format('M') }}</div>
                                                <div class="text-base font-bold text-gray-900 dark:text-gray-100 -mt-0.5">{{ $event->start_time->format('j') }}</div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-brand-green transition-colors truncate">{{ $event->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $event->start_time->format('l, g:i A') }}
                                                    @if($event->location)
                                                        · {{ $event->location }}
                                                    @endif
                                                </p>
                                            </div>
                                            @if($event->isHappeningNow())
                                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-0.5 text-[10px] font-medium text-red-700 dark:text-red-400 flex-shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                                    Now
                                                </span>
                                            @endif
                                            @if($event->isRsvpType())
                                                <span class="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-900/30 px-2 py-0.5 text-[10px] font-medium text-purple-700 dark:text-purple-400 flex-shrink-0">
                                                    RSVP
                                                </span>
                                            @endif
                                            <x-heroicon-m-chevron-right class="w-4 h-4 flex-shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-brand-green transition-colors"/>
                                        </a>

                                        <x-event-hover-popover :name="$event->name" :description="$event->description">
                                            <p class="flex items-center gap-1.5">
                                                <x-heroicon-m-calendar class="w-3.5 h-3.5 flex-shrink-0"/>
                                                {{ $event->start_time->format('l, M j, Y') }} at {{ $event->start_time->format('g:i A') }}
                                                @if($event->end_time)
                                                    &ndash; {{ $event->end_time->format('g:i A') }}
                                                @else
                                                    (ongoing)
                                                @endif
                                            </p>
                                            @if($event->location)
                                                <p class="flex items-center gap-1.5">
                                                    <x-heroicon-m-map-pin class="w-3.5 h-3.5 flex-shrink-0"/>
                                                    {{ $event->location }}
                                                </p>
                                            @endif
                                            <p class="flex items-center gap-1.5">
                                                @if($event->isRsvpType())
                                                    <x-heroicon-m-hand-raised class="w-3.5 h-3.5 flex-shrink-0"/>
                                                    RSVP event &mdash; no hours credited
                                                    @if($event->rsvpSpotsRemaining() !== null)
                                                        ({{ $event->rsvpSpotsRemaining() }} {{ Str::plural('spot', $event->rsvpSpotsRemaining()) }} left)
                                                    @endif
                                                @else
                                                    <x-heroicon-m-check-circle class="w-3.5 h-3.5 flex-shrink-0"/>
                                                    Check-in event &mdash; earns volunteer hours
                                                @endif
                                            </p>
                                        </x-event-hover-popover>
                                    </div>
                                @empty
                                    <p class="text-gray-300 dark:text-gray-500">No simple volunteer events you're eligible for right now.</p>
                                @endforelse
                            </div>
                        </dd>
                    </div>
                @endfeature
            </dl>
        </div>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- {{ __("You're logged in!") }} --}}
        </div>
    </div>

    {{-- <x-slot name="right">
        <p class="py-4">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Dicta quasi aperiam facere! Blanditiis accusamus minima totam omnis qui eos alias quod, obcaecati in? Necessitatibus iure blanditiis soluta neque? Veritatis, fugit!</p>
        <p class="py-4">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Fuga iure maxime, temporibus rerum odio at omnis deserunt eos ea dolores neque atque debitis natus iste laborum quod, autem voluptas consequatur?</p>
    </x-slot> --}}

</x-app-layout>
