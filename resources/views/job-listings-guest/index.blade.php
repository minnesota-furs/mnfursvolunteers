<x-guestv2-layout
  title="My Custom Page Title"
  ogTitle="MNFurs Help Wanted (Open Positions)"
  ogDescription="Want to help out? All positions within our organization are volunteer-based as we are a 501(c)(3) nonprofit organization."
  ogImage="{{URL('/images/dashboard/image2.jpg')}}"
  ogUrl="{{ url()->current() }}"
  ogType="article"
>

  <div class="relative isolate">

    {{-- <div class="absolute left-1/2 right-0 top-0 -ml-24 transform-gpu overflow-hidden blur-3xl lg:ml-24 xl:ml-48"
        aria-hidden="true">
        <div class="aspect-[801/1036] w-[50.0625rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30"
            style="clip-path: polygon(63.1% 29.5%, 100% 17.1%, 76.6% 3%, 48.4% 0%, 44.6% 4.7%, 54.5% 25.3%, 59.8% 49%, 55.2% 57.8%, 44.4% 57.2%, 27.8% 47.9%, 35.1% 81.5%, 0% 97.7%, 39.2% 100%, 35.2% 81.4%, 97.2% 52.8%, 63.1% 29.5%)">
        </div>
    </div> --}}
    <div class="overflow-hidden">
        <div class="mx-auto max-w-7xl px-6 pb-32 pt-36 sm:pt-60 lg:px-8 lg:pt-32">
          <h1 class="text-4xl font-semibold tracking-tight sm:text-4xl">Current Openings ({{$jobListings->total()}})</h1>
            <div class="mx-auto max-w-2xl gap-x-14 lg:mx-0 lg:max-w-none lg:items-center">
                
                {{-- Search and Filter Section --}}
                <div class="mt-8 mb-6">
                    <form method="GET" action="{{ route('job-listings-public.index') }}" class="space-y-4">
                        {{-- Search Bar and Filters in One Row --}}
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
                                    <option value="{{ $sector->id }}" {{ request('sector') == $sector->id ? 'selected' : '' }}>
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

                            {{-- Search Button --}}
                            <button 
                                type="submit" 
                                class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            >
                                Search
                            </button>
                        </div>

                        {{-- Active Filters & Reset --}}
                        @if(request()->hasAny(['search', 'sector', 'department']))
                            <div class="flex items-center justify-between pt-2">
                                <div class="flex flex-wrap gap-2">
                                    @if(request('search'))
                                        <span class="inline-flex items-center gap-x-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                            Search: "{{ request('search') }}"
                                            <a href="{{ route('job-listings-public.index', array_diff_key(request()->query(), ['search' => ''])) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-blue-200">
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
                                            <a href="{{ route('job-listings-public.index', array_diff_key(request()->query(), ['sector' => ''])) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-green-200">
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
                                                <a href="{{ route('job-listings-public.index', array_diff_key(request()->query(), ['department' => ''])) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-purple-200">
                                                    <span class="sr-only">Remove</span>
                                                    <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-purple-700/50 group-hover:stroke-purple-700/75">
                                                        <path d="M4 4l6 6m0-6l-6 6" />
                                                    </svg>
                                                </a>
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                <a href="{{ route('job-listings-public.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    Clear all filters
                                </a>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Start --}}
                {{ $jobListings->links('vendor.pagination.custom') }}
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
                    <ul role="list" class="divide-y divide-gray-100">
                        @forelse($jobListings as $listing)
                        <li class="flex items-center justify-between gap-x-6 py-5 hover:bg-gray-50 p-2">
                          <div class="min-w-0">
                            <div class="flex items-start gap-x-3">
                              <a href="{{route('job-listings-public.show', $listing->id)}}" class="text-sm/6 font-semibold text-blue-700">{{$listing->position_title}}</a>
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
                            </div>
                          </div>
                          <div class="flex flex-none items-center gap-x-4">
                            <a href="{{route('job-listings-public.show', $listing->id)}}" class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">View Position</a>
                          </div>
                        </li>
                        @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @if(request()->hasAny(['search', 'sector', 'department']))
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">No positions found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                                <div class="mt-6">
                                    <a href="{{ route('job-listings-public.index') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                                        </svg>
                                        Clear all filters
                                    </a>
                                </div>
                            @else
                                <h3 class="mt-2 text-sm font-semibold text-gray-900">No Positions Open</h3>
                                <p class="mt-1 text-sm text-gray-500">Check back later for new opportunities!</p>
                            @endif
                          </div>
                        @endforelse
                      </ul>  
                </div>
                {{ $jobListings->links('vendor.pagination.custom') }}
                {{-- End --}}
            </div>
        </div>
    </div>
</div>



</x-guestv2-layout>
