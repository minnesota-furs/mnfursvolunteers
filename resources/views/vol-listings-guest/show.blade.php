<x-guestv2-layout
  ogTitle="Help wanted with {{$event->name}}"
  ogDescription="{{\Str::limit($event->description, 200)}}"
  ogImage="{{URL('/images/dashboard/image3.jpg')}}"
  ogUrl="{{ url()->current() }}"
  ogType="article"
>

  <div class="relative isolate">
    {{-- <div class="absolute left-1/2 right-0 top-0 -ml-24 transform-gpu overflow-hidden blur-3xl lg:ml-24 xl:ml-48"
        aria-hidden="true">
        <div class="aspect-[801/1036] w-[50.0625rem] bg-gradient-to-tr from-[#32a852] to-[#fcb789] opacity-30"
            style="clip-path: polygon(63.1% 29.5%, 100% 17.1%, 76.6% 3%, 48.4% 0%, 44.6% 4.7%, 54.5% 25.3%, 59.8% 49%, 55.2% 57.8%, 44.4% 57.2%, 27.8% 47.9%, 35.1% 81.5%, 0% 97.7%, 39.2% 100%, 35.2% 81.4%, 97.2% 52.8%, 63.1% 29.5%)">
        </div>
    </div> --}}
    <div class="overflow-hidden">
        <div class="mx-auto max-w-7xl px-6 pb-32 pt-36 sm:pt-60 lg:px-8 lg:pt-32">
          <a class="text-blue-800" href="{{route('vol-listings-public.index')}}">&larr; Back to events</a>
            <div class="mx-auto max-w-2xl gap-x-14 lg:mx-0 lg:max-w-none lg:items-center">
                {{-- Start --}}
                <h1 class="text-5xl font-semibold tracking-tight sm:text-6xl">{{$event->name}}</h1>
                <div class="mt-6">
                  <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Starts</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $event->start_date->format('M j, Y @ g:i A') }}</dd>
                    </div>
                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Ends</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $event->end_date->format('M j, Y @ g:i A') ?? '' }}</dd>
                    </div>
                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Positions</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">{{ $event->shifts()->count() }}</dd>
                    </div>
                    {{-- <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Sector</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">{{$event->department->sector->name}}</dd>
                    </div> --}}
                  </dl>
                </div>
                {{-- End --}}
                
                <div class="prose prose-sm max-w-none mt-8">
                  <h1 class="text-3xl font-semibold tracking-tight sm:text-3xl">Available Slots</h1>
                  @auth
                    <p>Hello {{Auth::user()->name}}. This is the public view of the volunteer event. You can pickup slots in the application.
                      <a class="text-blue-700 no-underline" href="{{ route('volunteer.events.show', $event) }}">View Event in Application</a>
                    </p>
                  @else
                    <div class="not-prose bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                      <div class="flex items-start gap-3">
                        <x-heroicon-o-information-circle class="w-6 h-6 text-blue-500 flex-shrink-0"/>
                        <div class="flex-1">
                          <p class="text-sm text-blue-800">
                            You'll need a free volunteer account to pick up slots and get credit for your volunteer hours.
                          </p>
                          <div class="mt-3 flex flex-wrap gap-3">
                            @if(app_setting('advertise_registration_on_login', true))
                            <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 no-underline">
                              Create Account
                            </a>
                            @endif
                            <a href="{{ route('login') }}" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-inset ring-blue-300 hover:bg-blue-50 no-underline">
                              Login
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endauth
                  @if(!$event->signup_open_date || $event->signup_open_date->isPast())
                  @else
                    <p class="text-sm/6 text-gray-00 text-semibold">Signups open <span>{{ $event->signup_open_date->diffForHumans() }} ({{ $event->signup_open_date->format('l F j @ g:i A') }})</p>
                  @endif

                  {{-- Search and Filter Section --}}
                  <div class="mt-8 mb-6 not-prose">
                    <form method="GET" action="{{ route('vol-listings-public.show', $event) }}" class="space-y-4">
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
                            placeholder="Search slots..."
                            class="block w-full rounded-md border-0 py-2.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                          >
                        </div>

                        @if($event->isMultiDay() && $availableDays->isNotEmpty())
                          {{-- Day Filter --}}
                          <select
                            name="day"
                            id="day"
                            class="w-full rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:w-auto sm:text-sm sm:leading-6"
                            onchange="this.form.submit()"
                          >
                            <option value="">All Days</option>
                            @foreach($availableDays as $day)
                              <option value="{{ $day }}" {{ request('day') == $day ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($day)->format('l, M j') }}
                              </option>
                            @endforeach
                          </select>
                        @endif

                        {{-- Availability Filter --}}
                        <select
                          name="availability"
                          id="availability"
                          class="w-full rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:w-auto sm:text-sm sm:leading-6"
                          onchange="this.form.submit()"
                        >
                          <option value="">All Slots</option>
                          <option value="open" {{ request('availability') == 'open' ? 'selected' : '' }}>Open Slots Only</option>
                        </select>

                        @if($availableCategories->isNotEmpty())
                          {{-- Category Filter --}}
                          <select
                            name="category"
                            id="category"
                            class="w-full rounded-md border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:w-auto sm:text-sm sm:leading-6"
                            onchange="this.form.submit()"
                          >
                            <option value="">All Categories</option>
                            @foreach($availableCategories as $category)
                              <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                              </option>
                            @endforeach
                          </select>
                        @endif

                        {{-- Search Button --}}
                        <button
                          type="submit"
                          class="w-full rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:w-auto"
                        >
                          Search
                        </button>
                      </div>

                      {{-- Active Filters & Reset --}}
                      @if(request()->hasAny(['search', 'day', 'availability', 'category']))
                        <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:items-center sm:justify-between">
                          <div class="flex flex-wrap gap-2">
                            @if(request('search'))
                              <span class="inline-flex items-center gap-x-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('vol-listings-public.show', array_merge(['event' => $event], array_diff_key(request()->query(), ['search' => '']))) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-blue-200">
                                  <span class="sr-only">Remove</span>
                                  <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-blue-700/50 group-hover:stroke-blue-700/75">
                                    <path d="M4 4l6 6m0-6l-6 6" />
                                  </svg>
                                </a>
                              </span>
                            @endif
                            @if(request('day'))
                              <span class="inline-flex items-center gap-x-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                Day: {{ \Carbon\Carbon::parse(request('day'))->format('l, M j') }}
                                <a href="{{ route('vol-listings-public.show', array_merge(['event' => $event], array_diff_key(request()->query(), ['day' => '']))) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-green-200">
                                  <span class="sr-only">Remove</span>
                                  <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-green-700/50 group-hover:stroke-green-700/75">
                                    <path d="M4 4l6 6m0-6l-6 6" />
                                  </svg>
                                </a>
                              </span>
                            @endif
                            @if(request('availability'))
                              <span class="inline-flex items-center gap-x-1.5 rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-700">
                                Open Slots Only
                                <a href="{{ route('vol-listings-public.show', array_merge(['event' => $event], array_diff_key(request()->query(), ['availability' => '']))) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-purple-200">
                                  <span class="sr-only">Remove</span>
                                  <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-purple-700/50 group-hover:stroke-purple-700/75">
                                    <path d="M4 4l6 6m0-6l-6 6" />
                                  </svg>
                                </a>
                              </span>
                            @endif
                            @if(request('category'))
                              @php $activeCategory = $availableCategories->firstWhere('id', (int) request('category')); @endphp
                              <span class="inline-flex items-center gap-x-1.5 rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                                Category: {{ $activeCategory->name ?? request('category') }}
                                <a href="{{ route('vol-listings-public.show', array_merge(['event' => $event], array_diff_key(request()->query(), ['category' => '']))) }}" class="group relative -mr-1 h-3.5 w-3.5 rounded-sm hover:bg-yellow-200">
                                  <span class="sr-only">Remove</span>
                                  <svg viewBox="0 0 14 14" class="h-3.5 w-3.5 stroke-yellow-700/50 group-hover:stroke-yellow-700/75">
                                    <path d="M4 4l6 6m0-6l-6 6" />
                                  </svg>
                                </a>
                              </span>
                            @endif
                          </div>
                          <a href="{{ route('vol-listings-public.show', $event) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Clear all filters
                          </a>
                        </div>
                      @endif
                    </form>
                  </div>

                  {{ $shifts->links('vendor.pagination.custom') }}

                  <ul role="list" class="divide-y divide-gray-100">
                    @forelse($shifts as $shift)
                    @php
                      $openings = $shift->max_volunteers - $shift->filled_count;
                      $isFull = $openings <= 0;
                    @endphp
                      <li class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 sm:flex-nowrap hover:bg-gray-50 p-2 rounded">
                        <div class="min-w-0 flex-grow">
                          <p class="text-sm/6 mt-4 break-words">
                            @if($isFull)
                              <x-heroicon-o-check class="w-4 mb-1 inline text-gray-400"/>
                            @else
                              <x-heroicon-s-users class="w-4 mb-1 inline"/>
                            @endif
                            <a href="{{ route('vol-listings-public.shift.show', [$event, $shift]) }}"
                               class="font-semibold no-underline hover:underline {{ $isFull ? 'text-gray-400 hover:text-gray-500' : 'text-blue-700 hover:text-blue-800' }}">
                              {{$shift->name}}
                            </a>
                            <span class="font-light {{ $isFull ? 'text-gray-300' : 'text-gray-500' }}"> -
                              @if($event->isMultiDay())
                                {{ $shift->start_time->format('l') }}
                              @endif
                                {{ $shift->start_time->format('g:i A') }}
                              </span>
                            @foreach($shift->categories as $category)
                              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ml-1"
                                style="background-color:{{ $category->color }}22; color:{{ $category->color }}; border-color:{{ $category->color }}44;">
                                @if($category->color)
                                  <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color:{{ $category->color }}"></span>
                                @endif
                                {{ $category->name }}
                              </span>
                            @endforeach
                          </p>
                          <div class="flex flex-col mt-1 gap-x-2 text-xs/5 {{ $isFull ? 'text-gray-400' : 'text-gray-500' }}">
                            @if($shift->double_hours)
                            <div>
                              <x-heroicon-s-star title="Double Hours" class="w-3 mb-1 inline"/> This slot grants Double Hours
                            </div>
                            @endif
                            <p class="break-words">
                              {{$shift->description ?? 'No description given'}}

                            </p>
                          </div>
                        </div>
                        <div class="flex-shrink-0">
                          <span class="sr-only">Openings</span>
                          <span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $isFull ? 'bg-gray-100 text-gray-500' : 'bg-green-50 text-green-700' }}">
                            {{ $openings }} {{ Str::plural('Opening', $openings) }}
                          </span>
                        </div>
                      </li>
                    @empty
                    <li class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 sm:flex-nowrap">
                      <div>
                        <p class="text-sm/6 mt-4">
                          @if(request()->hasAny(['search', 'day', 'availability', 'category']))
                            <span class="text-gray-400">No slots match your search or filters.</span>
                            <a href="{{ route('vol-listings-public.show', $event) }}" class="text-blue-700 no-underline">Clear all filters</a>
                          @else
                            <span class="text-gray-400">No slots are currently available.</span>
                          @endif
                        </p>
                      </div>
                    </li>
                    @endforelse
                  </ul>

                  {{ $shifts->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>
</x-guestv2-layout>
