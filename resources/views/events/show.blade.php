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
            <h2 class="text-xl font-semibold mt-8">Your Volunteer Slots</h2>
            <p class="text-sm text-gray-700 mb-3">You picked up some volunteer slots! Thanks for your help! 
                @if($event->auto_credit_hours)
                Your volunteer hours will credit automatically after the event.
                @else 
                Your volunteer hours will credit after the event.
                @endif</p>
            @foreach ($userShifts as $s)
                <li class="ml-6">
                    @if ($event->isMultiDay())
                        <strong>{{ $s->name }}</strong> — {{ $s->start_time->format('l g:i A') }} to
                        {{ $s->end_time->format('g:i A') }}
                    @else
                        <strong>{{ $s->name }}</strong> — {{ $s->start_time->format('g:i A') }} to
                        {{ $s->end_time->format('g:i A') }}
                    @endif
                    @if(\Carbon\Carbon::parse($s->start_time)->isPast())
                        <span class="text-sm text-red-600">This slot has past and cannot be cancelled.</span>
                    @else
                    <form action="{{ route('shifts.cancel', $s) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline inline-block" onclick="return confirm('Are you sure you want to cancel your signup for {{$s->name}}?')">Cancel</button>
                    </form>
                    @endif
                </li>
            @endforeach
        @endif

        <h2 class="text-xl font-semibold mt-8 mb-3">Openings</h2>
        <ul role="list" class="divide-y divide-gray-100">
            @forelse ($shifts as $shift)
            @php
                $openings = $shift->max_volunteers - $shift->users->count();
                $isFull = $openings <= 0;
                $signedUp = $shift->users->contains(auth()->id());
            @endphp
                <li class="flex items-center justify-between gap-x-6 py-5 pl-4 {{ $signedUp ? '' : '' }}">
                    <div class="min-w-0">
                        <div class="flex items-start gap-x-3">
                            <p class="text-sm/6 font-semibold {{ $isFull ? 'text-gray-400' : 'text-gray-900' }}">
                                @if($isFull)
                                    <x-heroicon-o-check class="w-4 mb-1 inline text-gray-400"/>
                                @else
                                    <x-heroicon-s-users class="w-4 mb-1 inline"/>
                                @endif
                                {{ $shift->name }}
                                @if($signedUp)
                                    <span class="text-sm/6 font-bold text-blue-500"> (You're Assigned)</span>
                                @endif
                            </p>
                            {{-- <p
                                class="mt-0.5 whitespace-nowrap rounded-md bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                Complete</p> --}}
                        </div>
                        <div class="mt-1 flex items-center gap-x-2 text-xs/5 text-gray-500">
                            <p class="whitespace-nowrap">
                            @if($event->isMultiDay())
                                {{ $shift->start_time->format('l @ g:i A') }} — {{ $shift->end_time->format('l \@ g:i A') }}
                            @else
                                {{ $shift->start_time->format('g:i A') }} — {{ $shift->end_time->format('g:i A') }}
                            @endif
                            </p>
                            <svg viewBox="0 0 2 2" class="size-0.5 fill-current">
                                <circle cx="1" cy="1" r="1" />
                            </svg>
                            <p class="visible sm:invisible" title="{{$shift->users->pluck('name')->join(', ')}}">Signups: {{$shift->users->count()}} of {{ $shift->max_volunteers }}</p>
                            @if($shift->double_hours)
                                <svg viewBox="0 0 2 2" class="size-0.5 fill-current">
                                    <circle cx="1" cy="1" r="1" />
                                </svg>
                                <p class="font-bold"><x-heroicon-m-star class="w-3 mb-1 inline"/> Double Hours</p>
                            @endif
                        </div>
                        <div class="mt-1 flex items-center gap-x-2 text-xs/5 {{ $isFull ? 'text-gray-400' : 'text-gray-500' }}">
                            <p class="">{{ $shift->description ?? 'No description was provided for this slot/task.' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-none sm:flex-row items-center gap-x-4">
                        @if(\Carbon\Carbon::parse($shift->start_time)->isPast())
                            <span class="text-sm text-gray-400">This slot has past, and cannot be changed.</span>
                        @else
                            @if ($signedUp)
                                    <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                        class="rounded-md bg-red-500 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-600 sm:block"
                                        onclick="return confirm('Are you sure you want to cancel your signup for {{$shift->name}}?')">Cancel Signup</button>
                                    </form>
                            @elseif($shift->users->count() < $shift->max_volunteers)
                                @if (!$event->signup_open_date || $event->signup_open_date->isPast())
                                    <form action="{{ route('shifts.signup', $shift) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block"
                                            onclick="return confirm('Are you sure you want to pickup the volunteer slot for {{$shift->name}}?')">
                                            Sign Up
                                        </button>
                                    </form>
                                @else
                                    <div class="text-gray-500 text-sm">
                                        Signups open <span
                                            title="{{ $event->signup_open_date->format('F j, Y g:i A') }}">{{ $event->signup_open_date->diffForHumans() }}</span>
                                    </div>
                                @endif
                            @else
                                <span class="text-sm text-red-600">This slot is full.</span>
                            @endif
                        @endif
                    </div>
                </li>
            @empty
            <li class="flex items-center justify-between gap-x-6 py-5 pl-4">
                <div class="min-w-0">
                    <div class="flex items-start gap-x-3">
                        <p class="text-sm/6 text-gray-500">
                            No openings are currently available.
                        </p>
                    </div>
                </div>
            </li>

            @endforelse
        </ul>

    </div>
</x-app-layout>
