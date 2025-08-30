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
        <a href="{{ route('hours.create', Auth::user()->id) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-clock class="w-4 inline" /> Log New Hours
        </a>
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
            </dl>

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
                    <dt class="text-xl font-bold mb-3 text-gray-500">Your Upcoming Volunteering
                        ({{ $upcomingShifts->count() }})</dt>
                    <dd class="mt-1 tracking-tight text-gray-900">
                        @forelse($upcomingShifts as $eventName => $shifts)
                            <div class="mb-4">
                                <h3 class="font-semibold text-lg">{{ $eventName }}</h3>
                                <ul class="pl-4 list-disc text-sm">
                                    @foreach ($shifts as $shift)
                                        <li>{{ $shift->name }} — {{ $shift->start_time->format('M j, g:i A') }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-gray-300">No upcoming volunteer slots.</p>
                        @endforelse

                        <ul class="mb-6 list-disc">
                            @foreach ($upcomingShifts as $shift)
                                {{-- <li class="ml-6">{{ $shift->name }} ({{ $shift->start_time->format('M j, g:i A') }})</li> --}}
                            @endforeach
                        </ul>
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
