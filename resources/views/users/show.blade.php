<x-app-layout>
    @auth
        @section('title', 'Users - View ' . $user->name)
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if($user->active)
                            <span class="inline-flex items-center rounded-full bg-white/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-white ring-1 ring-inset ring-white/30">
                                <svg class="mr-1.5 h-1.5 w-1.5 fill-current" viewBox="0 0 6 6" aria-hidden="true">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-yellow-500/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-yellow-100 ring-1 ring-inset ring-yellow-400/30">
                                <svg class="mr-1.5 h-1.5 w-1.5 fill-current" viewBox="0 0 6 6" aria-hidden="true">
                                    <circle cx="3" cy="3" r="3" />
                                </svg>
                                Inactive
                            </span>
                        @endif
                        @if($user->isAdmin())
                            <span class="inline-flex items-center rounded-full bg-red-500/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-red-100 ring-1 ring-inset ring-red-400/30">
                                <x-heroicon-s-shield-check class="mr-1.5 h-3 w-3" />
                                Administrator
                            </span>
                        @endif
                        @if($user->vol_code)
                            <span class="inline-flex items-center rounded-full bg-blue-500/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-blue-100 ring-1 ring-inset ring-blue-400/30">
                                <x-heroicon-o-identification class="mr-1.5 h-3 w-3" />
                                {{ $user->vol_code }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="actions">
            @if (Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                <a href="{{ route('hours.create', ['user' => $user->id]) }}"
                    class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                    <x-heroicon-m-clock class="mr-2 h-4 w-4" />
                    Log Hours
                </a>
            @endif
            @can('manage-users')
                <a href="{{ route('users.edit', $user->id) }}"
                    class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <x-heroicon-m-pencil class="mr-2 h-4 w-4" />
                    Edit
                </a>
            @endcan
            @if (Auth::user()->isAdmin())
                <a href="{{ route('users.permissions.edit', $user->id) }}"
                    class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <x-heroicon-m-key class="mr-2 h-4 w-4" />
                    Permissions
                </a>
            @endif
        </x-slot>

        {{-- Overview Cards --}}
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-clock class="h-8 w-8 text-green-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Current Fiscal Hours</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ format_hours($user->totalHoursForCurrentFiscalLedger()) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-chart-bar class="h-8 w-8 text-blue-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Hours</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ format_hours($user->totalVolunteerHours()) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-building-office class="h-8 w-8 text-purple-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Departments</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $user->departments->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <x-heroicon-o-calendar-days class="h-8 w-8 text-orange-600" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Shifts Signed</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $user->shifts->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Information Cards --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- User Information --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">User Information</h3>
                            <div class="mt-6 border-t border-gray-200 dark:border-gray-700">
                                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Display Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $user->name }}</dd>
                                    </div>
                                    @if (Auth::user()->isAdmin())
                                        <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Legal First Name</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $user->first_name ?: '—' }}</dd>
                                        </div>
                                        <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Legal Last Name</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $user->last_name ?: '—' }}</dd>
                                        </div>
                                    @endif
                                    <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                            @if (Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                                                {{ $user->email }}
                                            @else
                                                <span class="text-gray-400">Hidden for privacy</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $user->created_at->format('F j, Y') }}</dd>
                                    </div>
                                    @if($user->notes)
                                        <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">{{ $user->notes }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>

                    {{-- Role Information --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Role & Permissions</h3>
                            <div class="mt-6 border-t border-gray-200 dark:border-gray-700">
                                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Primary Sector</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                            {{ $user->sector->name ?? '—' }}
                                        </dd>
                                    </div>
                                    <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Departments</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                            @if($user->departments->count() > 0)
                                                <div class="space-y-1">
                                                    @foreach($user->departments as $department)
                                                        <div class="flex items-center justify-between">
                                                            <span>{{ $department->name }}</span>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $department->sector->name }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">No departments assigned</span>
                                            @endif
                                        </dd>
                                    </div>
                                    @if(Auth::user()->isAdmin())
                                        <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Permissions</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0">
                                                @if(!empty($user->permissions))
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($user->permissions as $permission)
                                                            <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-100 ring-1 ring-inset ring-blue-700/10">
                                                                {{ $permission }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic">No special permissions</span>
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            {{-- Audit Logs Section (Admin Only) --}}
            @if($auditLogs)
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 border-t pt-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Audit Log</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Record of all changes made to this user account</p>
                            
                            @if($auditLogs->count() > 0)
                                <div class="mt-6">
                                    <div class="flow-root">
                                        <ul role="list" class="-mb-8">
                                            @foreach($auditLogs as $index => $log)
                                                <li>
                                                    <div class="relative pb-8">
                                                        @if($index < $auditLogs->count() - 1)
                                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-600" aria-hidden="true"></span>
                                                        @endif
                                                        <div class="relative flex space-x-3">
                                                            <div>
                                                                @if($log->action === 'created')
                                                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                                        <x-heroicon-s-plus class="h-4 w-4 text-white" />
                                                                    </span>
                                                                @elseif($log->action === 'updated')
                                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                                        <x-heroicon-s-pencil class="h-4 w-4 text-white" />
                                                                    </span>
                                                                @elseif($log->action === 'deleted')
                                                                    <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                                        <x-heroicon-s-trash class="h-4 w-4 text-white" />
                                                                    </span>
                                                                @else
                                                                    <span class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                                        <x-heroicon-s-document class="h-4 w-4 text-white" />
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                                <div>
                                                                    <p class="text-sm text-gray-900 dark:text-white">
                                                                        User <span class="font-medium">{{ $log->action }}</span>
                                                                        @if($log->user)
                                                                            by {{ $log->user->name }}
                                                                        @endif
                                                                    </p>
                                                                    @if($log->changes && $log->action === 'updated')
                                                                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                                            @if(isset($log->changes['old']) && isset($log->changes['new']))
                                                                                <div class="space-y-1">
                                                                                    @foreach($log->changes['new'] as $field => $newValue)
                                                                                        <div class="text-xs">
                                                                                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                                                            <span class="text-red-600 dark:text-red-400">
                                                                                                @php
                                                                                                    $oldValue = $log->changes['old'][$field] ?? null;
                                                                                                    if (is_array($oldValue)) {
                                                                                                        echo '[' . implode(', ', $oldValue) . ']';
                                                                                                    } elseif (is_null($oldValue)) {
                                                                                                        echo 'null';
                                                                                                    } else {
                                                                                                        echo $oldValue;
                                                                                                    }
                                                                                                @endphp
                                                                                            </span>
                                                                                            →
                                                                                            <span class="text-green-600 dark:text-green-400">
                                                                                                @php
                                                                                                    if (is_array($newValue)) {
                                                                                                        echo '[' . implode(', ', $newValue) . ']';
                                                                                                    } elseif (is_null($newValue)) {
                                                                                                        echo 'null';
                                                                                                    } else {
                                                                                                        echo $newValue;
                                                                                                    }
                                                                                                @endphp
                                                                                            </span>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                                                    <time datetime="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</time>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    
                                    @if($auditLogs->hasPages())
                                        <div class="mt-6">
                                            {{ $auditLogs->links('components.compact-pagination') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="mt-6 text-center py-8">
                                    <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No audit logs</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No changes have been recorded for this user yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 border-t pt-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="sm:flex sm:items-center">
                            <div class="sm:flex-auto">
                                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Hour Log</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Recent volunteer hours logged for this user</p>
                            </div>
                            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                                @if (Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                                    <a href="{{ route('hours.create', ['user' => $user->id]) }}"
                                        class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                                        <x-heroicon-m-clock class="mr-2 h-4 w-4" />
                                        New Hour Log
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if($volunteerHours->count() > 0)
                            <div class="mt-8 flow-root">
                                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-900">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                        Description
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                                        Department
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                        Hours
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                                                        Date
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">
                                                        Notes
                                                    </th>
                                                    <th scope="col" class="relative px-6 py-3">
                                                        <span class="sr-only">Actions</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach ($volunteerHours as $volunteerHour)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                            <a href="{{ route('hours.show', $volunteerHour->id) }}" class="hover:text-brand-green">
                                                                {{ $volunteerHour->description ?: 'No description provided' }}
                                                            </a>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                                            @if ($volunteerHour->hasDepartment())
                                                                {{ $volunteerHour->department->name }}
                                                                <div class="text-xs text-gray-400">{{ $volunteerHour->department->sector->name }}</div>
                                                            @else
                                                                <span class="italic">Not specified</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100">
                                                                {{ format_hours($volunteerHour->hours) }} hrs
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                                            @if ($volunteerHour->volunteer_date)
                                                                {{ $volunteerHour->volunteer_date->format('M j, Y') }}
                                                                <div class="text-xs text-gray-400">{{ $volunteerHour->volunteer_date->diffForHumans() }}</div>
                                                            @else
                                                                <span class="italic">Not specified</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                                            @if ($volunteerHour->hasNotes())
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100" title="{{ $volunteerHour->notes }}">
                                                                    <x-heroicon-s-document-text class="h-3 w-3" />
                                                                </span>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                            <div class="flex justify-end gap-2">
                                                                <a href="{{ route('hours.show', $volunteerHour->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                                    View
                                                                </a>
                                                                @if (Auth::user()->isAdmin() || Auth::user()->id == $volunteerHour->user_id)
                                                                    <a href="{{ route('hours.edit', $volunteerHour->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                                        Edit
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                @if($volunteerHours->hasPages())
                                    <div class="mt-6">
                                        {{ $volunteerHours->links('components.compact-pagination') }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-8 text-center py-8">
                                <x-heroicon-o-clock class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hours logged</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user hasn't logged any volunteer hours yet.</p>
                                @if (Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                                    <div class="mt-6">
                                        <a href="{{ route('hours.create', ['user' => $user->id]) }}"
                                            class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                                            <x-heroicon-m-plus class="mr-2 h-4 w-4" />
                                            Log First Hour Entry
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            {{-- Volunteer Shift Signups --}}
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="sm:flex sm:items-center">
                            <div class="sm:flex-auto">
                                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Event Signups</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Volunteer shifts this user has signed up for</p>
                            </div>
                        </div>

                        @if($user->shifts->count() > 0)
                            <div class="mt-8 space-y-6">
                                @foreach ($user->shifts->groupBy('event.name') as $eventName => $shifts)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $eventName }}</h4>
                                        </div>
                                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($shifts as $shift)
                                                <div class="px-6 py-4 flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex items-center justify-between">
                                                            <div>
                                                                <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                                                                    {{ $shift->name ?: 'Unnamed Shift' }}
                                                                </h5>
                                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('M j, Y g:i A') }} - 
                                                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                                                                </p>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100">
                                                                    {{ \Carbon\Carbon::parse($shift->start_time)->diffForHumans(\Carbon\Carbon::parse($shift->end_time), true) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-8 text-center py-8">
                                <x-heroicon-o-calendar class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No event signups</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user hasn't signed up for any volunteer shifts yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endauth
    
</x-app-layout>
