@php
    $spots = $event->remaining_volunteer_spots;
    $isSignupOpen = !$event->signup_open_date || $event->signup_open_date->isPast();
    $hasLimitations = $event->requiredTags->isNotEmpty() || $event->requiredDepartments->isNotEmpty();
    $dimmed = $dimmed ?? false;

    $userTagIds = $userTagIds ?? auth()->user()->tags()->pluck('tags.id')->all();
    $userDeptIds = $userDeptIds ?? auth()->user()->departments()->pluck('departments.id')->all();
    $requiredTagIds = $event->requiredUserTags->pluck('id')->all();
    $requiredDeptIds = $event->requiredDepartments->pluck('id')->all();
    $isEligible = empty(array_diff($requiredTagIds, $userTagIds))
        && (empty($requiredDeptIds) || !empty(array_intersect($requiredDeptIds, $userDeptIds)));
@endphp

<a href="{{ route('volunteer.events.show', $event) }}"
   class="group block bg-white dark:bg-gray-800 rounded-xl border shadow-sm hover:border-brand-green dark:hover:border-brand-green hover:shadow-md transition-all mb-4
          {{ $dimmed ? 'border-gray-200 dark:border-gray-700 opacity-75' : 'border-gray-200 dark:border-gray-700' }}">
    <div class="p-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
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

                @if($event->description)
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $event->description }}</p>
                @endif

                {{-- Limitations: required tags + departments + perks --}}
                @if($hasLimitations || $event->active_perks_count > 0)
                    <div class="mt-3 border-t border-gray-100 dark:border-gray-700 pt-3 flex flex-wrap items-center gap-1.5">
                        @if($event->requiredDepartments->isNotEmpty())
                            <span class="text-xs text-gray-400 dark:text-gray-500 mr-0.5">Requires Dept:</span>
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

                        @if($event->active_perks_count > 0)
                            <span class="inline-flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400 {{ $hasLimitations ? 'ml-2' : '' }}">
                                <x-heroicon-m-gift class="w-3.5 h-3.5 flex-shrink-0"/>
                                Earns perks
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 flex-shrink-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-700 pt-3 sm:pt-0">
                {{-- Not eligible indicator --}}
                @if($dimmed)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-900/20 px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800">
                        <x-heroicon-m-no-symbol class="w-3 h-3"/>
                        Not eligible
                    </span>
                @endif

                {{-- Availability badge --}}
                @if(!$isEligible)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-900/20 px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800">
                        <x-heroicon-m-no-symbol class="w-3 h-3"/>
                        Ineligible
                    </span>
                @elseif(!$isSignupOpen)
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
