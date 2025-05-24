<x-app-layout>
    @section('title', 'Events')
    <x-slot name="header">
        {{ __('Events') }}
    </x-slot>

    {{-- <x-slot name="actions">
        @if( Auth::user()->isAdmin() )
            <a href="{{route('admin.events.create')}}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-plus class="w-4 inline"/> Create New Event
            </a>
        @endif
    </x-slot> --}}

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Upcoming</h1>
                    </div>
                </div>
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $events->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-16">
                                            Slots Open</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 w-32">
                                            Start Date</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 w-32">End Date
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($upcomingEvents as $event)
                                    <tr>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a class="text-blue-700" href="{{ route('volunteer.events.show', $event) }}">{{$event->name}}</a>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            {{ $event->remaining_volunteer_spots }}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->start_date->format('M j, Y') }}</div>
                                            <div>{{ $event->start_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->end_date->format('M j, Y') ?? '-' }}</div>
                                            <div>{{ $event->end_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a href="{{ route('volunteer.events.show', $event) }}" class="text-blue-600 hover:underline mt-2 inline-block">View Shifts</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="4">
                                            <p class="">No events found</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- {{ $events->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 sm:px-6 lg:px-8 pt-12">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Past </h1>
                    </div>
                </div>
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $evemts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 w-32">
                                            Start Date</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 w-32">End Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($recentPastEvents as $event)
                                    <tr>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a class="text-blue-700" href="{{ route('volunteer.events.show', $event) }}">{{$event->name}}</a>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->start_date->format('M j, Y') }}</div>
                                            <div>{{ $event->start_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->end_date->format('M j, Y') ?? '-' }}</div>
                                            <div>{{ $event->end_date->format('g:i A') }}</div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="4">
                                            <p class="">No past events from the last 3 months</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- {{ $events->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <x-slot name="right">
        <p class="py-4 text-justify">Here you can find upcoming events that are in need of volunteers. Each event has shifts, of which you can pickup to volunteer for. When you signup for a volunteer slot, it will automatically credit those hours to your volunteer profile for you after.</p>
        <p class="py-4 text-justify">In addition to seeing upcoming events, you can also see past events from the last 3 months.</p>
    </x-slot>
</x-app-layout>
