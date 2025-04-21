<x-app-layout>
    <x-slot name="header">
        Manage Shifts for {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        @if( Auth::user()->isAdmin() )
            <a href="{{ route('admin.events.shifts.create', $event) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-plus class="w-4 inline"/> Create New Shift
            </a>
        @endif
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
                            {{-- {{ $shifts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">
                                            Start Time</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">
                                            End Time</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">
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
                                        $textClass = $signupCount >= $shift->max_volunteers ? 'text-green-800 text-weight-800' : ($signupCount > 0 ? 'text-purple-700' : '');
                                    @endphp
                                    <tr class="">
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a class="text-blue-700" href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}">
                                                {{$shift->name}}</a>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            {{ $shift->start_time->format('M j, Y \@ g:i A') }}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            {{ $shift->end_time->format('M j, Y \@ g:i A') }}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0 {{ $textClass }}">
                                            {{ $shift->users->count() }} of {{ $shift->max_volunteers }}
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <a href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}" class="text-blue-600 px-2">Edit</a>
                                            <form action="{{ route('admin.events.shifts.duplicate', [$event, $shift]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-yellow-700 ml-2 hover:underline">Duplicate</button>
                                            </form>
                                            <form action="{{ route('admin.events.shifts.destroy', [$event, $shift]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 ml-2" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="4">
                                            <p class="">No shifts created yet.</p>
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
</x-app-layout>
