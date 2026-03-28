@props(['recentNoShows'])

@if($recentNoShows->isNotEmpty())
<div class="mt-5">
    <div class="overflow-hidden rounded-lg bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-950 dark:to-rose-950 border-2 border-red-300 dark:border-red-700 px-4 py-5 shadow-lg sm:p-6">
        <h3 class="text-xl font-bold mb-2 text-red-800 dark:text-red-200 flex items-center">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 mr-2 flex-shrink-0" />
            Recent No-Show Notice
        </h3>

        <p class="text-red-700 dark:text-red-300 mb-4 text-sm leading-relaxed">
            You have been marked as a <strong>no-show</strong> on
            {{ $recentNoShows->count() === 1 ? 'a shift' : $recentNoShows->count() . ' shifts' }}
            in the past 14 days. Please review the details below.
        </p>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-700 divide-y divide-red-100 dark:divide-red-800 mb-4">
            @foreach($recentNoShows as $shift)
                <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $shift->name }}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">— {{ $shift->event->name }}</span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span>{{ $shift->start_time->format('M j, Y g:i A') }}</span>
                        @if($shift->pivot->no_show_marked_at)
                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">
                                Marked {{ \Carbon\Carbon::parse($shift->pivot->no_show_marked_at)->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-red-100 dark:bg-red-900/40 rounded-lg px-4 py-3 text-sm text-red-800 dark:text-red-200">
            <p class="font-semibold mb-1 flex items-center">
                <x-heroicon-o-bell-alert class="w-4 h-4 mr-1.5 flex-shrink-0" />
                Reminder: Please Cancel Shifts You Can No Longer Attend
            </p>
            <p class="leading-relaxed">
                If you have signed up for upcoming shifts and are no longer able to commit, please
                <a href="{{ route('volunteer.events.my-shifts-all') }}" class="font-semibold underline hover:text-red-900 dark:hover:text-red-100">
                    cancel them as soon as possible
                </a>
                so another volunteer can take your place. Repeated no-shows may affect your ability to sign up for future shifts.
            </p>
        </div>
    </div>
</div>
@endif
