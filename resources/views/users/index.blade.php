<x-app-layout>
    @auth
        @section('title', 'Users - Show all')
        <x-slot name="header">
            {{ __('All Users') }}
        </x-slot>

        <x-slot name="actions">
            {{-- <button type="button"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Log Hours
            </button> --}}
            @can('manage-users')
                <button
                    class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'show-trashed')">
                <x-heroicon-o-trash class="w-4 inline"/> Show Trash</button>
                <a href="{{route('users.export')}}"
                    class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-s-arrow-down-on-square-stack class="w-4 inline"/> Export CSV
                </a>
                <a href="{{route('users.import')}}"
                    class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-s-cloud-arrow-up class="w-4 inline"/> Import
                </a>
                <a href="{{route('users.create')}}"
                    class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-s-user class="w-4 inline"/> Create New User
                </a>
            @endcan
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            @if(null !==request('page'))
                                <p class="mt-2 text-sm text-orange-700"><x-heroicon-s-magnifying-glass class="w-4 inline"/> Currently showing <span class="underline">Page #{{$users->currentPage()}}</span> of {{$users->lastPage()}}.
                            @endif
                            @if(null !==request('search'))
                                <p class="mt-2 text-sm text-orange-700"><x-heroicon-s-magnifying-glass class="w-4 inline"/> Currently showing {{count($users)}} result(s) for search term: <span class="underline">{{request('search')}}</span>.
                                <a class="text-blue-600" href="{{route('users.index')}}">Clear Search</a>
                            @endif
                            @if(request()->hasAny(['departments', 'tags', 'status', 'department_status']))
                                <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                                    <x-heroicon-s-funnel class="w-4 inline"/> Filters active. 
                                    <a class="underline hover:text-blue-800" href="{{route('users.index')}}">Clear all filters</a>
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Filter Controls -->
                    <div class="mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm" x-data="{
                        departmentSearch: '',
                        tagSearch: '',
                        expanded: {{ request()->hasAny(['departments', 'tags', 'status', 'department_status']) ? 'true' : 'false' }}
                    }">
                        <div class="flex items-center justify-between px-4 py-1">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                                <x-heroicon-o-funnel class="w-4 h-4 mr-2 text-brand-green" />
                                Filter Users
                                @if(request()->hasAny(['departments', 'tags', 'status', 'department_status']))
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Active
                                    </span>
                                @endif
                            </h3>
                            <button @click="expanded = !expanded" 
                                    type="button"
                                    class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                <span x-text="expanded ? 'Hide' : 'Show'"></span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-200" 
                                                           ::class="expanded ? 'rotate-180' : ''" />
                            </button>
                        </div>

                        <form method="GET" action="{{ route('users.index') }}" id="filterForm" 
                              x-show="expanded" 
                              x-transition:enter="transition ease-out duration-200"
                              x-transition:enter-start="opacity-0 -translate-y-1"
                              x-transition:enter-end="opacity-100 translate-y-0"
                              x-transition:leave="transition ease-in duration-150"
                              x-transition:leave-start="opacity-100 translate-y-0"
                              x-transition:leave-end="opacity-0 -translate-y-1">
                            <div class="px-4 pb-4 space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex items-center justify-end gap-2 mb-4">
                                    @if(request()->hasAny(['departments', 'tags', 'status', 'department_status']))
                                        <a href="{{route('users.index')}}" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                            Clear all filters
                                        </a>
                                    @endif
                                    <button type="submit" class="px-4 py-2 text-sm font-medium rounded-md bg-brand-green text-white hover:bg-emerald-600">
                                        Apply Filters
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Departments Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Departments
                                    </label>
                                    <div class="space-y-2">
                                        <input type="text" 
                                               x-model="departmentSearch" 
                                               placeholder="Search departments..." 
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                        <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-2 space-y-1">
                                            @foreach($departments as $dept)
                                                <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer"
                                                       x-show="departmentSearch === '' || '{{ strtolower($dept->name . ' ' . $dept->sector->name) }}'.includes(departmentSearch.toLowerCase())">
                                                    <input type="checkbox" 
                                                           name="departments[]" 
                                                           value="{{ $dept->id }}"
                                                           {{ in_array($dept->id, (array)request('departments', [])) ? 'checked' : '' }}
                                                           class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                        {{ $dept->name }} <span class="text-gray-500">({{ $dept->sector->name }})</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @if(count((array)request('departments', [])) > 0)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ count((array)request('departments', [])) }} department(s) selected
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tags Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tags
                                    </label>
                                    <div class="space-y-2">
                                        <input type="text" 
                                               x-model="tagSearch" 
                                               placeholder="Search tags..." 
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                        <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-2 space-y-1">
                                            @foreach($tags as $tag)
                                                <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer"
                                                       x-show="tagSearch === '' || '{{ strtolower($tag->name) }}'.includes(tagSearch.toLowerCase())">
                                                    <input type="checkbox" 
                                                           name="tags[]" 
                                                           value="{{ $tag->id }}"
                                                           {{ in_array($tag->id, (array)request('tags', [])) ? 'checked' : '' }}
                                                           class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $tag->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @if(count((array)request('tags', [])) > 0)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ count((array)request('tags', [])) }} tag(s) selected
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Filters Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <!-- Department Status Filter -->
                                <div>
                                    <label for="department_status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Department Status
                                    </label>
                                    <select name="department_status" id="department_status" 
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                        <option value="">Any</option>
                                        <option value="has_department" {{ request('department_status') === 'has_department' ? 'selected' : '' }}>
                                            Has Department
                                        </option>
                                        <option value="no_department" {{ request('department_status') === 'no_department' ? 'selected' : '' }}>
                                            No Department
                                        </option>
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label for="status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status
                                    </label>
                                    <select name="status" id="status" 
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Preserve sort parameters -->
                            @if(request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif
                            @if(request('direction'))
                                <input type="hidden" name="direction" value="{{ request('direction') }}">
                            @endif
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            </div>
                        </form>
                    </div>

                    <div class="mt-8 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{ $users->appends(request()->except('page'))->links('vendor.pagination.custom') }}
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0 w-64">
                                        <x-sortable-column column="name" label="Name" :sort="$sort" :direction="$direction" route="users.index" />
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden sm:table-cell">Sector/Dept</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32 hidden md:table-cell">
                                        <x-sortable-column column="created_at" label="Created" :sort="$sort" :direction="$direction" route="users.index" />
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32 hidden md:table-cell">
                                        <x-sortable-column column="active" label="Status" :sort="$sort" :direction="$direction" route="users.index" />
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-16">
                                        <x-sortable-column column="hours" label="Hours" :sort="$sort" :direction="$direction" route="users.index" />
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
                                        @can('manage-users')
                                            <a href="{{route('users.edit', $user->id)}}" class="text-blue-600 hover:text-blue-800 px-2">Edit<span class="sr-only">, {{$user->name}}</span></a>
                                        @endcan
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

<x-modal name="show-trashed" class="p-6" focusable>
    <div class="p-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Trashed Users') }}</h2>
      <div class="px-4 sm:px-6 lg:px-8">
  
        <div class="mt-8 flow-root">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
              <table class="min-w-full divide-y divide-gray-300">
                <thead>
                  <tr>
                    <th scope="col" class="whitespace-nowrap py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">ID</th>
                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Title</th>
                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900">Deleted</th>
                    <th scope="col" class="relative whitespace-nowrap py-3.5 pl-3 pr-4 sm:pr-0">
                      <span class="sr-only">Edit</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                  @forelse($trashedUsers as $trashed)
                  <tr>
                    <td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm text-gray-500 sm:pl-0">{{$trashed->name}}</td>
                    <td class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-900">{{$trashed->email}}</td>
                    <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-900">{{$trashed->deleted_at->diffForHumans()}}</td>
                    <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                      <a href="#" class="text-indigo-600 hover:text-indigo-900">
                        <form action="{{ route('users.restore', $trashed->id) }}" method="POST" class="inline">
                          @csrf
                          <button type="submit" class="text-blue-600 hover:underline">Restore</button>
                      </form>
                      </a>
                    </td>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center text-sm text-gray-500 py-4">Nothing Deleted Here!</td></tr>
                    @endforelse
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    <div class="mt-6 flex justify-end">
        <x-secondary-button x-on:click="$dispatch('close')">
            {{ __('Close') }}
        </x-secondary-button>
    </div>
  </div>
  </x-modal>