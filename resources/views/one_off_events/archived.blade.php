<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Archived Events') }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('one-off-events.index') }}"
                class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
                <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Upcoming
            </a>
        </x-slot>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Archived Events</h1>
                <p class="text-gray-600 dark:text-gray-400">View and manage past one-off events</p>
            </div>

            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="{{ route('one-off-events.index') }}"
                       class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                        Upcoming Events
                    </a>
                    <a href="{{ route('one-off-events.archived') }}"
                       class="border-brand-green text-brand-green whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                        Archived Events
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                            {{ $events->total() }}
                        </span>
                    </a>
                </nav>
            </div>

            @if($events->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                    <x-heroicon-o-archive-box class="w-16 h-16 mx-auto mb-4 text-gray-400"/>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Archived Events</h2>
                    <p class="text-gray-600 dark:text-gray-400">There are no past events to display.</p>
                </div>
            @else
                <!-- Events List -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($events as $event)
                            <li class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="px-6 py-5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                    <a href="{{ route('one-off-events.show', $event) }}" class="hover:text-brand-green">
                                                        {{ $event->name }}
                                                    </a>
                                                </h3>
                                                @if($event->auto_credit_hours)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">
                                                        <x-heroicon-s-check-circle class="w-3 h-3 mr-1"/>
                                                        Auto-credit
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    <x-heroicon-s-archive-box class="w-3 h-3 mr-1"/>
                                                    Ended
                                                </span>
                                            </div>

                                            @if($event->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                                    {{ $event->description }}
                                                </p>
                                            @endif

                                            <div class="flex flex-wrap gap-4 text-sm">
                                                <div class="flex items-center text-gray-700 dark:text-gray-300">
                                                    <x-heroicon-m-calendar class="w-4 h-4 mr-1.5 text-gray-400"/>
                                                    {{ $event->start_time->format('M j, Y') }}
                                                </div>
                                                <div class="flex items-center text-gray-700 dark:text-gray-300">
                                                    <x-heroicon-m-clock class="w-4 h-4 mr-1.5 text-gray-400"/>
                                                    {{ $event->start_time->format('g:i A') }} - {{ $event->end_time->format('g:i A') }}
                                                </div>
                                                <div class="flex items-center text-gray-700 dark:text-gray-300">
                                                    <x-heroicon-m-user-group class="w-4 h-4 mr-1.5 text-gray-400"/>
                                                    {{ $event->checkIns()->count() }} check-in{{ $event->checkIns()->count() !== 1 ? 's' : '' }}
                                                </div>
                                                <div class="flex items-center text-gray-500 dark:text-gray-400">
                                                    <x-heroicon-m-clock class="w-4 h-4 mr-1.5 text-gray-400"/>
                                                    Ended {{ $event->end_time->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 ml-4">
                                            <a href="{{ route('one-off-events.show', $event) }}"
                                               class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                                <x-heroicon-o-eye class="w-4 h-4 mr-1"/>
                                                View
                                            </a>
                                            <a href="{{ route('one-off-events.check-ins', $event) }}"
                                               class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                                <x-heroicon-o-user-group class="w-4 h-4 mr-1"/>
                                                Check-ins
                                            </a>
                                            <form action="{{ route('one-off-events.duplicate', $event) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                                    <x-heroicon-o-document-duplicate class="w-4 h-4 mr-1"/>
                                                    Duplicate
                                                </button>
                                            </form>
                                            <a href="{{ route('one-off-events.edit', $event) }}"
                                               class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                                <x-heroicon-o-pencil class="w-4 h-4 mr-1"/>
                                                Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    @endauth
</x-app-layout>
