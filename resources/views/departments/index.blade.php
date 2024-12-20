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
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="text-base font-semibold leading-6 text-gray-900">Departments</h1>
                        </div>
                        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                            <form method="GET" action="{{ route('departments.index') }}" class="mb-4">
                                <div class="flex items-center gap-2">
                                    <label for="sector" class="text-sm font-medium text-gray-700">Filter by Sector:</label>
                                    <select name="sector" id="sector" class="border rounded-md px-3 py-2 text-xs">
                                        <option value="">All Sectors</option>
                                        @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}" {{ $selectedSector == $sector->id ? 'selected' : '' }}>
                                                {{ $sector->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded-md hover:bg-blue-600">
                                        Filter
                                    </button>
                                    <a href="{{ route('departments.index') }}" class="text-gray-500 hover:underline">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                {{ $departments->links() }}
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
                                                Dept Head</th>
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
                                                <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                                    {{$department->head->name ?? ''}}
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
                                {{ $departments->links() }}
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
