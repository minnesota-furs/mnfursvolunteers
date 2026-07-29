<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Simple Volunteer Events') }}
        </x-slot>

        <x-slot name="actions">
            @can('manage-events')
                <a href="{{ route('simple-volunteer-events.scanner') }}"
                    class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700">
                    <x-heroicon-o-qr-code class="w-4 inline"/> Scanner
                </a>
                <a href="{{ route('simple-volunteer-events.archived') }}"
                    class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
                    <x-heroicon-o-archive-box class="w-4 inline"/> Archived Events
                </a>
                <a href="{{route('simple-volunteer-events.create')}}"
                        class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                        <x-heroicon-s-plus class="w-4 inline"/> New Event
                    </a>
            @endcan
        </x-slot>

        <div class="space-y-8">

            <section>
                <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                    Upcoming Events
                </h2>

                @forelse($events as $event)
                    @include('one_off_events._event-card', ['event' => $event, 'checkedInEventIds' => $checkedInEventIds, 'rsvpedEventIds' => $rsvpedEventIds])
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-10 text-center">
                        <x-heroicon-o-calendar-days class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3"/>
                        <p class="text-sm text-gray-500 dark:text-gray-400">There are no simple volunteer events scheduled at this time.</p>
                        @can('manage-events')
                            <a href="{{ route('simple-volunteer-events.create') }}"
                               class="mt-4 inline-flex items-center px-4 py-2 bg-brand-green text-white rounded-md hover:bg-indigo-700">
                                <x-heroicon-s-plus class="w-5 h-5 mr-2"/> Create Event
                            </a>
                        @endcan
                    </div>
                @endforelse
            </section>

            @if($pastEvents->isNotEmpty())
                <section>
                    <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                        Previous Events
                    </h2>

                    @foreach($pastEvents as $event)
                        <a href="{{ route('simple-volunteer-events.show', $event) }}"
                           class="group flex items-center justify-between gap-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:border-brand-green dark:hover:border-brand-green hover:shadow-md transition-all mb-2 px-4 py-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-brand-green transition-colors truncate">
                                    {{ $event->name }}
                                    @if(in_array($event->id, $checkedInEventIds))
                                        <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-green-800/30 dark:text-green-400">
                                            Thanks! You checked in!
                                        </span>
                                    @elseif(in_array($event->id, $rsvpedEventIds))
                                        <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-green-800/30 dark:text-green-400">
                                            Thanks! You RSVP'd!
                                        </span>
                                    @endif
                                </h3>
                                <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-m-calendar class="w-3.5 h-3.5 flex-shrink-0"/>
                                        {{ $event->start_time->format('M j, Y') }}
                                    </span>
                                </div>
                            </div>
                            <x-heroicon-m-arrow-right class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-brand-green group-hover:translate-x-0.5 transition-all flex-shrink-0"/>
                        </a>
                    @endforeach

                    <div class="mt-4">
                        {{ $pastEvents->links('vendor.pagination.custom') }}
                    </div>
                </section>
            @endif

        </div>
    @endauth
</x-app-layout>
