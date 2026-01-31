<x-app-layout>
    @section('title', 'Event Agenda for ' . $event->name)
    <x-slot name="header">
        Volunteer Overview for {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.events.index')}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to All Events
        </a>
        <a href="{{ route('admin.events.allShifts.print', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-printer class="w-4 inline"/> Print Version
        </a>
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-clock class="w-4 inline"/> Manage Volunteer Slots
        </a>
    </x-slot>

    <div class="space-y-6">
        @foreach ($shifts as $date => $shifts)
            <div class="">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 pt-4">
                    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                </h3>

                <div class="mt-2 space-y-4">
                    @foreach ($shifts as $shift)
                        <div class="p-3 border border-gray-300 rounded-md bg-gray-50 dark:bg-gray-700">
                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}) - {{$shift->users->count()}} Signups
                            </div>
                            @if ($shift->users->isEmpty())
                                <p class="text-sm text-gray-500">No volunteers signed up.</p>
                            @else
                                <ul class="list-disc ml-6 text-sm text-gray-700 dark:text-gray-300">
                                    @foreach ($shift->users as $user)
                                        <li>{{ $user->name }}@if($user->pronouns) ({{ $user->pronouns }})@endif - {{ $user->email }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="px-4 sm:px-6 lg:px-8">
            {{-- <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900">Events</h1>
                </div>
            </div> --}}
            
            
            
        </div>
    </div>

    {{-- <x-slot name="right">
        <p class="py-4 text-justify">Paragraph one.</p>
        <p class="py-4 text-justify">Paragraph two.</p>
    </x-slot> --}}
    {{-- <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Public URL copied to clipboard!');
            }, function(err) {
                console.error('Failed to copy URL: ', err);
            });
        }
    </script> --}}
</x-app-layout>