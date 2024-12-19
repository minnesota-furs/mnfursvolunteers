<x-app-layout>

    @auth

        <x-slot name="header">
            {{ __('View All Departments') }}
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="text-base font-semibold leading-6 text-gray-900">Departments</h1>
                        </div>
                        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                            @if( Auth::user()->isAdmin() )
                                <a href="{{ route('departments.create') }}" type="button"
                                    class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add
                                    department</a>
                            @endif
                        </div>
                    </div>
                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                {{-- {{ $sectors->links() }} --}}
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                                Name</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                                Sector</th>
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
                                {{-- {{ $sectors->links() }} --}}
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
