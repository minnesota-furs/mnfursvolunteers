<x-app-layout>
    @section('title', 'Manage Events')
    <x-slot name="header">
        {{ __('Manage Events') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.events.create')}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Create New Event
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                {{-- <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Events</h1>
                    </div>
                </div> --}}
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $evemts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 w-32 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Visibility</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 w-16 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-16">
                                            Shifts</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Start Date</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">End Date
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($events as $event)
                                    <tr>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <span class="font-extrabold" href="{{route('admin.events.edit', $event->id)}}">{{$event->name}}</span>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            @if($event->visibility === 'draft')
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-200 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                                                    <svg class="size-1.5 fill-gray-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Draft
                                                </span>
                                            @elseif($event->visibility === 'unlisted')
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-yellow-200 px-1.5 py-0.5 text-xs font-medium text-yellow-800">
                                                    <svg class="size-1.5 fill-yellow-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Unlisted
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-green-200 px-1.5 py-0.5 text-xs font-medium text-green-800">
                                                    <svg class="size-1.5 fill-green-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Public
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-1 pr-3 text-sm sm:pl-0">
                                            {{ $event->shifts()->count() }}
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
                                            <a href="{{ route('admin.events.edit', $event) }}" class="text-blue-600 px-2"><x-heroicon-s-pencil class="w-3 inline"/> Edit</a>
                                            <a href="{{ route('admin.events.volunteers', $event) }}" class="text-blue-600 px-2"><x-heroicon-m-users class="w-3 inline"/> View Volunteers</a>
                                            <a href="{{ route('admin.events.shifts.index', $event) }}" class="text-green-600 px-2"><x-heroicon-s-clock class="w-3 inline"/> Manage Shifts</a>
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

        </div>
    </div>

    {{-- <x-slot name="right">
        <p class="py-4 text-justify">Paragraph one.</p>
        <p class="py-4 text-justify">Paragraph two.</p>
    </x-slot> --}}
</x-app-layout>
