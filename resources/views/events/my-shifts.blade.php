<x-app-layout>
    @section('title', 'My Assignments for ' . $event->name)
    <x-slot name="header">
        My Volunteer Summary for {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('volunteer.events.show', $event)}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            View/Manage Slots
        </a>
        <a href="{{route('volunteer.events.my-shifts-all')}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-list-bullet class="w-4 inline"/> View All
        </a>
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
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6" title="Double hours counted as double">
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
                
                @forelse($futureShifts as $shift)
                    <div class="mt-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 sm:p-6 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <!-- Date -->
                            <div class="flex flex-row sm:flex-col items-baseline sm:items-start gap-x-2 sm:w-28 sm:flex-shrink-0">
                                <time datetime="{{ $shift->start_time }}" class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $shift->start_time->format('D, M j') }}
                                </time>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $shift->start_time->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Shift Details -->
                            <div class="flex flex-col flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $shift->name }}</span>
                                    @if($shift->double_hours)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            <x-heroicon-s-star class="w-3 h-3 mr-1" />
                                            Double Hours
                                        </span>
                                    @endif
                                </div>
                                @if($shift->description)
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mb-2">{{ $shift->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 flex-wrap">
                                    <span>
                                        <x-heroicon-s-clock class="w-3 h-3 inline mr-1" />
                                        {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                                    </span>
                                    <span>{{ $shift->durationInHours() }} {{ Str::plural('hour', $shift->durationInHours()) }}</span>
                                    @if($shift->max_volunteers)
                                        <span>{{ $shift->users->count() }}/{{ $shift->max_volunteers }} volunteers</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Teammates -->
                            <div class="flex flex-col sm:w-48 sm:flex-shrink-0">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <x-heroicon-s-users class="w-3 h-3 inline mr-1" />
                                    Teammates
                                </span>
                                @php
                                    $otherVolunteers = $shift->users->where('id', '!=', auth()->id());
                                @endphp

                                @if($otherVolunteers->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($otherVolunteers->take(3) as $volunteer)
                                            <div class="flex items-center text-xs">
                                                <div class="w-5 h-5 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center text-xs font-medium text-gray-700 dark:text-gray-200 mr-2">
                                                    {{ strtoupper(substr($volunteer->displayName() ?? 'M', 0, 1)) }}
                                                </div>
                                                <a href="{{ route('users.profile.show', $volunteer) }}" class="text-gray-700 dark:text-gray-300 hover:text-brand-green hover:underline">
                                                    {{ $volunteer->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                        @if($otherVolunteers->count() > 3)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                +{{ $otherVolunteers->count() - 3 }} more
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic">
                                        Solo shift
                                    </span>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="text-sm font-medium text-right sm:flex-shrink-0">
                                <x-tailwind-dropdown buttonClass="dropdown-link text-blue-800" label="Manage" id="{{ $shift->id }}">
                                    <div class="py-1" role="none">
                                        <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 block px-4 py-2 text-sm text-gray-700"
                                                onclick="return confirm('Are you sure you want to cancel your signup for {{$shift->name}}?')">
                                                <x-heroicon-m-trash class="w-4 inline mb-1" /> Drop Volunteer Slot
                                            </button>
                                        </form>
                                    </div>
                                </x-tailwind-dropdown>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="mt-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg px-6 py-8 text-center">
                        <span class="font-semibold text-gray-900 dark:text-white">No shifts signed up.</span>
                        <div class="mt-2">
                            <a href="{{route('volunteer.events.show', $event)}}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Browse available assignments →
                            </a>
                        </div>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
