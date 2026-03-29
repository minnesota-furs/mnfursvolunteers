<x-app-layout>
    @section('title', 'Manage Shifts for ' . $event->name)
    <x-slot name="header">
        Manage Shifts for {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.events.index')}}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
        <x-tailwind-dropdown label="More" id=1>
            <div class="py-1" role="none">
                <x-tailwind-dropdown-item href="{{route('admin.events.edit', $event->id)}}"><x-heroicon-o-pencil class="w-4 inline"/>  Edit Event</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{route('admin.events.log', $event->id)}}" title="View Event Logs"><x-heroicon-o-list-bullet class="w-4 inline"/> View Logs</x-tailwind-dropdown-item>
            </div>
            <div class="py-1" role="none">
                <x-tailwind-dropdown-item href="{{ route('admin.events.volunteers', $event) }}" title="View all unquie volunteers signed up and email actions">View All Volunteers / Email</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{ route('admin.events.allShifts', $event) }}" title="View all the shifts and their associated volunteers">View Shift Overview</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{ route('admin.events.agenda', $event) }}" title="View calendar-style agenda with shift coverage visualization"><x-heroicon-o-calendar class="w-4 inline"/> View Agenda</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{ route('admin.events.shift-tag-report', $event) }}" title="View volunteer breakdown by shift tag"><x-heroicon-o-tag class="w-4 inline"/> Tag Report</x-tailwind-dropdown-item>
                <x-tailwind-dropdown-item href="{{ route('admin.events.manager-dashboard', $event) }}" title="Live coverage dashboard for this event"><x-heroicon-s-signal class="w-4 inline text-green-500"/> Live Manager Dashboard</x-tailwind-dropdown-item>
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
        @can('manageEditors', $event)
            <a href="{{ route('admin.events.editors', $event) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-o-user-group class="w-4 inline"/> Manage Collaborators
            </a>
        @endcan

        {{-- Split button: Create Shift Series (primary) + dropdown for Create New Shift --}}
        <div class="relative inline-flex rounded-md shadow-md">
            <a href="{{ route('admin.events.shifts.create-series', $event) }}"
                class="rounded-l-md bg-white px-3 py-2 text-sm font-semibold text-brand-green hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green whitespace-nowrap">
                <x-heroicon-o-queue-list class="w-4 inline -mt-0.5"/> Create Shift Series
            </a>
            <button type="button" id="menuButton9901" onclick="openMenu(9901); event.stopPropagation();"
                class="rounded-r-md border-l border-gray-200 bg-white px-2 py-2 text-brand-green hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green"
                aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">More shift options</span>
                <x-heroicon-m-chevron-down class="w-4 h-4"/>
            </button>
            <div id="optDropdown9901"
                class="hidden absolute right-0 top-full z-50 mt-1 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 scale-95 transition-transform"
                role="menu" tabindex="-1">
                <div class="py-1" role="none">
                    <a href="{{ route('admin.events.shifts.create', $event) }}"
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                        role="menuitem">
                        <x-heroicon-s-plus class="w-4 h-4 mr-2"/> Create New Shift
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                {{-- <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Events</h1>
                    </div>
                </div> --}}
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
                            x-data="{
                                showVolunteers: JSON.parse(localStorage.getItem('shifts_showVolunteers') ?? 'false'),
                                toggle() {
                                    this.showVolunteers = !this.showVolunteers;
                                    localStorage.setItem('shifts_showVolunteers', this.showVolunteers);
                                }
                            }">

                            {{-- View Options --}}
                            <div class="mb-3 flex items-center gap-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mr-1">View Options:</span>
                                <button type="button"
                                    @click="toggle()"
                                    :class="showVolunteers
                                        ? 'bg-brand-green text-white border-brand-green'
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
                                    class="inline-flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-xs font-medium shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-1">
                                    <x-heroicon-o-users class="w-3.5 h-3.5"/>
                                    Show Volunteer Names
                                    <span x-show="showVolunteers" class="ml-1 text-xs opacity-80">&check;</span>
                                </button>
                            </div>

                            @if($event->requiredTags->isNotEmpty())
                                <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                                    <x-heroicon-s-tag class="w-4 h-4 inline text-gray-400" />
                                    This event requires volunteers to have specific tags ({{ $event->requiredTags->pluck('name')->join(', ') }}), which may limit who can sign up for shifts.
                                </div>
                            @endif
                            {{-- Bulk action toolbar --}}
                            <div id="bulk-action-bar"
                                class="hidden mb-3 flex items-center gap-3 rounded-lg border border-yellow-300 bg-yellow-50 dark:bg-yellow-900/30 dark:border-yellow-700 px-4 py-2 text-sm">
                                <span class="font-medium text-yellow-800 dark:text-yellow-200" id="bulk-count-label">0 selected</span>
                                <form id="bulk-delete-form"
                                    action="{{ route('admin.events.shifts.bulk-destroy', $event) }}"
                                    method="POST"
                                    onsubmit="return confirmBulkDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <div id="bulk-hidden-inputs"></div>
                                    <button type="submit"
                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 focus:outline-none">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5 mr-1"/> Delete Selected
                                    </button>
                                </form>
                                <button type="button" onclick="clearSelection()"
                                    class="text-xs text-gray-500 dark:text-gray-400 hover:underline">Clear</button>
                            </div>

                            {{-- {{ $shifts->links() }} --}}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-0 w-8">
                                            <input type="checkbox" id="select-all-checkbox"
                                                onchange="toggleSelectAll(this)"
                                                class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green cursor-pointer"
                                                title="Select all">
                                        </th>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Name</th>
                                        <th scope="col"
                                            class="hidden sm:table-cell px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Start Time</th>
                                        <th scope="col"
                                            class="hidden sm:table-cell px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            End Time</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 w-24">
                                            Volunteers
                                        </th>
                                        <th scope="col"
                                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            Tags
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-2 sm:pr-0 w-16">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @if ($shifts->isEmpty())
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="7">
                                            <p class="font-semibold">No shifts created.</p>
                                            <p class="">People cannot signup for shifts until they are created.</p>
                                        </td>
                                    </tr>
                                    @else
                                    @php
                                        $shiftsByDay = $shifts->groupBy(fn($s) => $s->start_time->format('l, M j'));
                                    @endphp
                                    @foreach ($shiftsByDay as $day => $dayShifts)
                                        {{-- Day header row --}}
                                        <tr>
                                            <td colspan="7" class="bg-gray-100 dark:bg-gray-700 py-2 pl-4 pr-3 text-sm font-bold text-gray-800 dark:text-gray-100 sm:pl-0 border-t-2 border-gray-300 dark:border-gray-600 sm:pl-2">
                                                <x-heroicon-o-calendar class="w-4 h-4 inline mb-0.5 mr-1 text-gray-500 dark:text-gray-400"/>{{ $day }}
                                            </td>
                                        </tr>
                                        @php
                                            $shiftsByHour = $dayShifts->groupBy(fn($s) => $s->start_time->format('g A'));
                                        @endphp
                                        @foreach ($shiftsByHour as $hour => $hourShifts)
                                            {{-- Hour sub-header row --}}
                                            <tr>
                                                <td colspan="7" class="bg-gray-50 dark:bg-gray-800 py-1.5 pl-8 pr-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide sm:pl-2">
                                                    {{ $hour }}
                                                </td>
                                            </tr>
                                            @foreach ($hourShifts as $shift)
                                            @php
                                                $signupCount = $shift->users->count();
                                                $textClass = $signupCount >= $shift->max_volunteers ? 'text-green-800 dark:text-green-500 text-weight-800' : ($signupCount > 0 ? 'text-purple-700 dark:text-purple-400' : '');
                                            @endphp
                                            <tr class="">
                                                <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0 w-8">
                                                    <input type="checkbox"
                                                        class="shift-checkbox h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green cursor-pointer"
                                                        value="{{ $shift->id }}"
                                                        onchange="updateBulkBar()">
                                                </td>
                                                <td class="py-4 pl-4 pr-3 text-sm sm:pl-0">
                                                    <a class="font-medium text-blue-700 dark:text-blue-200 hover:underline" href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}">
                                                        {{$shift->name}}
                                                    </a>
                                                    {{-- Mobile: show time + tags inline under name --}}
                                                    <div class="sm:hidden mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <span class="whitespace-nowrap">
                                                            <x-heroicon-m-clock class="w-3 h-3 inline -mt-0.5"/>
                                                            {{ $shift->start_time->format('g:i A') }} – {{ $shift->end_time->format('g:i A') }}
                                                            @if($shift->double_hours)
                                                                <x-heroicon-m-star title="Double Hours" class="w-3 mb-0.5 inline text-yellow-500"/>
                                                            @endif
                                                        </span>
                                                        @foreach ($shift->tags->sortBy('name') as $tag)
                                                            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-xs font-medium"
                                                                style="{{ $tag->color ? 'background-color:' . $tag->color . '22; color:' . $tag->color : 'background-color:#e5e7eb; color:#374151' }}">
                                                                @if($tag->color)
                                                                    <span class="inline-block w-1.5 h-1.5 rounded-full mr-1" style="background-color:{{ $tag->color }}"></span>
                                                                @endif
                                                                {{ $tag->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="hidden sm:table-cell whitespace-nowrap py-4 px-3 text-sm">
                                                    {{ $shift->start_time->format('g:i A') }}
                                                </td>
                                                <td class="hidden sm:table-cell whitespace-nowrap py-4 px-3 text-sm">
                                                    {{ $shift->end_time->format('g:i A') }}
                                                    @if($shift->double_hours)
                                                        <x-heroicon-m-star title="Double Hours" class="w-3 mb-1 inline"/>
                                                    @endif
                                                </td>
                                                <td class="py-4 pl-3 pr-2 text-sm text-center {{ $textClass }}">
                                                    <div class="whitespace-nowrap">
                                                        @if($signupCount >= $shift->max_volunteers)
                                                            <x-heroicon-s-battery-100 title="Fully Staffed" class="w-4 mb-1 inline"/>
                                                        @elseif($signupCount > 0)
                                                            <x-heroicon-s-battery-50 title="Partially Staffed" class="w-4 mb-1 inline"/>
                                                        @else
                                                            <x-heroicon-s-battery-0 title="No Staff" class="w-4 mb-1 inline"/>
                                                        @endif
                                                        {{ $shift->users->count() }} of {{ $shift->max_volunteers }}
                                                    </div>
                                                    <div x-show="showVolunteers" x-cloak
                                                        class="mt-1.5 text-left space-y-0.5">
                                                        @forelse($shift->users->sortBy(fn($v) => $v->displayName()) as $vol)
                                                            <div class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs font-normal
                                                                {{ $vol->pivot->no_show ? 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 line-through' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">
                                                                @if($vol->pivot->no_show)
                                                                    <x-heroicon-m-x-circle class="w-3 h-3 flex-shrink-0"/>
                                                                @else
                                                                    <x-heroicon-m-user class="w-3 h-3 flex-shrink-0"/>
                                                                @endif
                                                                {{ $vol->displayName() }}
                                                            </div>
                                                        @empty
                                                            <span class="text-xs text-gray-400 italic">No sign-ups</span>
                                                        @endforelse
                                                    </div>
                                                </td>
                                                <td class="hidden py-4 px-3 text-sm">
                                                    @forelse ($shift->tags->sortBy('name') as $tag)
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium mb-1 mr-1"
                                                            style="{{ $tag->color ? 'background-color:' . $tag->color . '22; color:' . $tag->color : 'background-color:#e5e7eb; color:#374151' }}">
                                                            @if($tag->color)
                                                                <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color:{{ $tag->color }}"></span>
                                                            @endif
                                                            {{ $tag->name }}
                                                        </span>
                                                    @empty
                                                        <span class="text-gray-400 text-xs">—</span>
                                                    @endforelse
                                                </td>
                                                <td class="py-4 pl-2 pr-2 text-sm align-top">
                                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                                                        {{-- Edit link: icon-only on mobile, icon+text on sm+ --}}
                                                        <a href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}"
                                                            class="inline-flex items-center gap-1 rounded px-2 py-1 text-blue-600 dark:text-blue-200 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                                            <x-heroicon-m-pencil class="w-3.5 h-3.5 flex-shrink-0"/>
                                                            <span class="hidden sm:inline text-xs">Edit</span>
                                                        </a>

                                                        {{-- Mobile: single ••• dropdown containing all actions --}}
                                                        <div class="sm:hidden">
                                                            <x-tailwind-dropdown buttonClass="inline-flex items-center gap-1 rounded px-2 py-1 text-xs text-blue-600 dark:text-blue-200 hover:bg-blue-50 dark:hover:bg-blue-900/30" label="•••" id="{{ $shift->id + 5000 }}">
                                                                <div class="py-1" role="none">
                                                                    <a href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}"
                                                                        class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                        <x-heroicon-m-pencil class="w-4 h-4 mr-2"/> Edit Shift
                                                                    </a>
                                                                </div>
                                                                <div class="py-1" role="none">
                                                                    <form action="{{ route('admin.events.shifts.duplicate', [$event, $shift]) }}" method="POST" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                                        @csrf
                                                                        <button type="submit" title="Quick duplicate (adds 1 hour to time)">
                                                                            <x-heroicon-o-document-duplicate class="w-4 inline"/> Quick Duplicate
                                                                        </button>
                                                                    </form>
                                                                    <button type="button"
                                                                        onclick="window.dispatchEvent(new CustomEvent('open-duplicate-modal', { detail: { id: {{ $shift->id }}, name: '{{ addslashes($shift->name) }}', startTime: '{{ $shift->start_time->format('Y-m-d H:i:s') }}' } }))"
                                                                        class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 w-full text-left"
                                                                        title="Advanced duplicate with multiple options">
                                                                        <x-heroicon-o-squares-plus class="w-4 inline"/> Advanced Duplicate
                                                                    </button>
                                                                </div>
                                                                <div class="py-1" role="none">
                                                                    <form action="{{ route('admin.events.shifts.destroy', [$event, $shift]) }}" method="POST" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-red-600">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" onclick="return confirm('Are you sure you want to delete slot {{$shift->name}} on {{$shift->start_time->format('l \@ g:i A')}}?\n\nThis cannot be undone!')">
                                                                            <x-heroicon-o-trash class="w-4 inline"/> Delete
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </x-tailwind-dropdown>
                                                        </div>

                                                        {{-- Desktop: Manage dropdown --}}
                                                        <div class="hidden sm:block">
                                                            <x-tailwind-dropdown buttonClass="dropdown-link text-blue-600 dark:text-blue-200" label="Manage" id="{{ $shift->id + 1 }}">
                                                                <div class="py-1" role="none">
                                                                    <form action="{{ route('admin.events.shifts.duplicate', [$event, $shift]) }}" method="POST" class="block px-4 py-2 text-sm hover:bg-gray-50 text-gray-700">
                                                                        @csrf
                                                                        <button type="submit" class="" title="Quick duplicate (adds 1 hour to time)">
                                                                            <x-heroicon-o-document-duplicate class="w-4 inline"/> Quick Duplicate
                                                                        </button>
                                                                    </form>
                                                                    <button type="button"
                                                                        onclick="window.dispatchEvent(new CustomEvent('open-duplicate-modal', { detail: { id: {{ $shift->id }}, name: '{{ addslashes($shift->name) }}', startTime: '{{ $shift->start_time->format('Y-m-d H:i:s') }}' } }))"
                                                                        class="block px-4 py-2 text-sm hover:bg-gray-50 text-gray-700"
                                                                        title="Advanced duplicate with multiple options">
                                                                        <x-heroicon-o-squares-plus class="w-4 inline"/> Advanced Duplicate
                                                                    </button>
                                                                </div>
                                                                <div class="py-1" role="none">
                                                                    <form action="{{ route('admin.events.shifts.destroy', [$event, $shift]) }}" method="POST" class="block px-4 py-2 text-sm hover:bg-gray-50 text-gray-700">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="" onclick="return confirm('Are you sure you want to delete slot {{$shift->name}} on {{$shift->start_time->format('l \@ g:i A')}}?\n\nThis cannot be undone!')"><x-heroicon-o-trash class="w-4 inline"/> Delete</button>
                                                                    </form>
                                                                </div>
                                                            </x-tailwind-dropdown>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                            {{-- {{ $shifts->links() }} --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- <x-slot name="right">
        <p class="py-4 text-justify">Paragraph one.</p>
        <p class="py-4 text-justify">Paragraph two.</p>
    </x-slot> --}}
    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Public URL copied to clipboard!');
            }, function(err) {
                console.error('Failed to copy URL: ', err);
            });
        }

        function getCheckedBoxes() {
            return Array.from(document.querySelectorAll('.shift-checkbox:checked'));
        }

        function updateBulkBar() {
            const checked = getCheckedBoxes();
            const bar     = document.getElementById('bulk-action-bar');
            const label   = document.getElementById('bulk-count-label');
            const allBox  = document.getElementById('select-all-checkbox');
            const all     = document.querySelectorAll('.shift-checkbox');

            bar.classList.toggle('hidden', checked.length === 0);
            label.textContent = checked.length + ' selected';
            allBox.indeterminate = checked.length > 0 && checked.length < all.length;
            allBox.checked = checked.length === all.length && all.length > 0;
        }

        function toggleSelectAll(source) {
            document.querySelectorAll('.shift-checkbox').forEach(cb => cb.checked = source.checked);
            updateBulkBar();
        }

        function clearSelection() {
            document.querySelectorAll('.shift-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all-checkbox').checked = false;
            document.getElementById('select-all-checkbox').indeterminate = false;
            updateBulkBar();
        }

        function confirmBulkDelete() {
            const checked = getCheckedBoxes();
            if (checked.length === 0) return false;

            if (!confirm('Delete ' + checked.length + ' shift(s)? This cannot be undone!')) return false;

            // Populate hidden inputs with selected IDs
            const container = document.getElementById('bulk-hidden-inputs');
            container.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'shift_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });
            return true;
        }
    </script>
</x-app-layout>

{{-- Advanced Duplicate Modal - Outside layout to prevent constraints --}}
@include('admin.shifts.advanced-duplicate-modal')
