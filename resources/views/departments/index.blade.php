<x-app-layout>

    @auth

        <x-slot name="header">
            {{ __('View All Departments') }}
        </x-slot>

        <x-slot name="actions">
            @if( Auth::user()->isAdmin() )
                <a href="{{route('departments.create')}}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-s-plus class="w-4 inline"/> Create Department
                </a>
            @endif
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div class="sm:flex-auto">
                            <h1 class="text-base font-semibold leading-6 text-gray-900">Departments</h1>
                        </div>
                    </div>
                    
                    <!-- Filter Section -->
                    <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <form method="GET" action="{{ route('departments.index') }}" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                            <!-- Search Input -->
                            <div class="flex-1 w-full sm:w-auto">
                                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <x-heroicon-o-magnifying-glass class="w-4 h-4 inline mb-0.5" /> Search
                                </label>
                                <input 
                                    type="text" 
                                    name="search" 
                                    id="search" 
                                    value="{{ request('search') }}"
                                    placeholder="Search departments..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                            </div>
                            
                            <!-- Sector Filter -->
                            <div class="flex-1 w-full sm:w-auto">
                                <label for="sector" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <x-heroicon-o-funnel class="w-4 h-4 inline mb-0.5" /> Sector
                                </label>
                                <select 
                                    name="sector" 
                                    id="sector" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="">All Sectors</option>
                                    @foreach ($sectors as $sector)
                                        <option value="{{ $sector->id }}" {{ $selectedSector == $sector->id ? 'selected' : '' }}>
                                            {{ $sector->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-brand-green text-white text-sm font-medium rounded-md hover:bg-brand-green/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-green"
                                >
                                    <x-heroicon-o-magnifying-glass class="w-4 h-4 mr-1.5" />
                                    Apply
                                </button>
                                @if(request()->hasAny(['search', 'sector']))
                                    <a 
                                        href="{{ route('departments.index') }}" 
                                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
                                    >
                                        <x-heroicon-o-x-mark class="w-4 h-4 mr-1.5" />
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                {{ $departments->links('vendor.pagination.custom') }}
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                                <x-sortable-column column="name" label="Name" :sort="$sort" :direction="$direction" route="departments.index" /></th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                                <x-sortable-column column="sector_id" label="Sector" :sort="$sort" :direction="$direction" route="departments.index" /></th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                                Department Heads</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                                Staff Count</th>
                                            {{-- <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                                <span class="sr-only">Edit</span>
                                            </th> --}}
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr>
                                            <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                Unassigned
                                            </td>
                                            <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                -
                                            </td>
                                            <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                -
                                            </td>
                                            <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                -
                                            </td>
                                            {{-- <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                            </td> --}}
                                        </tr>
                                        @forelse ($departments as $department)
                                            <tr>
                                                <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                    <a class="text-blue-700"
                                                        href="{{ route('departments.show', $department->id) }}">{{ $department->name }}</a>
                                                </td>
                                                <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                    <p>{{ $sectors->where('id', $department->sector_id)->first()->name }}</p>
                                                </td>
                                                <td class="py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                    @if($department->heads->count() > 0)
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach($department->heads as $head)
                                                                <a href="{{ route('users.show', $head->id) }}" 
                                                                   class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 hover:bg-blue-100">
                                                                    {{ $head->name }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                    {{$department->userCount() ?? '-'}}
                                                </td>
                                                {{-- <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                </td> --}}
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center"
                                                    colspan="4">
                                                    <p class="">No departments found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                {{ $departments->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <x-slot name="right">
            <h1 class="text-base font-semibold leading-6 text-gray-900">What is a Department?</h1>
            <p class="py-4 text-justify">A Department is a specialized team or functional unit within an organization that
                operates under a specific <a class="text-blue-500" href="{{ route('sectors.index') }}">Sector</a>. Each department is responsible for managing key activities or tasks
                related to its area of expertise. Departments typically focus on more granular aspects of the sector’s
                overall mission, providing targeted support and handling day-to-day operations that contribute to the
                sector’s objectives.</p>
        </x-slot>

    @endauth
</x-app-layout>
