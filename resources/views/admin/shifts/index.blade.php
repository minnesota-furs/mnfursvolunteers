<x-app-layout>
    @section('title', 'Manage Shifts for ' . $event->name)
    <x-slot name="header">
        Manage Shifts for {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.events.index')}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
        <x-tailwind-dropdown label="More" id=1>
            <div class="py-1" role="none">
                <x-tailwind-dropdown-item href="{{route('admin.events.edit', $event->id)}}"><x-heroicon-o-pencil class="w-4 inline"/>  Edit Event</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{route('admin.events.log', $event->id)}}" title="View Event Logs"><x-heroicon-o-list-bullet class="w-4 inline"/> View Logs</x-tailwind-dropdown-item>
            </div>
            <div class="py-1" role="none">
                <x-tailwind-dropdown-item href="{{ route('admin.events.volunteers', $event) }}" title="View all unquie volunteers signed up and email actions">View All Volunteers / Email</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{ route('admin.events.allShifts', $event) }}" title="View all the shifts and their associated volunteers">View Shift Overview</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{ route('admin.events.agenda', $event) }}" title="View the shifts in a day agenda view">View Agenda (BETA)</x-tailwind-dropdown-item>
            </div>
            @if ($event->visibility === 'public' || $event->visibility === 'unlisted' )
            <div class="py-1" role="none">
                <x-tailwind-dropdown-item href="#" title="Link to the logged in user signup sheet" onclick="copyToClipboard('{{ route('volunteer.events.show', $event) }}')">
                    <x-heroicon-s-link class="w-4 inline"/> Copy Internal Signup URL
                </x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="#" title="Link the to the public site listing for this event" onclick="copyToClipboard('{{ route('vol-listings-public.show', $event->id) }}')">
                    <x-heroicon-s-link class="w-4 inline"/> Copy Public URL
                </x-tailwind-dropdown-item>
            </div>
            @else
            <div class="py-1" role="none">
                <x-tailwind-dropdown-item class="opacity-20 cursor-not-allowed" title="Link to the logged in user signup sheet">
                    <x-heroicon-s-link class="w-4 inline"/> Copy Internal Signup URL
                </x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item class="opacity-20 cursor-not-allowed">
                    <x-heroicon-s-link class="w-4 inline"/> Copy Public URL ({{ucfirst($event->visibility)}})
                </x-tailwind-dropdown-item>
            </div>
            @endif
            {{-- <div class="py-1" role="none">
                <x-tailwind-dropdown-item title="Delete" href="#" class="hover:bg-red-50 text-red-900" />
            </div> --}}
        </x-tailwind-dropdown>
        @can('manageEditors', $event)
            <a href="{{ route('admin.events.editors', $event) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-o-user-group class="w-4 inline"/> Manage Collaborators
            </a>
        @endcan
        <a href="{{ route('admin.events.shifts.create', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Create New Shift
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
                    <div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $shifts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Start Time</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            End Time</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Volunteers
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($shifts as $shift)
                                    @php
                                        $signupCount = $shift->users->count();
                                        $textClass = $signupCount >= $shift->max_volunteers ? 'text-green-800 dark:text-green-500 text-weight-800' : ($signupCount > 0 ? 'text-purple-700 dark:text-purple-400' : '');
                                    @endphp
                                    <tr class="">
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a class="text-blue-700 dark:text-blue-200" href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}">
                                                {{$shift->name}}
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            @if($event->isMultiDay())
                                                {{ $shift->start_time->format('l \@ g:i A') }}
                                            @else
                                                {{ $shift->start_time->format('g:i A') }}
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            @if($event->isMultiDay())
                                                {{ $shift->end_time->format('l \@ g:i A') }}
                                            @else
                                                {{ $shift->end_time->format('g:i A') }}
                                            @endif
                                            @if($shift->double_hours)
                                                <x-heroicon-m-star title="Double Hours" class="w-3 mb-1 inline"/>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0 {{ $textClass }}">
                                            @if($signupCount >= $shift->max_volunteers)
                                                <x-heroicon-s-battery-100 title="Fully Staffed" class="w-4 mb-1 inline"/>
                                            @elseif($signupCount > 0)
                                                <x-heroicon-s-battery-50 title="Partially Staffed" class="w-4 mb-1 inline"/>
                                            @else
                                                <x-heroicon-s-battery-0 title="No Staff" class="w-4 mb-1 inline"/>
                                            @endif
                                            {{ $shift->users->count() }} of {{ $shift->max_volunteers }}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            {{-- <a href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}" class="text-blue-600 dark:text-blue-200 px-2"><x-heroicon-m-plus class="w-3 inline"/> Signup</a> --}}
                                            <a href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}" class="text-blue-600 dark:text-blue-200 px-2"><x-heroicon-m-pencil class="w-3 inline"/> Edit</a>
                                            <form action="{{ route('admin.events.shifts.duplicate', [$event, $shift]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-yellow-700 dark:text-yellow-200 ml-2 hover:underline"><x-heroicon-m-document-duplicate class="w-3 inline"/> Duplicate</button>
                                            </form>
                                            <form action="{{ route('admin.events.shifts.destroy', [$event, $shift]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 ml-2" onclick="return confirm('Are you sure you want to delete slot {{$shift->name}} on {{$shift->start_time->format('l \@ g:i A')}}?\n\nThis cannot be undone!')"><x-heroicon-m-trash class="w-3 inline"/> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="4">
                                            <p class="font-semibold">No shifts created.</p>
                                            <p class="">People cannot signup for shifts until they are created.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- {{ $shifts->links() }} --}}
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
    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Public URL copied to clipboard!');
            }, function(err) {
                console.error('Failed to copy URL: ', err);
            });
        }
    </script>
</x-app-layout>
