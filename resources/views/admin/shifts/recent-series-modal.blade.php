{{-- Recent Series Creations Modal --}}
<div x-data="{ open: false }"
     @open-recent-series-modal.window="open = true"
     @keydown.escape.window="open = false"
     x-cloak
     class="relative z-50">

    {{-- Modal Overlay --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         @click="open = false">
    </div>

    {{-- Modal Panel --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-50 overflow-y-auto">

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                 @click.stop>

                {{-- Header --}}
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-clock class="h-6 w-6 text-blue-600 dark:text-blue-300"/>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Recent Series Creations</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    The last {{ $recentSeriesHistory->count() }} shift {{ Str::plural('series', $recentSeriesHistory->count()) }} created for this event.
                                </p>
                            </div>
                        </div>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4 max-h-96 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700 -mx-4 sm:-mx-6">
                        @forelse($recentSeriesHistory as $series)
                            <div class="flex flex-wrap items-center justify-between gap-2 px-4 sm:px-6 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $series->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $series->created_shift_count }} {{ Str::plural('shift', $series->created_shift_count) }}
                                        by {{ $series->created_by }}
                                        &middot;
                                        <span title="{{ $series->created_at->format('F j, Y g:i A') }}">{{ $series->created_at->diffForHumans() }}</span>
                                    </p>
                                    @if($series->remaining_count === 0)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                            <x-heroicon-m-check class="w-3 h-3 inline -mt-0.5"/> Already removed
                                        </p>
                                    @elseif($series->dismissed)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Dismissed from the banner</p>
                                    @elseif($series->has_signups)
                                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                                            <x-heroicon-s-exclamation-triangle class="w-3 h-3 inline -mt-0.5"/> Can't undo — some shifts already have volunteers signed up
                                        </p>
                                    @elseif(!$series->within_window)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Can't undo — created more than 6 hours ago</p>
                                    @endif
                                </div>
                                <div class="flex-shrink-0">
                                    @if($series->can_undo)
                                        <form action="{{ route('admin.events.shifts.undo-series', [$event, $series->log_id]) }}" method="POST"
                                            onsubmit="return confirm('Undo the &quot;{{ addslashes($series->name) }}&quot; series? This will permanently delete {{ $series->remaining_count }} shift(s).')">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 shadow-sm ring-1 ring-inset ring-blue-300 dark:ring-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/40">
                                                <x-heroicon-m-arrow-uturn-left class="w-3.5 h-3.5"/>
                                                Undo
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-medium text-gray-400 dark:text-gray-500">
                                            <x-heroicon-m-arrow-uturn-left class="w-3.5 h-3.5"/>
                                            Undo
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No shift series have been created for this event yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
