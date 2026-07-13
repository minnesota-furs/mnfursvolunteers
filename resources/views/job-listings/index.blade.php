<x-app-layout>
  @section('title', 'Positions')
    <x-slot name="header">
        {{ __('Open Positions') }}
    </x-slot>

    <x-slot name="actions">
      @can('manage-staff-applications')
        @feature('job_applications')
        <a href="{{ route('job-listings.applicants') }}"
            class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-inbox class="w-4 inline"/> View Applications
        </a>
        @endfeature
      @endcan
      @can('manage-job-listings')
        <button
          class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          x-data=""
          x-on:click.prevent="$dispatch('open-modal', 'show-trashed')">
          <x-heroicon-o-trash class="w-4 inline"/> Show Trash</button>
        <a href="{{route('job-listings.create')}}"
            class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-plus class="w-4 inline"/> Create New Listing
        </a>
      @endcan
    </x-slot>

    <div class="">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Job Listings</h1>
        </div>
    </div>

    {{-- Search and Filter Section --}}
    <div class="mt-4 mb-6">
        <form method="GET" action="{{ route('job-listings.index') }}" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Search Input --}}
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Search positions..."
                        class="block w-full rounded-md border-0 py-2.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    >
                </div>

                {{-- Sector Filter --}}
                <select
                    name="sector"
                    id="sector"
                    class="rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    onchange="this.form.submit()"
                >
                    <option value="">All Sectors</option>
                    @foreach ($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ $selectedSector == $sector->id ? 'selected' : '' }}>
                            {{ $sector->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Department Filter --}}
                <select
                    name="department"
                    id="department"
                    class="rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    onchange="this.form.submit()"
                >
                    <option value="">All Departments</option>
                    @foreach ($sectorsWithDepartments as $sector)
                        <optgroup label="{{ $sector->name }}">
                            @foreach ($sector->departments as $department)
                                <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                {{-- Visibility Filter --}}
                <select
                    name="visibility"
                    id="visibility"
                    class="rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    onchange="this.form.submit()"
                >
                    <option value="">All Visibilities</option>
                    @if($isAdmin)
                        <option value="draft" {{ request('visibility') == 'draft' ? 'selected' : '' }}>Draft</option>
                    @endif
                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                    <option value="internal" {{ request('visibility') == 'internal' ? 'selected' : '' }}>Internal</option>
                </select>

                {{-- Sort --}}
                <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                    <button
                        type="button"
                        x-on:click="open = !open"
                        title="Sort"
                        class="flex items-center justify-center rounded-md border-0 bg-white px-3 py-2.5 text-sm leading-6 text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 hover:text-gray-700 focus:ring-2 focus:ring-indigo-600"
                    >
                        <span class="sr-only">Sort</span>
                        <x-heroicon-o-funnel class="size-5"/>
                    </button>
                    <div
                        x-show="open"
                        x-transition
                        x-cloak
                        class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-gray-900/5 focus:outline-none"
                    >
                        @foreach ([
                            ['sort' => 'position_title', 'direction' => 'asc', 'label' => 'Title (A-Z)'],
                            ['sort' => 'position_title', 'direction' => 'desc', 'label' => 'Title (Z-A)'],
                            ['sort' => 'closing_date', 'direction' => 'asc', 'label' => 'Closing Date (Soonest)'],
                            ['sort' => 'closing_date', 'direction' => 'desc', 'label' => 'Closing Date (Latest)'],
                        ] as $option)
                            <a
                                href="{{ route('job-listings.index', array_merge(request()->query(), ['sort' => $option['sort'], 'direction' => $option['direction']])) }}"
                                class="flex items-center justify-between px-4 py-2 text-sm {{ $sort == $option['sort'] && $direction == $option['direction'] ? 'font-semibold text-indigo-600' : 'text-gray-700' }} hover:bg-gray-50"
                            >
                                {{ $option['label'] }}
                                @if($sort == $option['sort'] && $direction == $option['direction'])
                                    <x-heroicon-o-check class="size-4"/>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Search Button --}}
                <button
                    type="submit"
                    class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Search
                </button>
            </div>

            {{-- Active Filters & Reset --}}
            @if(request()->hasAny(['search', 'sector', 'department', 'visibility']))
                <div class="flex items-center justify-between pt-2">
                    <div class="flex flex-wrap gap-2">
                        @if(request('search'))
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('job-listings.index', array_diff_key(request()->query(), ['search' => ''])) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-blue-200">
                                    <span class="sr-only">Remove</span>
                                    <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-blue-700/50 group-hover:stroke-blue-700/75">
                                        <path d="M4 4l6 6m0-6l-6 6" />
                                    </svg>
                                </a>
                            </span>
                        @endif
                        @if(request('sector'))
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                Sector: {{ $sectors->find(request('sector'))->name }}
                                <a href="{{ route('job-listings.index', array_diff_key(request()->query(), ['sector' => ''])) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-green-200">
                                    <span class="sr-only">Remove</span>
                                    <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-green-700/50 group-hover:stroke-green-700/75">
                                        <path d="M4 4l6 6m0-6l-6 6" />
                                    </svg>
                                </a>
                            </span>
                        @endif
                        @if(request('department'))
                            @php
                                $selectedDepartment = null;
                                foreach ($sectorsWithDepartments as $sector) {
                                    $found = $sector->departments->find(request('department'));
                                    if ($found) {
                                        $selectedDepartment = $found;
                                        break;
                                    }
                                }
                            @endphp
                            @if($selectedDepartment)
                                <span class="inline-flex items-center gap-x-1.5 rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-700">
                                    Dept: {{ $selectedDepartment->name }}
                                    <a href="{{ route('job-listings.index', array_diff_key(request()->query(), ['department' => ''])) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-purple-200">
                                        <span class="sr-only">Remove</span>
                                        <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-purple-700/50 group-hover:stroke-purple-700/75">
                                            <path d="M4 4l6 6m0-6l-6 6" />
                                        </svg>
                                    </a>
                                </span>
                            @endif
                        @endif
                        @if(request('visibility'))
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-700">
                                Visibility: {{ ucfirst(request('visibility')) }}
                                <a href="{{ route('job-listings.index', array_diff_key(request()->query(), ['visibility' => ''])) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-orange-200">
                                    <span class="sr-only">Remove</span>
                                    <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-orange-700/50 group-hover:stroke-orange-700/75">
                                        <path d="M4 4l6 6m0-6l-6 6" />
                                    </svg>
                                </a>
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('job-listings.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Clear all filters
                    </a>
                </div>
            @endif
        </form>
    </div>

        {{ $jobListings->links('vendor.pagination.custom') }}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <ul role="list" class="divide-y divide-gray-100">
                @forelse($jobListings as $listing)
                <li class="flex items-center justify-between gap-x-6 py-5">
                  <div class="min-w-0">
                    <div class="flex items-start gap-x-3">
                      <p class="text-sm/6 font-semibold text-gray-900">{{$listing->position_title}}</p>
                      @if($listing->visibility == 'draft')
                      <p class="mt-0.5 whitespace-nowrap rounded-md bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">Draft</p>
                      @elseif($listing->visibility == 'public')
                    <p class="mt-0.5 whitespace-nowrap rounded-md bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Public</p>
                      @else
                      <p class="mt-0.5 whitespace-nowrap rounded-md bg-orange-50 px-1.5 py-0.5 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">Internal</p>
                      @endif
                    </div>
                    <div class="mt-1 flex items-center gap-x-2 text-xs/5 text-gray-500">
                        @if(isset($listing->closing_date))
                      <p class="whitespace-nowrap">Closes <time datetime="{{$listing->closing_date}}">{{\Carbon\Carbon::parse($listing->closing_date)->diffForHumans()}}</time></p>
                      @else
                      <p class="whitespace-nowrap">Open Until Filled</p>
                      @endif
                      <svg viewBox="0 0 2 2" class="size-0.5 fill-current">
                        <circle cx="1" cy="1" r="1" />
                      </svg>
                      <p class="truncate">{{$listing->department->name}} for {{$listing->department->sector->name}}</p>
                      <svg viewBox="0 0 2 2" class="size-0.5 fill-current">
                        <circle cx="1" cy="1" r="1" />
                      </svg>
                      <p>Seeking {{$listing->number_of_openings}} individuals</p>
                    </div>
                  </div>
                  <div class="flex flex-none items-center gap-x-4">
                    @if($listing->visibility == 'draft' && Auth::user()->isAdmin())
                      <a href="{{route('job-listings.edit', $listing->id)}}" class="hidden rounded-md bg-white dark:bg-gray-800 px-2.5 py-1.5 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:block"><x-heroicon-o-pencil class="w-3 inline"/> Edit Draft</a>
                    @endif
                    <a href="{{route('job-listings.show', $listing->id)}}" class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">View Position</a>
                    {{-- <div class="relative flex-none">
                      <button type="button" class="-m-2.5 block p-2.5 text-gray-500 hover:text-gray-900" id="options-menu-0-button" aria-expanded="false" aria-haspopup="true">
                        <span class="sr-only">Open options</span>
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                          <path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM11.5 15.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z" />
                        </svg>
                      </button>
              
                      <!--
                        Dropdown menu, show/hide based on menu state.
              
                        Entering: "transition ease-out duration-100"
                          From: "transform opacity-0 scale-95"
                          To: "transform opacity-100 scale-100"
                        Leaving: "transition ease-in duration-75"
                          From: "transform opacity-100 scale-100"
                          To: "transform opacity-0 scale-95"
                      -->
                      <div class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="options-menu-0-button" tabindex="-1">
                        <!-- Active: "bg-gray-50 outline-none", Not Active: "" -->
                        <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900" role="menuitem" tabindex="-1" id="options-menu-0-item-0">Edit<span class="sr-only">, GraphQL API</span></a>
                        <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900" role="menuitem" tabindex="-1" id="options-menu-0-item-1">Move<span class="sr-only">, GraphQL API</span></a>
                        <a href="#" class="block px-3 py-1 text-sm/6 text-gray-900" role="menuitem" tabindex="-1" id="options-menu-0-item-2">Delete<span class="sr-only">, GraphQL API</span></a>
                      </div>
                    </div> --}}
                  </div>
                </li>
                @empty
                <div class="text-center py-6">
                    <x-heroicon-o-information-circle class="w-12 mx-auto text-gray-400"/>
                    {{-- <svg class="mx-auto size-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                      <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg> --}}
                    @if(request()->hasAny(['search', 'sector', 'department', 'visibility']))
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No positions found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                        <div class="mt-4">
                            <a href="{{ route('job-listings.index') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Clear all filters
                            </a>
                        </div>
                    @else
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No Positions Open</h3>
                        <p class="mt-1 text-sm text-gray-500">Maybe there'll be more later!</p>
                    @endif
                  </div>
                @endforelse
              </ul>  
        </div>
        {{ $jobListings->links('vendor.pagination.custom') }}
      </div>
</x-app-layout>

<x-modal name="show-trashed" class="p-6" focusable>
  <div class="p-6">
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Trashed Listings') }}</h2>
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
              <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($trashedListings as $trashed)
                <tr>
                  <td class="whitespace-nowrap py-2 pl-4 pr-3 text-sm text-gray-500 sm:pl-0">{{$trashed->id}}</td>
                  <td class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-900">{{$trashed->position_title}}</td>
                  <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-900">{{$trashed->deleted_at->diffForHumans()}}</td>
                  <td class="relative whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                    <a href="#" class="text-indigo-600 hover:text-indigo-900">
                      <form action="{{ route('job-listings.restore', $trashed->id) }}" method="POST" class="inline">
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