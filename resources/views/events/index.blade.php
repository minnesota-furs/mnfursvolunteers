<x-app-layout>
    @section('title', 'Events')
    <x-slot name="header">
        {{ __('Events') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('volunteer.events.my-shifts-all') }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-green shadow-sm hover:bg-gray-100 transition-colors">
            <x-heroicon-m-list-bullet class="w-4 h-4"/>
            My Full Itinerary
        </a>
    </x-slot>

    <div class="space-y-8">

        {{-- ── Filter Toggle ───────────────────────────────────────────── --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('volunteer.events.index', ['filter' => 'eligible']) }}"
               class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-medium border transition-colors
                      {{ $filter === 'eligible'
                          ? 'bg-brand-green text-white border-brand-green'
                          : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-brand-green hover:text-brand-green' }}">
                <x-heroicon-m-check-badge class="w-4 h-4"/>
                Eligible for me
            </a>
            <a href="{{ route('volunteer.events.index', ['filter' => 'all']) }}"
               class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-medium border transition-colors
                      {{ $filter === 'all'
                          ? 'bg-brand-green text-white border-brand-green'
                          : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-brand-green hover:text-brand-green' }}">
                <x-heroicon-m-list-bullet class="w-4 h-4"/>
                Show all
            </a>
        </div>

        {{-- ── Upcoming Events ──────────────────────────────────────────── --}}
        <section>
            <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                Upcoming Events
            </h2>

            @forelse($upcomingEvents as $event)
                @php
                    $spots = $event->remaining_volunteer_spots;
                    $isSignupOpen = !$event->signup_open_date || $event->signup_open_date->isPast();
                    $hasLimitations = $event->requiredTags->isNotEmpty() || $event->requiredDepartments->isNotEmpty();
                @endphp

                <a href="{{ route('volunteer.events.show', $event) }}"
                   class="group block bg-white dark:bg-gray-800 rounded-xl border shadow-sm hover:border-brand-green dark:hover:border-brand-green hover:shadow-md transition-all mb-4
                          {{ ($filter === 'all' && !$event->is_eligible)
                              ? 'border-gray-200 dark:border-gray-700 opacity-75'
                              : 'border-gray-200 dark:border-gray-700' }}">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-brand-green transition-colors truncate">
                                    {{ $event->name }}
                                </h3>
                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-m-calendar class="w-4 h-4 flex-shrink-0"/>
                                        @if($event->isMultiDay())
                                            {{ $event->start_date->format('M j') }} – {{ $event->end_date->format('M j, Y') }}
                                        @else
                                            {{ $event->start_date->format('l, M j, Y') }}
                                        @endif
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-m-clock class="w-4 h-4 flex-shrink-0"/>
                                        {{ $event->start_date->format('g:i A') }} – {{ $event->end_date->format('g:i A') }}
                                    </span>
                                    @if($event->location)
                                        <span class="flex items-center gap-1">
                                            <x-heroicon-m-map-pin class="w-4 h-4 flex-shrink-0"/>
                                            {{ $event->location }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Limitations: required tags + departments --}}
                                @if($hasLimitations)
                                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                                        @if($event->requiredDepartments->isNotEmpty())
                                            <span class="text-xs text-gray-400 dark:text-gray-500 mr-0.5">Dept:</span>
                                            @foreach($event->requiredDepartments as $dept)
                                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $dept->name }}
                                                </span>
                                            @endforeach
                                        @endif

                                        @if($event->requiredTags->isNotEmpty())
                                            <span class="text-xs text-gray-400 dark:text-gray-500 mr-0.5 {{ $event->requiredDepartments->isNotEmpty() ? 'ml-2' : '' }}">Required:</span>
                                            @foreach($event->requiredTags as $tag)
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                      @if($tag->color)
                                                          style="background-color: {{ $tag->color }}22; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}55;"
                                                      @else
                                                          style="background-color: #e5e7eb; color: #374151;"
                                                      @endif>
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                {{-- Not eligible indicator (only in "show all" mode) --}}
                                @if($filter === 'all' && !$event->is_eligible)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-900/20 px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800">
                                        <x-heroicon-m-no-symbol class="w-3 h-3"/>
                                        Not eligible
                                    </span>
                                @endif

                                {{-- Availability badge --}}
                                @if(!$isSignupOpen)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300">
                                        <x-heroicon-m-lock-closed class="w-3 h-3"/>
                                        Signup Opens {{ $event->signup_open_date->format('M j') }}
                                    </span>
                                @elseif($spots === 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/30 px-3 py-1 text-xs font-medium text-red-700 dark:text-red-400">
                                        <x-heroicon-m-x-circle class="w-3 h-3"/>
                                        Full
                                    </span>
                                @elseif($spots <= 5)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">
                                        <x-heroicon-m-fire class="w-3 h-3"/>
                                        {{ $spots }} {{ Str::plural('spot', $spots) }} left
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-900/30 px-3 py-1 text-xs font-medium text-green-700 dark:text-green-400">
                                        <x-heroicon-m-check-circle class="w-3 h-3"/>
                                        {{ $spots }} open {{ Str::plural('spot', $spots) }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                    <x-heroicon-m-arrow-right class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"/>
                                    View shifts
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-10 text-center">
                    <x-heroicon-o-calendar-days class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3"/>
                    @if($filter === 'eligible')
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No upcoming events you're eligible for.</p>
                        <a href="{{ route('volunteer.events.index', ['filter' => 'all']) }}"
                           class="text-sm text-brand-green hover:underline">Show all events</a>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events at this time.</p>
                    @endif
                </div>
            @endforelse
        </section>

        {{-- ── Past Events ──────────────────────────────────────────────── --}}
        @if($recentPastEvents->isNotEmpty())
        <section>
            <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                Recent Past Events
            </h2>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($recentPastEvents as $event)
                    @php $hasLimitations = $event->requiredTags->isNotEmpty() || $event->requiredDepartments->isNotEmpty(); @endphp
                    <a href="{{ route('volunteer.events.show', $event) }}"
                       class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors first:rounded-t-xl last:rounded-b-xl
                              {{ ($filter === 'all' && !$event->is_eligible) ? 'opacity-75' : '' }}">
                        <div class="min-w-0 flex-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate block">
                                {{ $event->name }}
                            </span>
                            @if($hasLimitations)
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($event->requiredDepartments as $dept)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-600 dark:text-gray-400">
                                            {{ $dept->name }}
                                        </span>
                                    @endforeach
                                    @foreach($event->requiredTags as $tag)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                              @if($tag->color)
                                                  style="background-color: {{ $tag->color }}22; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}55;"
                                              @else
                                                  style="background-color: #e5e7eb; color: #374151;"
                                              @endif>
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($filter === 'all' && !$event->is_eligible)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-900/20 px-2 py-0.5 text-xs font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800">
                                    <x-heroicon-m-no-symbol class="w-3 h-3"/>
                                    Not eligible
                                </span>
                            @endif
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $event->start_date->format('M j') }}
                                @if($event->isMultiDay()) – {{ $event->end_date->format('M j') }}@endif,
                                {{ $event->start_date->format('Y') }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>

    <x-slot name="right">
        <p class="py-4">Here you can find upcoming events that are in need of volunteers. Each event has shifts you can sign up for. Hours are automatically credited to your volunteer profile once the event concludes.</p>
    </x-slot>
</x-app-layout>
