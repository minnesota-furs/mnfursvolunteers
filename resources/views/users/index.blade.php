<x-app-layout>
    @auth
        @section('title', 'Users - Show all')
        <x-slot name="header">
            {{ __('All Users') }}
        </x-slot>

        <x-slot name="actions">
            @can('manage-users')
                <!-- Bulk Actions Dropdown - Only shown when users are selected -->
                <div x-data="{ open: false }" 
                    x-show="$store.bulkUsers && $store.bulkUsers.count > 0" 
                    x-cloak
                    class="relative">
                    <button @click="open = !open"
                        class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-emerald-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-s-queue-list class="w-4 inline"/> Bulk Actions (<span x-text="$store.bulkUsers.count"></span>)
                    </button>
                    <div x-show="open" 
                        @click.away="open = false"
                        x-transition
                        class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div class="py-1">
                            <button @click="open = false; $dispatch('open-modal', 'bulk-log-hours')"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <x-heroicon-o-clock class="w-4 inline"/> Log Hours
                            </button>
                            <button @click="open = false; $dispatch('open-modal', 'bulk-add-tags')"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <x-heroicon-o-tag class="w-4 inline"/> Add Tags
                            </button>
                            <button @click="open = false; $dispatch('open-modal', 'bulk-remove-tags')"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <x-heroicon-o-minus-circle class="w-4 inline"/> Remove Tags
                            </button>
                            <button @click="open = false; $dispatch('open-modal', 'bulk-assign-department')"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <x-heroicon-o-building-office class="w-4 inline"/> Assign Department
                            </button>
                        </div>
                    </div>
                </div>

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
                            <table class="min-w-full divide-y divide-gray-300" 
                                x-data="{
                                    selectAll: false,
                                    toggleAll() {
                                        const checkboxes = $el.querySelectorAll('tbody input[type=checkbox]');
                                        checkboxes.forEach(cb => {
                                            const userId = parseInt(cb.value);
                                            $store.bulkUsers.selectedIds = $store.bulkUsers.selectedIds || [];
                                            if (this.selectAll) {
                                                if (!$store.bulkUsers.selectedIds.includes(userId)) {
                                                    $store.bulkUsers.selectedIds.push(userId);
                                                }
                                            } else {
                                                $store.bulkUsers.selectedIds = $store.bulkUsers.selectedIds.filter(id => id !== userId);
                                            }
                                        });
                                    }
                                }">
                                <thead>
                                <tr>
                                    @can('manage-users')
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white w-12">
                                        <input type="checkbox" 
                                            x-model="selectAll"
                                            @change="toggleAll()"
                                            class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700"
                                            title="Select all users on this page">
                                    </th>
                                    @endcan
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-0 w-64">
                                        <x-sortable-column column="name" label="Name" :sort="$sort" :direction="$direction" route="users.index" />
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden sm:table-cell">Sector/Dept</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32 hidden md:table-cell">
                                        <x-sortable-column column="created_at" label="Created" :sort="$sort" :direction="$direction" route="users.index" />
                                    </th>
                                    @if(app_setting('feature_user_tags', false))
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white w-32 hidden md:table-cell">
                                        Tags
                                    </th>
                                    @endif
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
                                    @can('manage-users')
                                    <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm w-12">
                                        <input type="checkbox" 
                                            :checked="$store.bulkUsers.selectedIds.includes({{ $user->id }})"
                                            @change="$store.bulkUsers.toggle({{ $user->id }})"
                                            value="{{ $user->id }}"
                                            class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700"
                                            title="Select {{ $user->name }}">
                                    </td>
                                    @endcan
                                    <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                    <div class="flex items-center">
                                        <a href="{{route('users.show', $user->id)}}">
                                        {{-- Removing until proper icons implemented --}}
                                        {{-- <div class="h-11 w-11 flex-shrink-0">
                                        <img class="h-11 w-11 rounded-full" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                        </div> --}}
                                        <div class="ml-4">
                                        <div class="font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                            {{$user->name}}
                                            @if($user->active)
                                                <span class="inline-block w-2 h-2 rounded-full bg-green-500" title="Active"></span>
                                            @else
                                                <span class="inline-block w-2 h-2 rounded-full bg-red-500" title="Inactive"></span>
                                            @endif
                                        </div>
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
                                    @if(app_setting('feature_user_tags', false))
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                        <div class="flex flex-wrap gap-1">
                                        @forelse($user->tags as $tag)
                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{$tag->name}}</span>
                                        @empty
                                            <span class="text-xs text-gray-400">—</span>
                                        @endforelse
                                        </div>
                                    </td>
                                    @endif
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

