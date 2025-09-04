<x-app-layout>
    @section('title', 'Event Summary - All My Shifts')
    <x-slot name="header">
        My Volunteer Summary all slots
    </x-slot>

    <x-slot name="actions">
        @if( Auth::user()->isAdmin() )
            <a href="{{route('volunteer.events.index')}}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                View Volunteer Events
            </a>
        @endif
    </x-slot>

    <x-slot name="postHeader">
        <div>
            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Total Volunteer Slots</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $shifts->count() }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Volunteer Opportunities Left</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $shiftsRemaining }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6" title="Total hours across all shifts, double hours counted as double">
                    <dt class="truncate text-sm font-medium text-gray-500">Total Volunteer Net Hours</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $totalVolunteerHours }} hours</dd>
                    @if($totalVolunteerHours >= 10)
                        <p class="text-sm text-red-500 font-bold">Remember to take breaks!</p>
                    @endif
                </div>
            </dl>
        </div>
    </x-slot>   

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <section class="">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Upcoming Volunteer Slots</h2>
                <ol class="mt-2 divide-y divide-gray-200 text-sm/6 text-gray-500 dark:divide-white/10 dark:text-gray-400">
                    @forelse($futureShifts as $shift)
                    <li class="py-4 sm:flex justify-between items-center">
                        <time datetime="{{ $shift->start_time }}" class="w-28 flex-none">{{ $shift->start_time->format('D, M j') }}</time>
                        <div class="w-full">
                            <p class="mt-2 flex-auto font-semibold text-gray-900 sm:mt-0 dark:text-white">{{ $shift->name }}</p>
                            <p class="text-xs italic text-gray-400"><a href="{{ route('volunteer.events.show', $shift->event) }}" class="hover:underline">{{ $shift->event->name }}</a></p>
                            <p class="text-xs">{{ $shift->description }}</p>
                            {{-- <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                    <button type="submit" 
                                    class="text-red-600 hover:underline inline-block text-xs"
                                    onclick="return confirm('Are you sure you want to cancel your signup for {{$shift->name}}?')">Drop Commitment</button>
                            </form> --}}
                        </div>
                        <p class="flex-none sm:ml-6">
                            {{$shift->start_time->diffForHumans()}} •
                            <time datetime="{{ $shift->start_time }}">{{ $shift->start_time->format('g:i A') }}</time> - <time datetime="{{ $shift->end_time }}">{{ $shift->end_time->format('g:i A') }}</time> • 
                            {{-- {{ $shift->durationInHours() }} {{ Str::plural('hour', $shift->durationInHours()) }} --}}
                            <x-tailwind-dropdown buttonClass="dropdown-link text-blue-800" label="Manage" id="{{ $shift->id }}">
                                <div class="py-1" role="none">
                                    <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" 
                                        class="text-red-600 block px-4 py-2 text-sm text-gray-700"
                                        onclick="return confirm('Are you sure you want to cancel your signup for {{$shift->name}}?')"><x-heroicon-m-trash class="w-4 inline mb-1" /> Drop Volunteer Slot</button>
                                </form>
                            </div>
                        </x-tailwind-dropdown>
                        </p>
                    </li>
                    @empty
                    <li class="py-4">
                        <p class="mt-2 flex-auto sm:mt-0"><span class="font-semibold">No shifts signed up.</span> Why not <a href="{{ route('volunteer.events.show', $event) }}" class="text-blue-600 hover:underline">checkout whats available</a>?</p>
                    </li>
                    @endforelse
                </ol>
            </section>
        </div>
    </div>
</x-app-layout>
