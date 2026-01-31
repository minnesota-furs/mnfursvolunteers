<x-appPrint-layout>
    @section('title', 'Print Layout for ' . $event->name)
    <x-slot name="header">
        Volunteer Overview for {{ $event->name }}
    </x-slot>

    <div class="">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Event Details</h3>
        <div class="mt-2 space-y-4">
            <div class="p-3">
                <div class="font-semibold text-gray-800 dark:text-gray-200">
                    {{ $event->name }} ({{ $event->start_date?->format('l g:i A') }} - {{ $event->end_date?->format('l g:i A') }})
                </div>
                <p class="text-sm text-gray-500"><x-heroicon-o-map-pin class="w-4 inline"/> Location: {{ $event->location }}</p>
                <p class="text-sm text-gray-500"><x-heroicon-o-clock class="w-4 inline"/> Hour Logging: {{ $event->auto_credit_hours ? 'Automatic Logging' : 'Manual Approval Logging' }}</p>
                <p class="text-sm text-gray-500"><x-heroicon-o-user class="w-4 inline"/> Total Shifts: {{$event->shifts()->count()}}</p>
            </div>
            <div class="space-y-8">
                @foreach ($shifts as $date => $shifts)
                <div class="">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </h2>

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
        </div>
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