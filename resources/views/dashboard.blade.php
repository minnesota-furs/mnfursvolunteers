<x-app-layout>
    @section('title', 'Dashboard')
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>
    
    <x-slot name="actions">
        <a href="{{ route('users.show', Auth::user()->id) }}"
            class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-user class="w-4 inline" /> View Your Profile
        </a>
        @if (Auth::user()->isStaff)
        <a href="{{ route('hours.create', Auth::user()->id) }}"
            class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-clock class="w-4 inline" /> Log New Hours
        </a>
        @endif
    </x-slot>

    <x-slot name="postHeader">
        <div>
            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Hours This Year</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        {{ format_hours(Auth::user()->totalHoursForCurrentFiscalLedger()) }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Lifetime Hours</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        {{ floor(Auth::user()->totalVolunteerHours()) }}</dd>
                </div>
                @if (Auth::user()->isStaff)
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Department(s)</dt>
                    @if (Auth::user()->hasDept())
                        {{-- <dd class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{Auth::user()->department->name ?? 'NO_DEPARTMENT'}} for {{Auth::user()->sector->name ?? 'NO_SECTOR'}}</dd> --}}
                        <dd class="mt-1 text-2xl tracking-tight text-gray-900 dark:text-gray-100">
                            @foreach (Auth::user()->departments as $department)
                                <span class="font-semibold">{{ $department->name }}</span> for <span
                                    class="font-semibold">{{ $department->sector->name }}</span>
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </dd>
                    @else
                        <dd class="mt-1 text-xl font-semibold tracking-tight text-gray-300 dark:text-gray-500">No Department Assigned</dd>
                    @endif
                </div>
                @else
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Your Volunteer Code</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                        {{Auth::user()->vol_code}}</dd>
                </div>
                @endif
            </dl>

            <x-profile-completion-notice />

            <x-no-show-warning :recentNoShows="$recentNoShows" />

            <x-elections-dashboard-notice :activeElections="$activeElections" />

            <x-applications-dashboard-notice 
                :unclaimedPendingCount="$unclaimedPendingCount" 
                :claimedApplications="$claimedApplications" 
            />

            <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                    <dt class="text-xl font-bold mb-1 text-gray-500 dark:text-gray-400">Upcoming Volunteer Events
                        ({{ $upcomingEvents->count() }})</dt>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-3">These events and departments are looking for volunteers!</p>
                    <dd class="mt-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            @forelse($upcomingEvents as $event)
                                <a href="{{ route('volunteer.events.show', $event) }}"
                                   class="group flex items-center gap-2.5 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-2.5 py-1.5 hover:border-brand-green dark:hover:border-brand-green hover:bg-green-50 dark:hover:bg-brand-green/10 transition-all">
                                    <div class="flex-shrink-0 text-center leading-tight rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 px-1.5 py-0.5 shadow-sm">
                                        <div class="text-[10px] font-semibold uppercase text-brand-green">{{ $event->start_date->format('M') }}</div>
                                        <div class="text-base font-bold text-gray-900 dark:text-gray-100 -mt-0.5">{{ $event->start_date->format('j') }}</div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-brand-green transition-colors truncate">{{ $event->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            @if($event->isMultiDay())
                                                {{ $event->start_date->format('M j') }} – {{ $event->end_date->format('M j, Y') }}
                                            @else
                                                {{ $event->start_date->format('l, g:i A') }}
                                            @endif
                                            @if($event->location)
                                                · {{ $event->location }}
                                            @endif
                                        </p>
                                    </div>
                                    <x-heroicon-m-chevron-right class="w-4 h-4 flex-shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-brand-green transition-colors"/>
                                </a>
                            @empty
                                <p class="text-gray-300 dark:text-gray-500">No upcoming events in need of volunteers.</p>
                            @endforelse
                        </div>
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
