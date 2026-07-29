<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('RSVPs for ') . $oneOffEvent->name }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('simple-volunteer-events.edit', $oneOffEvent) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                <x-heroicon-m-pencil class="w-4 inline"/> Edit Event
            </a>
            <a href="{{ route('simple-volunteer-events.show', $oneOffEvent) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Event
            </a>
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Event Summary --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $oneOffEvent->name }}</h1>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p><strong>Date:</strong> {{ $oneOffEvent->start_time->format('F j, Y') }}</p>
                        <p><strong>Time:</strong> {{ $oneOffEvent->start_time->format('g:i A') }}
                            @if($oneOffEvent->end_time)
                                - {{ $oneOffEvent->end_time->format('g:i A') }}
                            @else
                                (ongoing)
                            @endif
                        </p>
                        @if($oneOffEvent->location)
                            <p><strong>Location:</strong>
                                @if($oneOffEvent->url)
                                    <a href="{{ $oneOffEvent->url }}" target="_blank" rel="noopener noreferrer" class="text-brand-green hover:underline">{{ $oneOffEvent->location }}</a>
                                @else
                                    {{ $oneOffEvent->location }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Statistics --}}
                <div class="grid grid-cols-1 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="text-sm font-medium text-blue-700 dark:text-blue-300">Total RSVPs</div>
                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $rsvps->count() }}</div>
                    </div>
                </div>

                {{-- RSVPs Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">RSVP List</h2>
                    </div>

                    @if($rsvps->isEmpty())
                        <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-hand-raised class="w-12 h-12 mx-auto mb-4 opacity-50"/>
                            <p class="text-lg font-medium">No RSVPs yet</p>
                            <p class="text-sm">Volunteers can RSVP any time before the event.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Volunteer
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            RSVP'd At
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($rsvps as $rsvp)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            <a href="{{ route('users.show', $rsvp->user) }}" class="hover:underline text-blue-600 dark:text-blue-400">
                                                                {{ $rsvp->user->name }}
                                                            </a>
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $rsvp->user->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $rsvp->created_at->format('M j, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $rsvp->created_at->format('g:i A') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form method="POST" action="{{ route('simple-volunteer-events.rsvps.destroy', [$oneOffEvent, $rsvp]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-red-600 hover:text-red-900 dark:hover:text-red-400"
                                                            onclick="return confirm('Remove {{ $rsvp->user->name }}\'s RSVP?')">
                                                        <x-heroicon-m-x-circle class="w-5 h-5 inline"/> Remove
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
