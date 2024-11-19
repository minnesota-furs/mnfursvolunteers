<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Volunteer: ') }}{{ $user->name }}
        </x-slot>

        <x-slot name="actions">
            {{-- <button type="button"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Log Hours
            </button> --}}
            @if( Auth::user()->isAdmin() )
                <a href="{{route('users.edit', $user->id)}}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Edit
                </a>
                <a href="{{route('users.delete_confirm', $user->id)}}"
                    class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-s-trash class="w-4 inline"/> Delete
                </a>
            @endif
        </x-slot>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-4 gap-4">
                    <div class="col-span-4 md:col-span-2">
                        {{-- Start Left Column --}}
                        <div>
                            <div class="px-4 sm:px-0">
                                <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Volunteer / User Information</h3>
                            </div>
                            <div class="mt-6 border-t border-gray-100">
                                <dl class="divide-y divide-gray-100">
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Name / Alias</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ $user->name }}</dd>
                                    </div>
                                    @if (Auth::user()->isAdmin())
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Legal First Name</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ $user->first_name ?? '-'  }}</dd>
                                    </div>
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Legal Last Name</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{ $user->last_name ?? '-' }}</dd>
                                    </div>
                                    @endif
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Email address</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            @if (Auth::user()->isAdmin())
                                                {{ $user->email }}
                                            @else
                                                ******
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Status</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            @if ($user->active == true)
                                                <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-800 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-100 ring-1 ring-inset ring-green-600/20">Active</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-yellow-50 dark:bg-yellow-800 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-100 ring-1 ring-inset ring-yellow-600/20">Inactive</span>
                                            @endif
                                            @if ($user->isAdmin() == true)
                                                <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-800 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-100 ring-1 ring-inset ring-red-600/20">Admin</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Notes</dt>
                                        @if($user->hasNotes())
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ $user->notes }}
                                        @else
                                        <dd class="mt-1 text-sm leading-6 text-gray-300 dark:text-gray-700 sm:col-span-2 sm:mt-0">
                                            No Notes recorded...
                                        @endif

                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        {{-- Start Right Column --}}
                        <div>
                            <div class="px-4 sm:px-0">
                                <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Role Information</h3>
                                {{-- <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Information involving their staff involvement with the group</p> --}}
                            </div>
                            <div class="mt-6 border-t border-gray-100">
                                <dl class="divide-y divide-gray-100">
                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Primary Sector</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">{{$user->sector->name ?? '-'}}</dd>
                                    </div>

                                    {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Primary Dept</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            @if($user->department)
                                                <a href="{{ route('departments.show', $user->department->id) }}" class="text-blue-600">{{$user->department->name}}</a>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div> --}}

                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Departments ({{$user->departments->count()}})</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">




                                            <ul role="list" class="divide-y divide-gray-200">
                                                @forelse ($user->departments as $department)
                                                    <li class="relative bg-white px-1 py-1 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 hover:bg-gray-50">
                                                    <div class="flex justify-between space-x-3">
                                                        <div class="min-w-0 flex-1">
                                                        <a href="{{ route('departments.show', $department->id) }}" class="block focus:outline-none">
                                                            <span class="absolute inset-0" aria-hidden="true"></span>
                                                            <p class="truncate text-sm font-medium text-gray-900">{{ $department->name }}</p>
                                                            <p class="truncate text-sm text-gray-500">{{ $department->sector->name }}</p>
                                                        </a>
                                                        </div>
                                                        {{-- <time datetime="2021-01-27T16:35" class="shrink-0 whitespace-nowrap text-sm text-gray-500">{{$department->created_at}}</time> --}}
                                                    </div>
                                                    {{-- <div class="mt-1">
                                                        <p class="line-clamp-2 text-sm text-gray-600">Doloremque dolorem maiores assumenda dolorem facilis. Velit vel in a rerum natus facere. Enim rerum eaque qui facilis. Numquam laudantium sed id dolores omnis in. Eos reiciendis deserunt maiores et accusamus quod dolor.</p>
                                                    </div> --}}
                                                    </li>
                                                @empty
                                                    No Departments Assigned
                                                @endforelse
                                                <!-- More messages... -->
                                              </ul>
                                        </dd>
                                    </div>








                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">This Fiscal</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{format_hours($user->totalHoursForCurrentFiscalLedger())}} hours
                                        </dd>
                                    </div>

                                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Lifetime Hours</dt>
                                        <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                            {{format_hours($user->totalVolunteerHours())}} hours
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 border-t pt-6">
                    <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Hour Log</h1>
                        <p class="mt-2 text-sm text-gray-700 dark:text-white">Transactional log of recently logged hours for this volunteer/user.</p>
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        @if(Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                            <a href="{{ route('hours.create', ['user' => $user->id]) }}" class="block rounded-md bg-brand-green px-2 py-1 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                <x-heroicon-m-clock class="w-4 inline"/> New Hour Log
                            </a>
                        @endif
                    </div>
                    </div>
                    <div class="mt-8 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Short Description</th>
                                <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 hidden md:table-cell">Sector, Department</th>
                                <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-32">Amount</th>
                                <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-32 hidden sm:table-cell">Task Date</th>
                                <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 w-16 hidden sm:table-cell">Notes</th>
                                <th scope="col" class="relative w-16 whitespace-nowrap py-3.5 pl-3 pr-4 sm:pr-0">
                                <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @forelse ($volunteerHours as $volunteerHour)
                            <tr class="even:bg-gray-50">
                                <td class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                                    <a href="{{route('hours.show', $volunteerHour->id)}}" class="text-slate-500">
                                    @if ($volunteerHour->description)
                                        {{$volunteerHour->description}}
                                    @else
                                        <span class="text-xs text-gray-300">Description Not Provided</span>
                                    @endif
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-400 dark:text-gray-300 hidden md:table-cell">
                                    @if ($volunteerHour->hasDepartment())
                                        {{$volunteerHour->department->sector->name ?? '-'}} / {{$volunteerHour->department->name ?? ''}}
                                    @else
                                        <span class="text-xs text-gray-300">Department Not Provided</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300" title="{{$volunteerHour->fiscalLedger->name ?? '???'}}">
                                    {{format_hours($volunteerHour->hours)}} hrs
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300 hidden sm:table-cell">
                                    @if(isset($volunteerHour->volunteer_date))
                                        {{$volunteerHour->volunteer_date->diffForHumans() ?? '-'}}
                                    @else
                                        <span class="text-xs text-gray-300">Date not logged</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-300 hidden sm:table-cell">
                                    @if($volunteerHour->hasNotes())
                                        <x-heroicon-o-check title="{{$volunteerHour->notes ?? ''}}" class="w-4"/>
                                    @else
                                        <span class="text-xs text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                    <a href="{{route('hours.show', $volunteerHour->id)}}" class="text-blue-400 hover:text-blue-500 px-1">View<span class="sr-only"></span></a>
                                    @if(Auth::user()->isAdmin() || Auth::user()->id == $volunteerHour->user_id)
                                        <a href="{{route('hours.edit', $volunteerHour->id)}}" class="text-blue-400 hover:text-blue-500 px-1">Edit<span class="sr-only"></span></a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="6">No hours logged for this user...</td>
                            </tr>
                            @endforelse
                            </tbody>
                        </table>
                        {{ $volunteerHours->links('components.compact-pagination') }}
                        {{-- {{ $volunteerHours->links() }} --}}
                        </div>
                    </div>
                    </div>

            </div>
        </div>
    @endauth
</x-app-layout>
