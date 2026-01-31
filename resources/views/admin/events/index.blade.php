<x-app-layout>
    @section('title', 'Manage Events')
    <x-slot name="header">
        {{ __('Manage Events') }}
    </x-slot>

    <x-slot name="actions">
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
                    <form method="GET" action="{{ route('admin.events.index') }}" class="flex items-center gap-6">
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
                    <div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $evemts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Creator</th>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 w-32 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Visibility</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 w-16 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-16">
                                            Shifts</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Start Date</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">End Date
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($events as $event)
                                    <tr>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <span class="font-extrabold {{ $event->hasPast() ? 'text-gray-400' : '' }}" href="{{route('admin.events.edit', $event->id)}}">
                                                {{$event->name}}
                                                @if($event->requiredTags->isNotEmpty())
                                                    <x-heroicon-s-tag class="w-4 h-4 inline text-red-500" title="Has tag requirements ({{ $event->requiredTags->count() }} tag{{ $event->requiredTags->count() > 1 ? 's' : '' }})"/>
                                                @endif
                                                <!-- If event past -->
                                                @if ($event->hasPast())
                                                    <span>(Past Event)</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            <div class="flex items-center">
                                                <x-heroicon-o-user class="w-4 h-4 mr-1 text-gray-400"/>
                                                <span class="text-gray-700 dark:text-gray-300">{{ $event->creator->name ?? 'Unknown' }}</span>
                                            </div>
                                            @if($event->editors()->count() > 0)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    +{{ $event->editors()->count() }} editor{{ $event->editors()->count() > 1 ? 's' : '' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
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
                                            @else
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md bg-green-200 px-1.5 py-0.5 text-xs font-medium text-green-800">
                                                    <svg class="size-1.5 fill-green-800" viewBox="0 0 6 6" aria-hidden="true">
                                                    <circle cx="3" cy="3" r="3" />
                                                    </svg>
                                                    Public
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-1 pr-3 text-sm sm:pl-0">
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('update', $event))
                                                <a href="{{ route('admin.events.shifts.index', $event) }}" class="text-blue-500 font-semibold px-2">{{ $event->shifts()->count() }}</a>
                                            @else
                                                <span class="text-gray-500 font-semibold px-2">{{ $event->shifts()->count() }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->start_date->format('M j, Y') }}</div>
                                            <div>{{ $event->start_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm text-center sm:pl-0">
                                            <div>{{ $event->end_date->format('M j, Y') ?? '-' }}</div>
                                            <div>{{ $event->end_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('update', $event))
                                                <a href="{{ route('admin.events.edit', $event) }}" class="text-blue-600 px-2"><x-heroicon-s-pencil class="w-3 inline"/> Edit</a>
                                                <a href="{{ route('admin.events.shifts.index', $event) }}" class="text-blue-600 px-2"><x-heroicon-m-clock class="w-3 inline"/> Manage Shifts</a>
                                            
                                                <x-tailwind-dropdown buttonClass="dropdown-link text-blue-600" label="Manage" id="{{ $event->id }}">
                                                    <div class="py-1" role="none">
                                                        <x-tailwind-dropdown-item href="{{route('admin.events.edit', $event->id)}}" title="Edit Event Details"><x-heroicon-o-pencil class="w-4 inline"/> Edit Event</x-tailwind-dropdown-item>
                                                        <x-tailwind-dropdown-item href="{{route('admin.events.shifts.index', $event->id)}}" title="Create/Edit/View Event Shifts"><x-heroicon-o-clock class="w-4 inline"/> Manage Shifts</x-tailwind-dropdown-item>
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
                                                    @if ($event->visibility === 'public' || $event->visibility === 'unlisted' )
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
