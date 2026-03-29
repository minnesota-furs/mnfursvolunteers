<x-app-layout>
    @section('title', 'Manage Events')
    <x-slot name="header">
        {{ __('Manage Events') }}
    </x-slot>

    <x-slot name="actions">
        @can('manage-volunteer-events')
        <a href="{{route('admin.manager-dashboard')}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-signal class="w-4 inline"/> Manager Overview
        </a>
        @endcan
        <a href="{{route('admin.events.create')}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Create New Event
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                <!-- Filter Checkboxes -->
                <div class="mb-4">
                    <form method="GET" action="{{ route('admin.events.index') }}" class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="show_past" 
                                   name="show_past" 
                                   value="1"
                                   {{ $showPast ? 'checked' : '' }}
                                   onchange="this.form.submit()"
                                   class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                            <label for="show_past" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Show past events
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="show_mine" 
                                   name="show_mine" 
                                   value="1"
                                   {{ $showMine ? 'checked' : '' }}
                                   onchange="this.form.submit()"
                                   class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                            <label for="show_mine" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Show mine only
                            </label>
                        </div>
                    </form>
                </div>
                {{-- <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Events</h1>
                    </div>
                </div> --}}
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $evemts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="hidden sm:table-cell py-3.5 pl-4 pr-3 w-32 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Visibility</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 w-16 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            Shifts</th>
                                        <th scope="col"
                                            class="hidden sm:table-cell px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Start Date</th>
                                        <th scope="col"
                                            class="hidden sm:table-cell px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">End Date
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-2 sm:pr-0 w-12">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($events as $event)
                                    <tr>
                                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-0">
                                            <span class="font-extrabold {{ $event->hasPast() ? 'text-gray-400' : '' }}">
                                                {{$event->name}}
                                                @if($event->requiredTags->isNotEmpty())
                                                    <x-heroicon-s-tag class="w-4 h-4 inline text-red-500" title="Has tag requirements ({{ $event->requiredTags->count() }} tag{{ $event->requiredTags->count() > 1 ? 's' : '' }})"/>
                                                @endif
                                                @if($event->requiredDepartments->isNotEmpty())
                                                    <x-heroicon-s-building-office class="w-4 h-4 inline text-amber-500" title="Department limited ({{ $event->requiredDepartments->pluck('name')->join(', ') }})"/>
                                                @endif
                                                @if ($event->hasPast())
                                                    <span>(Past Event)</span>
                                                @endif
                                            </span>
                                            <div class="mt-1 flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                                <x-heroicon-o-user class="w-3 h-3 flex-shrink-0"/>
                                                <span>{{ $event->creator->displayName() ?? 'Unknown' }}</span>
                                                @if($event->editors->count() > 0)
                                                    <span class="text-gray-300 dark:text-gray-600">·</span>
                                                    <span>+{{ $event->editors->count() }} editor{{ $event->editors->count() > 1 ? 's' : '' }}</span>
                                                @endif
                                            </div>
                                            {{-- Mobile: visibility badge + date range --}}
                                            <div class="sm:hidden mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                                                @php
                                                    $visBadge = match($event->visibility) {
                                                        'draft'    => ['bg-gray-200 text-gray-600',   'fill-gray-800',  'Draft'],
                                                        'unlisted' => ['bg-yellow-200 text-yellow-800','fill-yellow-800','Unlisted'],
                                                        'internal' => ['bg-blue-200 text-blue-800',    'fill-blue-800',  'Internal'],
                                                        default    => ['bg-green-200 text-green-800',  'fill-green-800', 'Public'],
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center gap-x-1 rounded-md {{ $visBadge[0] }} px-1.5 py-0.5 text-xs font-medium">
                                                    <svg class="size-1.5 {{ $visBadge[1] }}" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3"/></svg>
                                                    {{ $visBadge[2] }}
                                                </span>
                                                <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                                    <x-heroicon-m-calendar class="w-3 h-3 inline -mt-0.5"/>
                                                    {{ $event->start_date->format('M j') }} - {{ $event->end_date->format('M j, Y') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="hidden sm:table-cell whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-0">
                                            @if($event->visibility === 'draft')
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-200 px-1.5 py-0.5 text-xs font-medium text-gray-600">
                                                    <svg class="size-1.5 fill-gray-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Draft
                                                </span>
                                            @elseif($event->visibility === 'unlisted')
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-yellow-200 px-1.5 py-0.5 text-xs font-medium text-yellow-800">
                                                    <svg class="size-1.5 fill-yellow-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Unlisted
                                                </span>
                                            @elseif($event->visibility === 'internal')
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-blue-200 px-1.5 py-0.5 text-xs font-medium text-blue-800">
                                                    <svg class="size-1.5 fill-blue-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Internal
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-green-200 px-1.5 py-0.5 text-xs font-medium text-green-800">
                                                    <svg class="size-1.5 fill-green-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Public
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 pl-1 pr-3 text-sm sm:pl-0 min-w-[100px]">
                                            @php
                                                $shiftCount  = $event->shifts->count();
                                                $totalSlots  = $event->shifts->sum('max_volunteers');
                                                $filledSlots = $event->shifts->sum(fn($s) => $s->users->count());
                                                $pct         = $totalSlots > 0 ? round($filledSlots / $totalSlots * 100) : 0;
                                                $barColor    = $pct >= 100 ? 'bg-green-600' : ($pct >= 75 ? 'bg-green-900' : 'bg-orange-600');
                                            @endphp
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('update', $event))
                                                <a href="{{ route('admin.events.shifts.index', $event) }}" class="text-blue-500 font-semibold hover:underline">
                                                    {{ $shiftCount }} {{ Str::plural('shift', $shiftCount) }}
                                                </a>
                                            @else
                                                <span class="text-gray-500 font-semibold">{{ $shiftCount }} {{ Str::plural('shift', $shiftCount) }}</span>
                                            @endif
                                            @if($totalSlots > 0)
                                                <div class="mt-1.5">
                                                    <div class="flex justify-between text-xs text-gray-400 dark:text-gray-500 mb-0.5">
                                                        <span>{{ $filledSlots }}/{{ $totalSlots }} slots</span>
                                                        <span>{{ $pct }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                                        <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="hidden sm:table-cell whitespace-nowrap py-4 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->start_date->format('M j, Y') }}</div>
                                            <div>{{ $event->start_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="hidden sm:table-cell whitespace-nowrap py-4 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->end_date->format('M j, Y') ?? '-' }}</div>
                                            <div>{{ $event->end_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="py-4 pl-2 pr-2 text-sm align-middle">
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('update', $event))
                                                {{-- Mobile: single ••• dropdown --}}
                                                <div class="sm:hidden">
                                                    <x-tailwind-dropdown buttonClass="inline-flex items-center gap-1 rounded px-2 py-1 text-xs text-blue-600 dark:text-blue-200 hover:bg-blue-50 dark:hover:bg-blue-900/30" label="•••" id="{{ $event->id + 9000 }}">
                                                        <div class="py-1" role="none">
                                                            <x-tailwind-dropdown-item href="{{route('admin.events.edit', $event->id)}}" title="Edit Event Details"><x-heroicon-o-pencil class="w-4 inline"/> Edit Event</x-tailwind-dropdown-item>
                                                            <x-tailwind-dropdown-item href="{{route('admin.events.shifts.index', $event->id)}}" title="Manage Shifts"><x-heroicon-o-clock class="w-4 inline"/> Manage Shifts</x-tailwind-dropdown-item>
                                                            <button type="button"
                                                                onclick="window.dispatchEvent(new CustomEvent('open-event-duplicate-modal', { detail: { id: {{ $event->id }}, name: '{{ addslashes($event->name) }}', shiftsCount: {{ $event->shifts->count() }}, startDate: '{{ $event->start_date->toIso8601String() }}', endDate: '{{ $event->end_date->toIso8601String() }}' } }))"
                                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                                <x-heroicon-o-document-duplicate class="w-4 inline"/> Duplicate Event
                                                            </button>
                                                            @if(auth()->user()->isAdmin() || auth()->user()->can('manageEditors', $event))
                                                                <x-tailwind-dropdown-item href="{{route('admin.events.editors', $event->id)}}" title="Manage editors"><x-heroicon-o-user-group class="w-4 inline"/> Manage Editors</x-tailwind-dropdown-item>
                                                            @endif
                                                            <x-tailwind-dropdown-item href="{{route('admin.events.log', $event->id)}}"><x-heroicon-o-list-bullet class="w-4 inline"/> View Logs</x-tailwind-dropdown-item>
                                                        </div>
                                                        <div class="py-1" role="none">
                                                            <x-tailwind-dropdown-item href="{{ route('admin.events.volunteers', $event) }}">View All Volunteers / Email</x-tailwind-dropdown-item>
                                                            <x-tailwind-dropdown-item href="{{ route('admin.events.allShifts', $event) }}">View Shift Overview</x-tailwind-dropdown-item>
                                                        </div>
                                                        @if ($event->visibility === 'public' || $event->visibility === 'unlisted' || $event->visibility === 'internal')
                                                        <div class="py-1" role="none">
                                                            <x-tailwind-dropdown-item href="#" onclick="copyToClipboard('{{ route('volunteer.events.show', $event) }}')"><x-heroicon-s-link class="w-4 inline"/> Copy Internal URL</x-tailwind-dropdown-item>
                                                            <x-tailwind-dropdown-item href="#" onclick="copyToClipboard('{{ route('vol-listings-public.show', $event->id) }}')"><x-heroicon-s-link class="w-4 inline"/> Copy Public URL</x-tailwind-dropdown-item>
                                                        </div>
                                                        @endif
                                                    </x-tailwind-dropdown>
                                                </div>

                                                {{-- Desktop: Edit + Manage Shifts + Manage dropdown --}}
                                                <div class="hidden sm:flex sm:items-center sm:gap-0">
                                                    <a href="{{ route('admin.events.edit', $event) }}" class="text-blue-600 dark:text-blue-200 px-2 hover:underline whitespace-nowrap"><x-heroicon-s-pencil class="w-3 h-3 inline-block align-middle"/> Edit</a>
                                                    <a href="{{ route('admin.events.shifts.index', $event) }}" class="text-blue-600 dark:text-blue-200 px-2 hover:underline whitespace-nowrap"><x-heroicon-m-clock class="w-3 h-3 inline-block align-middle"/> Manage Shifts</a>

                                                <x-tailwind-dropdown buttonClass="dropdown-link text-blue-600 dark:text-blue-200" label="Manage" id="{{ $event->id }}">
                                                    <div class="py-1" role="none">
                                                        <x-tailwind-dropdown-item href="{{route('admin.events.edit', $event->id)}}" title="Edit Event Details"><x-heroicon-o-pencil class="w-4 inline"/> Edit Event</x-tailwind-dropdown-item>
                                                        <x-tailwind-dropdown-item href="{{route('admin.events.shifts.index', $event->id)}}" title="Create/Edit/View Event Shifts"><x-heroicon-o-clock class="w-4 inline"/> Manage Shifts</x-tailwind-dropdown-item>
                                                        <button type="button" 
                                                            onclick="window.dispatchEvent(new CustomEvent('open-event-duplicate-modal', { detail: { id: {{ $event->id }}, name: '{{ addslashes($event->name) }}', shiftsCount: {{ $event->shifts->count() }}, startDate: '{{ $event->start_date->toIso8601String() }}', endDate: '{{ $event->end_date->toIso8601String() }}' } }))"
                                                            class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 text-gray-700"
                                                            title="Create a duplicate event with all shifts">
                                                            <x-heroicon-o-document-duplicate class="w-4 inline"/> Duplicate Event
                                                        </button>
                                                        @if(auth()->user()->isAdmin() || auth()->user()->can('manageEditors', $event))
                                                            <x-tailwind-dropdown-item href="{{route('admin.events.editors', $event->id)}}" title="Manage who can edit this event"><x-heroicon-o-user-group class="w-4 inline"/> Manage Editors</x-tailwind-dropdown-item>
                                                        @endif
                                                        <x-tailwind-dropdown-item href="{{route('admin.events.log', $event->id)}}" title="View Event Logs"><x-heroicon-o-list-bullet class="w-4 inline"/> View Logs</x-tailwind-dropdown-item>
                                                    </div>
                                                    <div class="py-1" role="none">
                                                        <x-tailwind-dropdown-item href="{{ route('admin.events.volunteers', $event) }}" title="View all unquie volunteers signed up and email actions">View All Volunteers / Email</x-tailwind-dropdown-item>
                                                        <x-tailwind-dropdown-item href="{{ route('admin.events.allShifts', $event) }}" title="View all the shifts and their associated volunteers">View Shift Overview</x-tailwind-dropdown-item>
                                                        {{-- <x-tailwind-dropdown-item href="{{ route('admin.events.agenda', $event) }}" title="View the shifts in a day agenda view">View Agenda (BETA)</x-tailwind-dropdown-item> --}}
                                                    </div>
                                                    @if ($event->visibility === 'public' || $event->visibility === 'unlisted' || $event->visibility === 'internal' )
                                                    <div class="py-1" role="none">
                                                        <x-tailwind-dropdown-item href="#" title="Link to the logged in user signup sheet" onclick="copyToClipboard('{{ route('volunteer.events.show', $event) }}')">
                                                            <x-heroicon-s-link class="w-4 inline"/> Copy Internal Signup URL
                                                        </x-tailwind-dropdown-item>
                                                        <x-tailwind-dropdown-item href="#" title="Link the to the public site listing for this event" onclick="copyToClipboard('{{ route('vol-listings-public.show', $event->id) }}')">
                                                            <x-heroicon-s-link class="w-4 inline"/> Copy Public URL
                                                        </x-tailwind-dropdown-item>
                                                    </div>
                                                    @else
                                                    <div class="py-1" role="none">
                                                        <x-tailwind-dropdown-item class="opacity-20 cursor-not-allowed" title="Link to the logged in user signup sheet">
                                                            <x-heroicon-s-link class="w-4 inline"/> Copy Internal Signup URL
                                                        </x-tailwind-dropdown-item>
                                                        <x-tailwind-dropdown-item class="opacity-20 cursor-not-allowed">
                                                            <x-heroicon-s-link class="w-4 inline"/> Copy Public URL ({{ucfirst($event->visibility)}})
                                                        </x-tailwind-dropdown-item>
                                                    </div>
                                                    @endif
                                                    {{-- <div class="py-1" role="none">
                                                        <x-tailwind-dropdown-item title="Delete" href="#" class="hover:bg-red-50 text-red-900" />
                                                    </div> --}}
                                                </x-tailwind-dropdown>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="9">
                                            <p class="">No events found</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{-- {{ $events->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Public URL copied to clipboard!');
            }, function(err) {
                console.error('Failed to copy URL: ', err);
            });
        }
    </script>
</x-app-layout>

{{-- Advanced Duplicate Modal - Outside layout to prevent constraints --}}
@include('admin.events.advanced-duplicate-modal')