<!-- Bulk Operations Modals -->
@can('manage-users')
<!-- Bulk Log Hours Modal -->
<x-modal name="bulk-log-hours" :show="false" focusable>
    <form method="POST" action="{{ route('admin.users.bulk-log-hours') }}" class="p-6">
        @csrf
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Log Hours for Selected Users
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Add volunteer hours for <span x-text="$store.bulkUsers.count"></span> selected user(s).
        </p>

        <input type="hidden" name="user_ids" x-bind:value="$store.bulkUsers.idsString">

        <div class="mt-6 space-y-4">
            <div>
                <x-input-label for="bulk_hours" value="Hours" />
                <x-text-input id="bulk_hours" name="hours" type="number" step="0.5" min="0" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->get('hours')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="bulk_date" value="Date" />
                <x-text-input id="bulk_date" name="date" type="date" class="mt-1 block w-full" :value="date('Y-m-d')" required />
                <x-input-error :messages="$errors->get('date')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="bulk_department" value="Department" />
                <select id="bulk_department" name="department_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-green dark:focus:border-brand-green focus:ring-brand-green dark:focus:ring-brand-green rounded-md shadow-sm" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->sector->name }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="bulk_description" value="Description (Optional)" />
                <x-textarea-input id="bulk_description" name="description" class="mt-1 block w-full" rows="3"></x-textarea-input>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancel
            </x-secondary-button>
            <x-primary-button>
                Log Hours
            </x-primary-button>
        </div>
    </form>
</x-modal>

<!-- Bulk Add Tags Modal -->
<x-modal name="bulk-add-tags" :show="false" focusable>
    <form method="POST" action="{{ route('admin.users.bulk-add-tags') }}" class="p-6">
        @csrf
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Add Tags to Selected Users
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Add tags to <span x-text="$store.bulkUsers.count"></span> selected user(s).
        </p>

        <input type="hidden" name="user_ids" x-bind:value="$store.bulkUsers.idsString">

        <div class="mt-6">
            <x-input-label value="Select Tags to Add" />
            <div class="mt-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3 space-y-2">
                @foreach($tags as $tag)
                    <label class="flex items-center">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" 
                            class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancel
            </x-secondary-button>
            <x-primary-button>
                Add Tags
            </x-primary-button>
        </div>
    </form>
</x-modal>

<!-- Bulk Remove Tags Modal -->
<x-modal name="bulk-remove-tags" :show="false" focusable>
    <form method="POST" action="{{ route('admin.users.bulk-remove-tags') }}" class="p-6">
        @csrf
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Remove Tags from Selected Users
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Remove tags from <span x-text="$store.bulkUsers.count"></span> selected user(s).
        </p>

        <input type="hidden" name="user_ids" x-bind:value="$store.bulkUsers.idsString">

        <div class="mt-6">
            <x-input-label value="Select Tags to Remove" />
            <div class="mt-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3 space-y-2">
                @foreach($tags as $tag)
                    <label class="flex items-center">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" 
                            class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancel
            </x-secondary-button>
            <x-primary-button>
                Remove Tags
            </x-primary-button>
        </div>
    </form>
</x-modal>

<!-- Bulk Assign Department Modal -->
<x-modal name="bulk-assign-department" :show="false" focusable>
    <form method="POST" action="{{ route('admin.users.bulk-assign-department') }}" class="p-6">
        @csrf
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Assign Department to Selected Users
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Add department to <span x-text="$store.bulkUsers.count"></span> selected user(s).
        </p>

        <input type="hidden" name="user_ids" x-bind:value="$store.bulkUsers.idsString">

        <div class="mt-6">
            <x-input-label for="bulk_dept_assign" value="Select Department" />
            <select id="bulk_dept_assign" name="department_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-green dark:focus:border-brand-green focus:ring-brand-green dark:focus:ring-brand-green rounded-md shadow-sm" required>
                <option value="">Select Department</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->sector->name }})</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">
                Cancel
            </x-secondary-button>
            <x-primary-button>
                Assign Department
            </x-primary-button>
        </div>
    </form>
</x-modal>
@endcan


<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('bulkUsers', {
        selectedIds: [],
        
        get count() {
            return this.selectedIds.length;
        },
        
        get idsString() {
            return this.selectedIds.join(',');
        },
        
        toggle(userId) {
            const index = this.selectedIds.indexOf(userId);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(userId);
            }
        },
        
        clear() {
            this.selectedIds = [];
        }
    });
});
</script>
