<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Report: ' . $reportTitle) }}
        </x-slot>

        <x-slot name="actions">
            
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">{{$reportTitle}}</h1>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">{{$reportDescription}}</p>
                            @if(null !==request('page'))
                                <p class="mt-2 text-sm text-orange-700"><x-heroicon-s-magnifying-glass class="w-4 inline"/> Currently showing <span class="underline">Page #{{$users->currentPage()}}</span> of {{$users->lastPage()}}.
                            @endif
                            @if(null !==request('search'))
                                <p class="mt-2 text-sm text-orange-700"><x-heroicon-s-magnifying-glass class="w-4 inline"/> Currently showing {{count($users)}} result(s) for search term: <span class="underline">{{request('search')}}</span>.
                                <a class="text-blue-600" href="{{route('users.index')}}">Clear Search</a>
                            @endif
                        </div>
                        {{-- @if( Auth::user()->isAdmin() )
                            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                                <a href="{{route('users.create')}}" type="button" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add user</a>
                            </div>
                        @endif --}}
                    </div>
                    <div class="mt-8 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{ $users->appends(request()->except('page'))->links('vendor.pagination.custom') }}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0 w-64">
                                        <x-sortable-column column="name" label="Name" :sort="$sort" :direction="$direction" route="{{ Route::currentRouteName() }}" />
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden sm:table-cell">Sector/Dept</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32 hidden md:table-cell">
                                        <x-sortable-column column="created_at" label="Created" :sort="$sort" :direction="$direction" route="{{ Route::currentRouteName() }}" />
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32 hidden md:table-cell">
                                        <x-sortable-column column="active" label="Status" :sort="$sort" :direction="$direction" route="{{ Route::currentRouteName() }}" />
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-16">
                                        <x-sortable-column column="hours" label="Hours" :sort="$sort" :direction="$direction" route="{{ Route::currentRouteName() }}" />
                                    </th>
                                    {{-- <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th> --}}
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-16">
                                    <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                @forelse ($users as $user)
                                <tr>
                                    <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                    <div class="flex items-center">
                                        <a href="{{route('users.show', $user->id)}}">
                                        {{-- Removing until proper icons implemented --}}
                                        {{-- <div class="h-11 w-11 flex-shrink-0">
                                        <img class="h-11 w-11 rounded-full" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                        </div> --}}
                                        <div class="ml-4">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{$user->name}}</div>
                                        </a>
                                        @if(Auth::user()->isAdmin())
                                            <div class="mt-1 text-xs text-gray-500">{{$user->first_name}} {{$user->last_name}}</div>
                                            <div class="mt-1 text-xs text-gray-500">{{$user->email}}</div>
                                        @else
                                            <div class="mt-1 text-xs text-gray-300">Email Not Visible</div>
                                        @endif
                                        </div>
                                    </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 hidden sm:table-cell">
                                        <div class="flex flex-wrap gap-2">
                                        @forelse($user->departments as $department)
                                            <span class="dept-badge inline-flex items-center">{{$department->name}} ({{$department->sector->name}})</span>
                                        @empty
                                        <span class="inline-flex text-xs items-center">No Department(s) Assigned</span>
                                        @endforelse
                                        </div>
                                        {{-- <div class="text-gray-900">{{$user->sector->name ?? '-'}}</div>
                                        <div class="mt-1 text-gray-500">{{$user->department->name ?? '-'}}</div> --}}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-xs text-gray-500 hidden md:table-cell">
                                        {{$user->created_at->diffForHumans()}}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 hidden md:table-cell">
                                        @if($user->active)
                                        <span class="active-badge">Active</span>
                                        @else
                                        <span class="inactive-badge">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        @if( Auth::user()->isAdmin() || Auth::user()->id == $user->id)
                                            <div class="text-gray-900 dark:text-gray-100 font-black">{{format_hours($user->totalHoursForCurrentFiscalLedger())}}</div>
                                            <div class="mt-1 text-gray-400 text-xs">{{format_hours($user->totalVolunteerHours())}}</div>
                                        @else
                                            @if($user->totalHoursForCurrentFiscalLedger() == 0)
                                                <div class="mt-1 text-gray-400 text-xs">No Hours Logged</div>
                                            @else
                                                <div class="text-gray-900">> 0 Hours</div>
                                            @endif
                                        @endif
                                    </td>
                                    {{-- <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500"> | </td> --}}
                                    <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <a href="{{route('users.show', $user->id)}}" class="text-blue-600 hover:text-blue-800 px-2">View<span class="sr-only">, {{$user->name}}</span></a>
                                        @if( Auth::user()->isAdmin() )
                                            <a href="{{route('users.edit', $user->id)}}" class="text-blue-600 hover:text-blue-800 px-2">Edit<span class="sr-only">, {{$user->name}}</span></a>
                                        @endif
                                        {{-- <x-tailwind-dropdown id="{{$user->id}}">
                                            <div class="py-1" role="none">
                                                <x-tailwind-dropdown-item title="Duplicate" href="#"/>
                                                <x-tailwind-dropdown-item title="Bookmark" href="#"/>
                                            </div>
                                            <div class="py-1" role="none">
                                                <x-tailwind-dropdown-item title="Add Hours" href="#"/>
                                            </div>
                                            <div class="py-1" role="none">
                                                <x-tailwind-dropdown-item title="Delete" href="#" class="hover:bg-red-50 text-red-900" />
                                            </div>
                                            </div>
                                        </x-tailwind-dropdown> --}}
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 text-center" colspan="3">No users found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $users->appends(request()->except('page'))->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                    </div>
                </div>

            </div>
        </div>
    @endauth
</x-app-layout>
