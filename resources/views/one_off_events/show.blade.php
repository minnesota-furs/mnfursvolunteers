<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('One Off Event: ') }} {{ $oneOffEvent->name }}
        </x-slot>

        <x-slot name="actions">
            @can('manage-events')
                <a href="{{ route('one-off-events.check-ins', $oneOffEvent) }}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                    <x-heroicon-o-user-group class="w-4 inline"/> View Check-ins ({{ $oneOffEvent->checkIns()->count() }})
                </a>
                <a href="{{ route('one-off-events.edit', $oneOffEvent) }}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                    <x-heroicon-m-pencil class="w-4 inline"/> Edit Event
                </a>
            @endcan
            <a href="{{ route('one-off-events.index') }}"
                class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
                <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Events
            </a>
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $oneOffEvent->name }}</h1>
                
                    <p class="text-gray-700 dark:text-gray-300 mb-2">{{ $oneOffEvent->description }}</p>
                
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <strong>Starts:</strong> {{ $oneOffEvent->start_time->format('F j, Y g:i A') }}<br>
                        <strong>Ends:</strong> {{ $oneOffEvent->end_time->format('F j, Y g:i A') }}
                    </div>
                
                    @auth
                        @if ($checkIn)
                            <div class="text-green-700 dark:text-green-300 font-semibold mb-4">
                                ✅ You checked in at {{ $checkIn->checked_in_at->format('F j, Y g:i A') }}.
                            </div>
                        @else
                            <form method="POST" action="{{ route('one-off-events.check-in', $oneOffEvent) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    ✅ Check In Now
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="mt-4 text-gray-600 dark:text-gray-400">
                            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Log in</a> to check in for this event.
                        </div>
                    @endauth
                
                    <div class="mt-6">
                        <a href="{{ route('one-off-events.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            ← Back to Events
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
