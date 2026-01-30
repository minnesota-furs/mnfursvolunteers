<x-app-layout>
    @auth
        @section('title', 'Users - View ' . $user->name)
        <x-slot name="header">
            {{ __('Volunteer: ') }}{{ $user->name }}
        </x-slot>

        <x-slot name="actions">
            {{-- <button type="button"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Log Hours
            </button> --}}
            @can('manage-users')
            <a href="{{ route('users.edit', $user->id) }}"
                class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Edit
            </a>
            <a href="{{ route('users.timeline', $user->id) }}"
                class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-list-bullet class="w-4 inline"/> Activity Timeline
            </a>
            @endcan

            @if(Auth::user()->can('manage-users') || Auth::id() === $user->id)
            <x-tailwind-dropdown label="More" id=1>
                <div class="py-1" role="none">
                    @can('manage-users')
                    <x-tailwind-dropdown-item href="{{ route('users.communications', $user->id) }}" title="Communication History"><x-heroicon-o-envelope class="w-4 inline"/> Communication History</x-tailwind-dropdown-item>
                    @endcan
                    @if(Auth::user()->can('manage-users') || Auth::id() === $user->id)
                    <x-tailwind-dropdown-item href="{{ route('users.notes.index', $user->id) }}" title="Notes"><x-heroicon-o-pencil-square class="w-4 inline"/> Notes</x-tailwind-dropdown-item>
                    @endif
                    @if (Auth::user()->isAdmin())
                    <x-tailwind-dropdown-item href="{{ route('users.permissions.edit', $user->id) }}" title="App Permissions"><x-heroicon-o-shield-check class="w-4 inline"/> App Permissions</x-tailwind-dropdown-item>
                    @endif
                </div>
                {{-- <div class="py-1" role="none">
                    <x-tailwind-dropdown-item title="Delete" href="#" class="hover:bg-red-50 text-red-900" />
                </div> --}}
            </x-tailwind-dropdown>
            @endif
        </x-slot>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- User Information Section --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="px-4 sm:px-0">
                                <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Volunteer / User
                                    Information</h3>
                            </div>
                            <div class="mt-6 border-t border-gray-100 dark:border-gray-700">
                                <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Name / Alias
                                        </dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ $user->name }}</dd>
                                    </div>
                                    @if (Auth::user()->isAdmin())
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Legal
                                                First Name</dt>
                                            <dd
                                                class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                                {{ $user->first_name ?? '-' }}</dd>
                                        </div>
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Legal
                                                Last Name</dt>
                                            <dd
                                                class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                                {{ $user->last_name ?? '-' }}</dd>
                                        </div>
                                    @endif
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Email
                                            address</dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            @if (Auth::user()->isAdmin())
                                                {{ $user->email }}
                                            @else
                                                ******
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Status</dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            @if ($user->active == true)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-800 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-100 ring-1 ring-inset ring-green-600/20">Active</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-md bg-yellow-50 dark:bg-yellow-800 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-100 ring-1 ring-inset ring-yellow-600/20">Inactive</span>
                                            @endif
                                            @if ($user->isAdmin() == true)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-800 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-100 ring-1 ring-inset ring-red-600/20">Admin</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">User Comment</dt>
                                        @if ($user->hasNotes())
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                                {{ $user->notes }}
                                            @else
                                            <dd
                                                class="mt-1 text-sm leading-6 text-gray-300 dark:text-gray-700 sm:col-span-2 sm:mt-0">
                                                No Notes recorded...
                                        @endif

                                        </dd>
                                    </div>
                                    @if (Auth::user()->isAdmin())
                                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">User Notes Count</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                                <span class="font-medium">{{ $totalNotes }}</span> total note{{ $totalNotes !== 1 ? 's' : '' }}
                                                @if($writeupCount > 0)
                                                    <span class="text-red-600 dark:text-red-400">
                                                        (<span class="font-semibold">{{ $writeupCount }}</span> writeup{{ $writeupCount !== 1 ? 's' : '' }})
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        {{-- Role Information Section --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="px-4 sm:px-0">
                                <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Role Information
                                </h3>
                            </div>
                            <div class="mt-6 border-t border-gray-100 dark:border-gray-700">
                                <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">App
                                            Permissions</dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            @if (!empty($user->permissions))
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($user->permissions as $permissionLabel)
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-yellow-50 dark:bg-yellow-800 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-100 ring-1 ring-inset ring-yellow-600/20">
                                                            {{ $permissionLabel }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-gray-500 dark:text-gray-400 italic">This user has no permissions assigned.</p>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Vol Code</dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ $user->vol_code ?? '-' }}</dd>
                                    </div>
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Primary
                                            Sector</dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ $user->sector->name ?? '-' }}</dd>
                                    </div>

                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Departments
                                            ({{ $user->departments->count() }})</dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">

                                            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @forelse ($user->departments as $department)
                                                    <li
                                                        class="relative bg-white dark:bg-gray-800 px-1 py-1 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                        <div class="flex justify-between space-x-3">
                                                            <div class="min-w-0 flex-1">
                                                                <a href="{{ route('departments.show', $department->id) }}"
                                                                    class="block focus:outline-none">
                                                                    <span class="absolute inset-0"
                                                                        aria-hidden="true"></span>
                                                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $department->name }}</p>
                                                                    <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                                                        {{ $department->sector->name }}</p>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @empty
                                                    <li class="text-gray-500 dark:text-gray-400">No Departments Assigned</li>
                                                @endforelse
                                            </ul>
                                        </dd>
                                    </div>

                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">This Fiscal
                                        </dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ format_hours($user->totalHoursForCurrentFiscalLedger()) }} hours
                                        </dd>
                                    </div>

                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Lifetime
                                            Hours</dt>
                                        <dd
                                            class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ format_hours($user->totalVolunteerHours()) }} hours
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

            </div>
            
            <!-- Hour Log Section -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="sm:flex sm:items-center sm:justify-between">
                            <div class="sm:flex-auto">
                                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Hour Log</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Recent volunteer hours logged for this user</p>
                            </div>
                            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-2">
                        @if (Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                            <a href="{{ route('hours.create', ['user' => $user->id]) }}"
                                class="block rounded-md bg-brand-green px-2 py-1 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                <x-heroicon-m-clock class="w-4 inline" /> New Hour Log
                            </a>
                        @endif
                            
                        @if(Auth::user()->isAdmin())
                            @if($user->hasValidHourSubmissionToken())
                                <button 
                                    onclick="copyToClipboard('{{ $user->getHourSubmissionUrl() }}')"
                                    class="block rounded-md bg-indigo-600 px-2 py-1 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                    title="Copy hour submission link to clipboard"
                                >
                                    <x-heroicon-m-link class="w-4 inline" /> Copy Hour Link
                                </button>
                            @else
                                <form action="{{ route('hours.generate-token', ['user' => $user->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="block rounded-md bg-indigo-600 px-2 py-1 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                        title="Generate a unique link for hour submission"
                                    >
                                        <x-heroicon-m-link class="w-4 inline" /> Generate Hour Link
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                        </div>
                    </div>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">
                                            Short Description</th>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 hidden md:table-cell">
                                            Sector, Department</th>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-32">
                                            Amount</th>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-32 hidden sm:table-cell">
                                            Task Date</th>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-16 hidden sm:table-cell">
                                            Notes</th>
                                        <th scope="col" class="relative w-16 whitespace-nowrap py-3.5 pl-3 pr-4 sm:pr-0">
                                            <span class="sr-only">Edit</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($volunteerHours as $volunteerHour)
                                        <tr class="even:bg-gray-50 even:dark:bg-gray-800/25">
                                            <td
                                                class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                                                <a href="{{ route('hours.show', $volunteerHour->id) }}"
                                                    class="text-slate-500">
                                                    @if ($volunteerHour->description)
                                                        {{ $volunteerHour->description }}
                                                    @else
                                                        <span class="text-xs text-gray-300">Description Not Provided</span>
                                                    @endif
                                                </a>
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-400 dark:text-gray-300 hidden md:table-cell">
                                                @if ($volunteerHour->hasDepartment())
                                                    {{ $volunteerHour->department->sector->name ?? '-' }} /
                                                    {{ $volunteerHour->department->name ?? '' }}
                                                @else
                                                    <span class="text-xs text-gray-300">Department Not Provided</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300"
                                                title="{{ $volunteerHour->fiscalLedger->name ?? '???' }}">
                                                {{ format_hours($volunteerHour->hours) }} hrs
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300 hidden sm:table-cell">
                                                @if (isset($volunteerHour->volunteer_date))
                                                    {{ $volunteerHour->volunteer_date->diffForHumans() ?? '-' }}
                                                @else
                                                    <span class="text-xs text-gray-300">Date not logged</span>
                                                @endif
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300 hidden sm:table-cell">
                                                @if ($volunteerHour->hasNotes())
                                                    <x-heroicon-o-check title="{{ $volunteerHour->notes ?? '' }}"
                                                        class="w-4" />
                                                @else
                                                    <span class="text-xs text-gray-300">-</span>
                                                @endif
                                            </td>
                                            <td
                                                class="relative whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                                <a href="{{ route('hours.show', $volunteerHour->id) }}"
                                                    class="text-blue-400 hover:text-blue-500 px-1">View<span
                                                        class="sr-only"></span></a>
                                                @if (Auth::user()->isAdmin() || Auth::user()->id == $volunteerHour->user_id)
                                                    <a href="{{ route('hours.edit', $volunteerHour->id) }}"
                                                        class="text-blue-400 hover:text-blue-500 px-1">Edit<span
                                                            class="sr-only"></span></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center"
                                                colspan="6">No hours logged for this user...</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                            {{ $volunteerHours->links('components.compact-pagination') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Volunteer Signup Log Section -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="sm:flex-auto">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Volunteer Signup Log</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Shift signups and volunteer slots for this user</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">
                                            Event/Task Name</th>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-32">
                                            Duration</th>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-32 hidden sm:table-cell">
                                            Start Time</th>
                                        <th scope="col"
                                            class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-32 hidden sm:table-cell">
                                            End Time</th>
                                        <th scope="col"
                                            class="relative w-16 whitespace-nowrap py-3.5 pl-3 pr-4 sm:pr-0">
                                            <span class="sr-only">View</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($user->shifts->groupBy('event.name') as $eventName => $shifts)
                                        {{-- Event Header Row --}}
                                        <tr class="bg-gray-100 dark:bg-gray-800">
                                            <td colspan="6" class="px-2 py-2 text-sm font-bold text-gray-700 dark:text-gray-100">
                                                {{ $eventName }}
                                            </td>
                                        </tr>
                    
                                        @foreach ($shifts as $shift)
                                            <tr class="even:bg-gray-50 even:dark:bg-gray-800/25">
                                                <td class="whitespace-nowrap pl-10 pr-2 py-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                                                    {{ $shift->name ?? 'Unnamed Shift' }}
                                                </td>
                                                <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300">
                                                    {{ \Carbon\Carbon::parse($shift->start_time)->diffForHumans(\Carbon\Carbon::parse($shift->end_time), true) }}
                                                </td>
                                                <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300 hidden sm:table-cell">
                                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('M d, g:i A') }}
                                                </td>
                                                <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300 hidden sm:table-cell">
                                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('M d, g:i A') }}
                                                </td>
                                                <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                                    {{-- <a href="{{ route('events.shifts.show', [$shift->event_id, $shift->id]) }}"
                                                        class="text-blue-400 hover:text-blue-500 px-1">View</a> --}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr class="dark:bg-gray-800">
                                            <td colspan="6" class="px-2 py-2 text-sm text-center text-gray-700 dark:text-gray-100">
                                                No Slots Signed up
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>
        </div>
    @endauth
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success feedback
                alert('Hour submission link copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy link. Please try again.');
            });
        }

        @if(session('copy_to_clipboard'))
            // Automatically copy to clipboard on page load if we have a URL to copy
            document.addEventListener('DOMContentLoaded', function() {
                const urlToCopy = @json(session('copy_to_clipboard'));
                navigator.clipboard.writeText(urlToCopy).then(function() {
                    console.log('Hour submission link automatically copied to clipboard!');
                }, function(err) {
                    console.error('Could not automatically copy text: ', err);
                });
            });
        @endif
    </script>
</x-app-layout>
