<div class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:border-brand-green dark:hover:border-brand-green hover:shadow-md transition-all mb-4">
    <div class="p-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    <a href="{{ route('simple-volunteer-events.show', $event) }}" class="group-hover:text-brand-green transition-colors">
                        {{ $event->name }}
                    </a>
                </h3>
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                        <x-heroicon-m-calendar class="w-4 h-4 flex-shrink-0"/>
                        {{ $event->start_time->format('l, M j, Y') }}
                    </span>
                    <span class="flex items-center gap-1">
                        <x-heroicon-m-clock class="w-4 h-4 flex-shrink-0"/>
                        @if($event->end_time)
                            {{ $event->start_time->format('g:i A') }} &ndash; {{ $event->end_time->format('g:i A') }}
                        @else
                            {{ $event->start_time->format('g:i A') }} (ongoing)
                        @endif
                    </span>
                    @if($event->isCheckInType() && $event->end_time)
                        <span class="flex items-center gap-1">
                            <x-heroicon-m-clock class="w-4 h-4 flex-shrink-0"/>
                            {{ $event->start_time->floatDiffInHours($event->end_time) }} hours
                        </span>
                    @endif
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

                @can('manage-events')
                    <div class="mt-3 border-t border-gray-100 dark:border-gray-700 pt-3 flex flex-wrap items-center gap-4 text-xs">
                        @if($event->isRsvpType())
                            <a href="{{ route('simple-volunteer-events.rsvps', $event) }}" class="flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline">
                                <x-heroicon-m-user-group class="w-3.5 h-3.5"/> {{ $event->rsvps()->count() }} RSVPs
                            </a>
                        @else
                            <a href="{{ route('simple-volunteer-events.check-ins', $event) }}" class="flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline">
                                <x-heroicon-m-user-group class="w-3.5 h-3.5"/> {{ $event->checkIns()->count() }} check-ins
                            </a>
                        @endif
                        <form action="{{ route('simple-volunteer-events.duplicate', $event) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:underline">
                                <x-heroicon-m-document-duplicate class="w-3.5 h-3.5"/> Duplicate
                            </button>
                        </form>
                        <a href="{{ route('simple-volunteer-events.edit', $event) }}" class="flex items-center gap-1 text-gray-600 dark:text-gray-400 hover:underline">
                            <x-heroicon-m-pencil class="w-3.5 h-3.5"/> Edit
                        </a>
                    </div>
                @endcan
            </div>

            <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 flex-shrink-0 border-t sm:border-t-0 border-gray-100 dark:border-gray-700 pt-3 sm:pt-0">
                @if($event->isRsvpType())
                    @if($event->isRsvpFull())
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/30 px-3 py-1 text-xs font-medium text-red-700 dark:text-red-400">
                            <x-heroicon-m-x-circle class="w-3 h-3"/>
                            RSVPs Full
                        </span>
                    @elseif($event->rsvpSpotsRemaining() !== null)
                        <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 dark:bg-purple-900/30 px-3 py-1 text-xs font-medium text-purple-700 dark:text-purple-400">
                            <x-heroicon-m-hand-raised class="w-3 h-3"/>
                            {{ $event->rsvpSpotsRemaining() }} {{ Str::plural('spot', $event->rsvpSpotsRemaining()) }} left
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 dark:bg-purple-900/30 px-3 py-1 text-xs font-medium text-purple-700 dark:text-purple-400">
                            <x-heroicon-m-hand-raised class="w-3 h-3"/>
                            RSVP
                        </span>
                    @endif
                @elseif($event->auto_credit_hours)
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-900/30 px-3 py-1 text-xs font-medium text-green-700 dark:text-green-400">
                        <x-heroicon-m-check-circle class="w-3 h-3"/>
                        Auto-credit
                    </span>
                @endif

                <a href="{{ route('simple-volunteer-events.show', $event) }}"
                   class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500 group-hover:text-brand-green transition-colors">
                    View Details
                    <x-heroicon-m-arrow-right class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"/>
                </a>
            </div>
        </div>
    </div>
</div>
