<x-app-layout>
    @section('title', 'Dashboard')
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>
    
    <x-slot name="actions">
        <a href="{{ route('users.show', Auth::user()->id) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-user class="w-4 inline" /> View Your Profile
        </a>
        @if (Auth::user()->isStaff)
        <a href="{{ route('hours.create', Auth::user()->id) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-clock class="w-4 inline" /> Log New Hours
        </a>
        @endif
    </x-slot>

    <x-slot name="postHeader">
        <div>
            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Your Hours This Year</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                        {{ format_hours(Auth::user()->totalHoursForCurrentFiscalLedger()) }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Your Lifetime Hours</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                        {{ floor(Auth::user()->totalVolunteerHours()) }}</dd>
                </div>
                @if (Auth::user()->isStaff)
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Your Department(s)</dt>
                    @if (Auth::user()->hasDept())
                        {{-- <dd class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{Auth::user()->department->name ?? 'NO_DEPARTMENT'}} for {{Auth::user()->sector->name ?? 'NO_SECTOR'}}</dd> --}}
                        <dd class="mt-1 text-2xl tracking-tight text-gray-900">
                            @foreach (Auth::user()->departments as $department)
                                <span class="font-semibold">{{ $department->name }}</span> for <span
                                    class="font-semibold">{{ $department->sector->name }}</span>
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </dd>
                    @else
                        <dd class="mt-1 text-xl font-semibold tracking-tight text-gray-300">No Department Assigned</dd>
                    @endif
                </div>
                @else
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Your Volunteer Code</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                        {{Auth::user()->vol_code}}</dd>
                </div>
                @endif
            </dl>

            @if($activeElections->count() > 0)
            <div class="mt-5">
                <div class="overflow-hidden rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 px-4 py-5 shadow-lg sm:p-6">
                    <h3 class="text-xl font-bold mb-3 text-blue-800 flex items-center">
                        <x-heroicon-o-check-badge class="w-6 h-6 mr-2" />
                        Active Elections ({{ $activeElections->count() }})
                    </h3>
                    <div class="space-y-4">
                        @foreach($activeElections as $election)
                        <div class="bg-white p-4 rounded-lg border border-blue-100">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-lg text-gray-900">{{ $election->name }}</h4>
                                    <p class="text-gray-600 mt-1">{{ $election->description }}</p>
                                    
                                    @php
                                        $now = now();
                                        $isNominationPeriod = $election->nomination_start_date && $now >= $election->nomination_start_date && $now <= $election->nomination_end_date;
                                        $isVotingPeriod = $now >= $election->start_date && $now <= $election->end_date;
                                    @endphp
                                    
                                    <div class="mt-2 flex items-center space-x-4 text-sm">
                                        @if($isNominationPeriod)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Nomination Period
                                            </span>
                                            <span class="text-gray-500">
                                                Ends {{ $election->nomination_end_date->format('M j, Y g:i A') }}
                                            </span>
                                        @elseif($isVotingPeriod)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Voting Open
                                            </span>
                                            <span class="text-gray-500">
                                                Ends {{ $election->end_date->format('M j, Y g:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <a href="{{ route('elections.show', $election) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        @if($isNominationPeriod)
                                            View Nominations
                                        @elseif($isVotingPeriod)
                                            Vote Now
                                        @else
                                            View Election
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="text-xl font-bold mb-3 text-gray-500">Upcoming Volunteer Events
                        ({{ $upcomingEvents->count() }})</dt>
                    <dd class="mt-1 tracking-tight text-gray-900">
                        <ul class="mb-6 list-disc">
                            @forelse($upcomingEvents as $event)
                                <li class="ml-6"><a href="{{ route('volunteer.events.show', $event) }}"
                                        class="text-blue-600 hover:underline mt-2 inline-block">{{ $event->name }}</a>
                                    — {{ $event->start_date->format('M j, Y') }}</li>
                            @empty
                                <p class="text-gray-300">No upcoming events in need of volunteers.</p>
                            @endforelse
                        </ul>
                    </dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-lg sm:p-6">
                    <dt class="text-xl font-bold mb-3 text-gray-500">Your Upcoming Volunteer Slots</dt>
                    <dd class="mt-1 tracking-tight text-gray-900">
                        @forelse($upcomingShifts as $eventName => $shifts)
                            <div class="mb-4">
                                <h3 class="font-semibold text-lg">{{ $eventName }}</h3>
                                <ul class="pl-4 list-disc text-sm">
                                    <!-- Limit to first 3 shifts -->
                                    @foreach ($shifts->take(5) as $shift)
                                        <li>{{ $shift->name }} - {{ $shift->start_time->diffForHumans() }} ({{ $shift->start_time->format('M j, g:i A') }})</li>
                                    @endforeach
                                    @if ($shifts->count() > 5)
                                        <li class="text-gray-500 italic">and {{ $shifts->count() - 5 }} more...</li>
                                    @endif
                                </ul>
                            </div>
                        @empty
                            <p class="text-gray-300">No upcoming volunteer slots.</p>
                        @endforelse

                        <div class="mt-4">
                            <a href="{{ route('volunteer.events.index') }}" class="text-sm text-blue-600 hover:underline">View Volunteer Opportunities</a>
                            <a href="{{ route('volunteer.events.my-shifts-all') }}" class="ml-4 text-sm text-blue-600 hover:underline">View Full Itinerary</a>
                        </div>
                    </dd>
                </div>
            </dl>
        </div>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- {{ __("You're logged in!") }} --}}
        </div>
    </div>

    {{-- <x-slot name="right">
        <p class="py-4">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Dicta quasi aperiam facere! Blanditiis accusamus minima totam omnis qui eos alias quod, obcaecati in? Necessitatibus iure blanditiis soluta neque? Veritatis, fugit!</p>
        <p class="py-4">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Fuga iure maxime, temporibus rerum odio at omnis deserunt eos ea dolores neque atque debitis natus iste laborum quod, autem voluptas consequatur?</p>
    </x-slot> --}}

</x-app-layout>
