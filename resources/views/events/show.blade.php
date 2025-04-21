<x-app-layout>
    <x-slot name="header">
        {{ $event->name }} - {{ $event->start_date->format('M j') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('volunteer.events.index') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
    </x-slot>

    <div class="">
        <p class="text-sm text-gray-700 mb-1">{{ $event->start_date->format('M j, Y \@ g:i A') }} — {{ $event->end_date->format('M j, Y \@ g:i A') }}</p>
    <p class="text-gray-600 mb-4">{{ $event->description ?? 'No description...' }}</p>

    <h2 class="text-xl font-semibold mt-8 mb-3">Available Shifts</h2>

    @if($shifts->isEmpty())
        <p class="text-gray-600">No shifts available for this event.</p>
    @else
        <div class="space-y-4">
            @foreach($shifts as $shift)
                <div class="border p-4 rounded shadow-sm">
                    <h2 class="text-lg font-semibold">{{ $shift->name }}</h2>
                    <p class="text-sm text-gray-700">{{ $shift->start_time->format('M j, Y \@ g:i A') }} — {{ $shift->end_time->format('M j, Y \@ g:i A') }}</p>
                    <p class="text-sm text-gray-600 mb-2">Max Volunteers: {{ $shift->max_volunteers }}</p>
                    <p class="text-gray-600">{{ $shift->description }}</p>

                    @php
                        $signedUp = $shift->users->contains(auth()->id());
                    @endphp

                    <div class="mt-3">
                        @if($signedUp)
                            <form action="{{ route('shifts.cancel', $shift) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Cancel Signup</button>
                            </form>
                        @elseif($shift->users->count() < $shift->max_volunteers)
                            <form action="{{ route('shifts.signup', $shift) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">Sign Up</button>
                            </form>
                        @else
                            <span class="text-sm text-red-600">This shift is full.</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    </div>
</x-app-layout>
