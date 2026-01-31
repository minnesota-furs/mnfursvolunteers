<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('One Off Events') }}
        </x-slot>

        <x-slot name="actions">
            @can('manage-events')
                <a href="{{ route('one-off-events.archived') }}"
                    class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
                    <x-heroicon-o-archive-box class="w-4 inline"/> Archived Events
                </a>
                <a href="{{route('one-off-events.create')}}"
                        class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                        <x-heroicon-s-plus class="w-4 inline"/> New Event
                    </a>
            @endcan
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">One-Off Events</h1>
                    <p class="text-gray-600 dark:text-gray-400">Check in to events to earn volunteer hours</p>
                </div>

                <!-- Tab Navigation -->
                @can('manage-events')
                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <a href="{{ route('one-off-events.index') }}"
                               class="border-brand-green text-brand-green whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                                Upcoming Events
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    {{ $events->count() }}
                                </span>
                            </a>
                            <a href="{{ route('one-off-events.archived') }}"
                               class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                                Archived Events
                            </a>
                        </nav>
                    </div>
                @endcan

                @if($events->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                        <x-heroicon-o-calendar-days class="w-16 h-16 mx-auto mb-4 text-gray-400"/>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Upcoming Events</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">There are no one-off events scheduled at this time.</p>
                        @can('manage-events')
                            <a href="{{ route('one-off-events.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-brand-green text-white rounded-md hover:bg-indigo-700">
                                <x-heroicon-s-plus class="w-5 h-5 mr-2"/> Create Event
                            </a>
                        @endcan
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($events as $event)
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-3">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            <a href="{{ route('one-off-events.show', $event) }}" class="hover:text-brand-green">
                                                {{ $event->name }}
                                            </a>
                                        </h3>
                                        @if($event->auto_credit_hours)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">
                                                Auto-credit
                                            </span>
                                        @endif
                                    </div>

                                    @if($event->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                            {{ $event->description }}
                                        </p>
                                    @endif

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <x-heroicon-m-calendar class="w-4 h-4 mr-2 text-gray-400"/>
                                            {{ $event->start_time->format('F j, Y') }}
                                        </div>
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <x-heroicon-m-clock class="w-4 h-4 mr-2 text-gray-400"/>
                                            {{ $event->start_time->format('g:i A') }} - {{ $event->end_time->format('g:i A') }}
                                        </div>
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <x-heroicon-m-clock class="w-4 h-4 mr-2 text-gray-400"/>
                                            {{ $event->start_time->floatDiffInHours($event->end_time) }} hours
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('one-off-events.show', $event) }}"
                                           class="block w-full text-center px-4 py-2 bg-brand-green text-white rounded-md hover:bg-indigo-700 transition-colors">
                                            View Details
                                        </a>
                                    </div>

                                    @can('manage-events')
                                        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between text-xs">
                                            <a href="{{ route('one-off-events.check-ins', $event) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                <x-heroicon-m-user-group class="w-4 h-4 inline"/> {{ $event->checkIns()->count() }} check-ins
                                            </a>
                                            <a href="{{ route('one-off-events.edit', $event) }}" class="text-gray-600 dark:text-gray-400 hover:underline">
                                                <x-heroicon-m-pencil class="w-4 h-4 inline"/> Edit
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endauth
</x-app-layout>
