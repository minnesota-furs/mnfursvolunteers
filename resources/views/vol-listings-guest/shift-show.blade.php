<x-guestv2-layout
  ogTitle="{{ $shift->name }} - {{ $event->name }}"
  ogDescription="{{ $shift->description ?? 'Volunteer opportunity for ' . $event->name }}"
  ogImage="{{ URL('/images/dashboard/image3.jpg') }}"
  ogUrl="{{ url()->current() }}"
  ogType="article"
>

  <div class="relative isolate">
    <div class="overflow-hidden">
        <div class="mx-auto max-w-7xl px-6 pb-32 pt-36 sm:pt-60 lg:px-8 lg:pt-32">
          <a class="text-blue-800 no-underline hover:underline" href="{{ route('vol-listings-public.show', $event) }}">&larr; Back to {{ $event->name }}</a>
            <div class="mx-auto max-w-2xl gap-x-14 lg:mx-0 lg:max-w-none lg:items-center">
                {{-- Header Section --}}
                <div class="mb-8">
                  <h1 class="text-5xl font-semibold tracking-tight sm:text-6xl">{{ $shift->name }}</h1>
                  <p class="mt-4 text-xl text-gray-600">{{ $event->name }}</p>
                </div>

                {{-- Status Banner --}}
                @if($isFull)
                  <div class="rounded-md bg-gray-100 p-4 mb-6">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <x-heroicon-o-check class="h-5 w-5 text-gray-400" />
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-800">This shift is full</h3>
                        <div class="mt-2 text-sm text-gray-700">
                          <p>All volunteer positions for this shift have been filled.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                @elseif($isPast)
                  <div class="rounded-md bg-gray-100 p-4 mb-6">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <x-heroicon-o-clock class="h-5 w-5 text-gray-400" />
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-800">This shift has passed</h3>
                        <div class="mt-2 text-sm text-gray-700">
                          <p>This volunteer opportunity has already occurred.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                @elseif($openings > 0 && $openings <= 3)
                  <div class="rounded-md bg-yellow-50 p-4 mb-6">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400" />
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Only {{ $openings }} {{ Str::plural('spot', $openings) }} remaining!</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                          <p>This shift is filling up quickly. Sign up soon to secure your spot!</p>
                        </div>
                      </div>
                    </div>
                  </div>
                @else
                  <div class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <x-heroicon-s-users class="h-5 w-5 text-green-400" />
                      </div>
                      <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Positions Available</h3>
                        <div class="mt-2 text-sm text-green-700">
                          <p>{{ $openings }} volunteer {{ Str::plural('position', $openings) }} available for this shift.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                @endif

                {{-- Shift Details --}}
                <div class="mt-6">
                  <h2 class="text-2xl font-semibold tracking-tight mb-4">Shift Details</h2>
                  <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="border-t border-gray-100 px-4 py-6 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Date</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $shift->start_time->format('l, F j, Y') }}
                      </dd>
                    </div>
                    
                    <div class="border-t border-gray-100 px-4 py-6 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Time</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $shift->start_time->format('g:i A') }} - {{ $shift->end_time->format('g:i A') }}
                      </dd>
                    </div>
                    
                    <div class="border-t border-gray-100 px-4 py-6 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Duration</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $shift->durationInHours() }} {{ Str::plural('hour', $shift->durationInHours()) }}
                      </dd>
                    </div>
                    
                    <div class="border-t border-gray-100 px-4 py-6 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Available Positions</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $openings }} of {{ $shift->max_volunteers }}
                      </dd>
                    </div>
                    
                    @if($shift->double_hours)
                    <div class="border-t border-gray-100 px-4 py-6 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Hours Credit</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        <div class="flex items-center">
                          <x-heroicon-s-star class="w-4 h-4 text-yellow-500 mr-1" />
                          <span class="font-semibold text-yellow-700">Double Hours</span>
                        </div>
                        <span class="text-xs text-gray-600">You'll receive {{ $shift->durationInHours() * 2 }} hours credit</span>
                      </dd>
                    </div>
                    @else
                    <div class="border-t border-gray-100 px-4 py-6 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Hours Credit</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $shift->durationInHours() }} {{ Str::plural('hour', $shift->durationInHours()) }}
                      </dd>
                    </div>
                    @endif
                    
                    <div class="border-t border-gray-100 px-4 py-6 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Location</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">
                        {{ $event->location ?? 'Not specified' }}
                      </dd>
                    </div>
                  </dl>
                </div>

                {{-- Description Section --}}
                @if($shift->description)
                <div class="mt-8">
                  <h2 class="text-2xl font-semibold tracking-tight mb-4">Description</h2>
                  <div class="prose prose-sm max-w-none text-gray-700">
                    <p>{{ $shift->description }}</p>
                  </div>
                </div>
                @endif

                {{-- Call to Action --}}
                <div class="mt-10 border-t border-gray-200 pt-8">
                  @auth
                    <div class="rounded-md bg-blue-50 p-6">
                      <h3 class="text-lg font-semibold text-blue-900 mb-2">Ready to volunteer?</h3>
                      <p class="text-sm text-blue-700 mb-4">
                        You're viewing the public page. To sign up for this shift, please use the volunteer application.
                      </p>
                      <a href="{{ route('volunteer.events.show', $event) }}" 
                         class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        View in Application
                        <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                      </a>
                    </div>
                  @else
                    <div class="rounded-md bg-blue-50 p-6">
                      <h3 class="text-lg font-semibold text-blue-900 mb-2">Interested in this shift?</h3>
                      <p class="text-sm text-blue-700 mb-4">
                        Login or create an account to sign up for this volunteer opportunity and help make {{ $event->name }} a success!
                      </p>
                      <div class="flex gap-3">
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                          Login
                        </a>
                        {{-- <a href="{{ route('register') }}" 
                           class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm ring-1 ring-inset ring-blue-300 hover:bg-blue-50">
                          Create Account
                        </a> --}}
                      </div>
                    </div>
                  @endauth
                </div>

                {{-- Related Shifts --}}
                @php
                  $relatedShifts = $event->shifts()
                    ->where('id', '!=', $shift->id)
                    ->when($event->hide_past_shifts, fn($query) => $query->where('start_time', '>=', now()))
                    ->orderBy('start_time')
                    ->limit(5)
                    ->get();
                @endphp

                @if($relatedShifts->count() > 0)
                <div class="mt-12 border-t border-gray-200 pt-8">
                  <h2 class="text-2xl font-semibold tracking-tight mb-4">More Opportunities for {{ $event->name }}</h2>
                  <ul role="list" class="divide-y divide-gray-100 border border-gray-200 rounded-lg">
                    @foreach($relatedShifts as $relatedShift)
                    @php
                      $relatedOpenings = $relatedShift->max_volunteers - $relatedShift->users->count();
                      $relatedIsFull = $relatedOpenings <= 0;
                    @endphp
                      <li class="flex items-center justify-between gap-x-6 py-4 px-4 hover:bg-gray-50">
                        <div class="min-w-0">
                          <div class="flex items-start gap-x-3">
                            <a href="{{ route('vol-listings-public.shift.show', [$event, $relatedShift]) }}" 
                               class="text-sm/6 font-semibold no-underline hover:underline {{ $relatedIsFull ? 'text-gray-400' : 'text-blue-700' }}">
                              @if($relatedIsFull)
                                <x-heroicon-o-check class="w-4 h-4 inline mb-1"/>
                              @else
                                <x-heroicon-s-users class="w-4 h-4 inline mb-1"/>
                              @endif
                              {{ $relatedShift->name }}
                            </a>
                            <span class="text-sm/6 {{ $relatedIsFull ? 'text-gray-400' : 'text-gray-600' }}">
                              {{ $relatedShift->start_time->format('M j @ g:i A') }}
                            </span>
                          </div>
                          @if($relatedShift->description)
                          <div class="mt-1 flex items-center gap-x-2 text-xs/5 {{ $relatedIsFull ? 'text-gray-300' : 'text-gray-500' }}">
                            {{ Str::limit($relatedShift->description, 100) }}
                          </div>
                          @endif
                        </div>
                        <div class="flex flex-none items-center gap-x-4">
                          <span class="text-sm {{ $relatedIsFull ? 'text-gray-400' : 'text-gray-900' }}">
                            {{ $relatedOpenings }} {{ Str::plural('opening', $relatedOpenings) }}
                          </span>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

</x-guestv2-layout>
