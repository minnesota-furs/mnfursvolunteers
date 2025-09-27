<x-app-layout>
    @section('title', 'Event - ' . $event->name)
    <x-slot name="header">
        {{ $event->name }} ({{ $event->start_date->format('F j') }})
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('volunteer.events.index')}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
        <a href="{{ route('volunteer.events.my-shifts', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            My Shift Summary
        </a>
    </x-slot>

    <div class="">
        {{-- <p class="text-sm text-gray-700 mb-1">{{ $event->start_date->format('M j, Y \@ g:i A') }} — {{ $event->end_date->format('M j, Y \@ g:i A') }}</p> --}}
        <p class="text-gray-600 mb-4">{{ $event->description ?? 'No description...' }}</p>

        @if ($userShifts->isNotEmpty())
            <h2 class="text-xl font-semibold mt-8 mb-3">Your Volunteer Slots</h2>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800 dark:text-blue-200 mb-4">
                    <x-heroicon-s-check-circle class="w-5 h-5 inline-block mr-1"/>
                    You picked up some volunteer slots! Thanks for your help! 
                    @if($event->auto_credit_hours)
                    Your volunteer hours will credit automatically after the event.
                    @else 
                    Your volunteer hours will credit after the event.
                    @endif
                </p>
                <div class="space-y-3">
                    @foreach ($userShifts as $s)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-blue-200 dark:border-blue-700">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $s->name }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        @if ($event->isMultiDay())
                                            {{ $s->start_time->format('l g:i A') }} to {{ $s->end_time->format('g:i A') }}
                                        @else
                                            {{ $s->start_time->format('g:i A') }} to {{ $s->end_time->format('g:i A') }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    @if(\Carbon\Carbon::parse($s->start_time)->isPast())
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                            <x-heroicon-s-clock class="w-3 h-3 mr-1"/>
                                            Past Event
                                        </span>
                                    @else
                                        <form action="{{ route('shifts.cancel', $s) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="inline-flex items-center rounded-md bg-red-100 hover:bg-red-200 px-3 py-2 text-sm font-medium text-red-700 transition-colors duration-200" 
                                                onclick="return confirm('Are you sure you want to cancel your signup for {{$s->name}}?')">
                                                <x-heroicon-s-x-mark class="w-4 h-4 mr-1"/>
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <h2 class="text-xl font-semibold mt-8 mb-3">Openings</h2>
        <div class="space-y-4">
            @forelse ($shifts as $shift)
            @php
                $openings = $shift->max_volunteers - $shift->users->count();
                $isFull = $openings <= 0;
                $signedUp = $shift->users->contains(auth()->id());
            @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border {{ $signedUp ? 'border-blue-200 dark:border-blue-700 bg-blue-50/50 dark:bg-blue-900/10' : 'border-gray-200 dark:border-gray-700' }} p-4 sm:p-6">
                    <!-- Header with title and status -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                @if($isFull)
                                    <x-heroicon-o-check class="w-5 h-5 text-gray-400 flex-shrink-0"/>
                                @else
                                    <x-heroicon-s-users class="w-5 h-5 text-gray-600 dark:text-gray-400 flex-shrink-0"/>
                                @endif
                                <h3 class="text-lg font-semibold {{ $isFull ? 'text-gray-400' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $shift->name }}
                                </h3>
                                @if($signedUp)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:text-blue-200">
                                        <x-heroicon-s-check class="w-3 h-3 mr-1"/>
                                        You're Assigned
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Time and signup info -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-s-clock class="w-4 h-4"/>
                                    <span>
                                        @if($event->isMultiDay())
                                            {{ $shift->start_time->format('l @ g:i A') }} — {{ $shift->end_time->format('l @ g:i A') }}
                                        @else
                                            {{ $shift->start_time->format('g:i A') }} — {{ $shift->end_time->format('g:i A') }}
                                        @endif
                                    </span>
                                </div>
                                
                                <span class="hidden sm:inline text-gray-300 dark:text-gray-600">•</span>
                                
                                <div class="flex items-center gap-1" title="{{$shift->users->pluck('name')->join(', ')}}">
                                    <x-heroicon-s-user-group class="w-4 h-4"/>
                                    <span>{{$shift->users->count()}} of {{ $shift->max_volunteers }} spots filled</span>
                                </div>
                                
                                @if($shift->double_hours)
                                    <span class="hidden sm:inline text-gray-300 dark:text-gray-600">•</span>
                                    <div class="flex items-center gap-1 font-medium text-yellow-600 dark:text-yellow-500">
                                        <x-heroicon-m-star class="w-4 h-4"/>
                                        <span>Double Hours</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Action buttons for desktop -->
                        <div class="hidden sm:flex flex-shrink-0">
                            @if(\Carbon\Carbon::parse($shift->start_time)->isPast())
                                <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-s-clock class="w-4 h-4 mr-1"/>
                                    Past Event
                                </span>
                            @else
                                @if ($signedUp)
                                    <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="inline-flex items-center rounded-md bg-red-600 hover:bg-red-700 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to cancel your signup for {{$shift->name}}?')">
                                            <x-heroicon-s-x-mark class="w-4 h-4 mr-1"/>
                                            Cancel Signup
                                        </button>
                                    </form>
                                @elseif($shift->users->count() < $shift->max_volunteers)
                                    @if (!$event->signup_open_date || $event->signup_open_date->isPast())
                                        <form action="{{ route('shifts.signup', $shift) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md bg-brand-green hover:bg-green-700 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-200"
                                                onclick="return confirm('Are you sure you want to pickup the volunteer slot for {{$shift->name}}?')">
                                                <x-heroicon-s-plus class="w-4 h-4 mr-1"/>
                                                Sign Up
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-gray-500 dark:text-gray-400 text-sm bg-gray-100 dark:bg-gray-700 rounded-md px-3 py-2">
                                            <div class="font-medium">Signups open</div>
                                            <div title="{{ $event->signup_open_date->format('F j, Y g:i A') }}">{{ $event->signup_open_date->diffForHumans() }}</div>
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-100 dark:bg-red-900 px-3 py-2 text-sm font-medium text-red-700 dark:text-red-400">
                                        <x-heroicon-s-x-circle class="w-4 h-4 mr-1"/>
                                        Full
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Description -->
                    @if($shift->description)
                        <p class="text-sm {{ $isFull ? 'text-gray-400' : 'text-gray-600 dark:text-gray-300' }} mb-4">
                            {{ $shift->description }}
                        </p>
                    @endif
                    
                    <!-- Action buttons for mobile -->
                    <div class="sm:hidden">
                        @if(\Carbon\Carbon::parse($shift->start_time)->isPast())
                            <div class="w-full text-center py-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-md">
                                <x-heroicon-s-clock class="w-4 h-4 inline mr-1"/>
                                This slot has past and cannot be changed
                            </div>
                        @else
                            @if ($signedUp)
                                <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="w-full inline-flex items-center justify-center rounded-md bg-red-600 hover:bg-red-700 px-4 py-3 text-sm font-medium text-white shadow-sm transition-colors duration-200"
                                        onclick="return confirm('Are you sure you want to cancel your signup for {{$shift->name}}?')">
                                        <x-heroicon-s-x-mark class="w-5 h-5 mr-2"/>
                                        Cancel Your Signup
                                    </button>
                                </form>
                            @elseif($shift->users->count() < $shift->max_volunteers)
                                @if (!$event->signup_open_date || $event->signup_open_date->isPast())
                                    <form action="{{ route('shifts.signup', $shift) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center rounded-md bg-brand-green hover:bg-green-700 px-4 py-3 text-sm font-medium text-white shadow-sm transition-colors duration-200"
                                            onclick="return confirm('Are you sure you want to pickup the volunteer slot for {{$shift->name}}?')">
                                            <x-heroicon-s-plus class="w-5 h-5 mr-2"/>
                                            Sign Up for This Slot
                                        </button>
                                    </form>
                                @else
                                    <div class="w-full text-center py-3 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-md">
                                        <div class="font-medium">Signups open {{ $event->signup_open_date->diffForHumans() }}</div>
                                        <div class="text-xs mt-1">{{ $event->signup_open_date->format('F j, Y g:i A') }}</div>
                                    </div>
                                @endif
                            @else
                                <div class="w-full text-center py-3 text-sm font-medium text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900 rounded-md">
                                    <x-heroicon-s-x-circle class="w-5 h-5 inline mr-1"/>
                                    This slot is full
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
                    <x-heroicon-o-calendar-days class="w-12 h-12 text-gray-400 mx-auto mb-3"/>
                    <p class="text-gray-500 dark:text-gray-400">
                        No openings are currently available.
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
