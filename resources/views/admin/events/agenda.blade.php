<x-app-layout>
    @section('title', 'Event Agenda for ' . $event->name)
    <x-slot name="header">
        Agenda for {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.events.index')}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to All Events
        </a>
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-clock class="w-4 inline"/> Manage Volunteer Slots
        </a>
    </x-slot>

    

    <div class="">
        @php
    function getShiftBgColor($name) {
        $colors = [
            'a' => 'bg-red-100 text-red-800',
            'b' => 'bg-orange-100 text-orange-800',
            'c' => 'bg-yellow-100 text-yellow-800',
            'd' => 'bg-lime-100 text-lime-800',
            'e' => 'bg-green-100 text-green-800',
            'f' => 'bg-emerald-100 text-emerald-800',
            'g' => 'bg-teal-100 text-teal-800',
            'h' => 'bg-cyan-100 text-cyan-800',
            'i' => 'bg-sky-100 text-sky-800',
            'j' => 'bg-blue-100 text-blue-800',
            'k' => 'bg-indigo-100 text-indigo-800',
            'l' => 'bg-violet-100 text-violet-800',
            'm' => 'bg-purple-100 text-purple-800',
            'n' => 'bg-fuchsia-100 text-fuchsia-800',
            'o' => 'bg-pink-100 text-pink-800',
            'p' => 'bg-rose-100 text-rose-800',
            'q' => 'bg-red-200 text-red-900',
            'r' => 'bg-orange-200 text-orange-900',
            's' => 'bg-yellow-200 text-yellow-900',
            't' => 'bg-lime-200 text-lime-900',
            'u' => 'bg-green-200 text-green-900',
            'v' => 'bg-emerald-200 text-emerald-900',
            'w' => 'bg-teal-200 text-teal-900',
            'x' => 'bg-cyan-200 text-cyan-900',
            'y' => 'bg-blue-200 text-blue-900',
            'z' => 'bg-indigo-200 text-indigo-900',
        ];

        $letter = strtolower(substr($name ?? '', 0, 1));
        return $colors[$letter] ?? 'bg-gray-100 text-gray-800';
    }
@endphp
        <div class="grid grid-cols-[100px_1fr] gap-2 relative">
            <!-- Time Column -->
            <div class="text-sm text-right pr-2">
              @for ($hour = $startHour; $hour <= $endHour; $hour++)
                <div class="h-16 border-t border-gray-200">{{ \Carbon\Carbon::createFromTime($hour)->format('g A') }}</div>
              @endfor
            </div>
        
            <!-- Calendar Grid -->
            <div class="relative border-l border-gray-200 bg-white" style="height: {{ ($endHour - $startHour + 1) * 4 }}rem;">
              @foreach ($shifts as $shift)
                @php
                  $start = $shift->start_time;
                  $end = $shift->end_time;
                  $topOffset = ($start->hour + $start->minute / 60 - $startHour) * 4; // 4rem = 1 hour
                  $height = $start->diffInMinutes($end) / 15 * 1; // 1rem = 15 minutes
                  $col = $positions[$shift->id]['column'];
                  $totalCols = $positions[$shift->id]['columns'];
                  $widthPercent = 100 / $totalCols;
                  $leftPercent = $col * $widthPercent;
                @endphp
        
                @php $colorClass = getShiftBgColor($shift->name ?? '') @endphp
                <div class="absolute rounded p-2 text-xs shadow {{ $colorClass }}"
                     style="
                       top: {{ $topOffset }}rem;
                       height: {{ $height }}rem;
                       left: {{ $leftPercent }}%;
                       width: {{ $widthPercent }}%;
                     ">
                  <div class="font-semibold">{{ $shift->name ?? 'Shift' }}</div>
                  <div class="text-indigo-600 text-[10px]">{{ $start->format('g:i A') }} – {{ $end->format('g:i A') }}</div>
                  <div class="text-[10px] text-gray-700">{{ $shift->users->pluck('name')->join(', ') ?: 'Unfilled' }}</div>
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