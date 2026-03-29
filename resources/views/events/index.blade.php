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

        <section>
            <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                Upcoming Events
            </h2>

            @forelse($events as $event)
                @include('events._event-card', ['event' => $event, 'userTagIds' => $userTagIds, 'userDeptIds' => $userDeptIds])
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-10 text-center">
                    <x-heroicon-o-calendar-days class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3"/>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events available.</p>
                </div>
            @endforelse
        </section>

        @if($pastEvents->isNotEmpty())
            <section>
                <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                    Previous Events
                </h2>

                @foreach($pastEvents as $event)
                    <a href="{{ route('volunteer.events.show', $event) }}"
                       class="group flex items-center justify-between gap-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:border-brand-green dark:hover:border-brand-green hover:shadow-md transition-all mb-2 px-4 py-3">
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-brand-green transition-colors truncate">
                                {{ $event->name }}
                                @if(in_array($event->id, $workedEventIds))
                                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-green-800/30 dark:text-green-400">
                                        Thanks! You worked this!
                                    </span>
                                @endif
                            </h3>
                            <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                                <span class="flex items-center gap-1">
                                    <x-heroicon-m-calendar class="w-3.5 h-3.5 flex-shrink-0"/>
                                    @if($event->isMultiDay())
                                        {{ $event->start_date->format('M j') }} – {{ $event->end_date->format('M j, Y') }}
                                    @else
                                        {{ $event->start_date->format('M j, Y') }}
                                    @endif
                                </span>
                                @if($event->location)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-m-map-pin class="w-3.5 h-3.5 flex-shrink-0"/>
                                        {{ $event->location }}
                                    </span>
                                @endif
                                @if($event->active_perks_count > 0)
                                    <span class="flex items-center gap-1 text-slate-500 dark:text-slate-400">
                                        <x-heroicon-m-gift class="w-3.5 h-3.5 flex-shrink-0"/>
                                        Earns perks
                                    </span>
                                @endif
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

    <x-slot name="right">
        <p class="py-4">Here you can find upcoming events that are in need of volunteers. Each event has shifts you can sign up for. Hours are automatically credited to your volunteer profile once the event concludes.</p>
    </x-slot>
</x-app-layout>
