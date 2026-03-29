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

    </div>

    <x-slot name="right">
        <p class="py-4">Here you can find upcoming events that are in need of volunteers. Each event has shifts you can sign up for. Hours are automatically credited to your volunteer profile once the event concludes.</p>
    </x-slot>
</x-app-layout>
