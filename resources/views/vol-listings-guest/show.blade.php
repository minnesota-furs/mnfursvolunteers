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
                  <dl class="grid grid-cols-3 gap-4">
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
                    <p>See somewhere you want to help? Login or create an account and pickup a slot!</p>
                  @endauth
                  @if(!$event->signup_open_date || $event->signup_open_date->isPast())
                  @else
                    <p class="text-sm/6 text-gray-00 text-semibold">Signups open <span>{{ $event->signup_open_date->diffForHumans() }} ({{ $event->signup_open_date->format('l F j @ g:i A') }})</p>
                  @endif
                  <ul role="list" class="divide-y divide-gray-100">
                    @forelse($shifts as $shift)
                    @php
                      $openings = $shift->max_volunteers - $shift->users->count();
                      $isFull = $openings <= 0;
                    @endphp
                      <li class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 sm:flex-nowrap">
                        <div>
                          <p class="text-sm/6 mt-4">
                            @if($isFull)
                              <x-heroicon-o-check class="w-4 mb-1 inline text-gray-400"/>
                            @else
                              <x-heroicon-s-users class="w-4 mb-1 inline"/>
                            @endif
                            <span class="font-semibold {{ $isFull ? 'text-gray-400' : 'text-gray-900' }}">
                              {{$shift->name}}
                            </span>
                            <span class="font-light {{ $isFull ? 'text-gray-300' : 'text-gray-500' }}"> - 
                              @if($event->isMultiDay())
                                {{ $shift->start_time->format('l') }}
                              @endif
                                {{ $shift->start_time->format('g:i A') }}
                              </span>
                          </p>
                          <div class="mt-1 flex items-center gap-x-2 text-xs/5 {{ $isFull ? 'text-gray-400' : 'text-gray-500' }}">
                            <p>
                              {{$shift->description ?? 'No description given'}} (Slot ID {{$shift->id}})
                            </p>
                            <p>
                          </div>
                        </div>
                        <dl class="flex w-full flex-none justify-between gap-x-8 sm:w-auto">
                          <div class="flex w-32 gap-x-2.5">
                            <dt>
                              <span class="sr-only">Openings</span>
                            </dt>
                            <dd class="text-sm/6 {{ $isFull ? 'text-gray-300' : 'text-gray-900' }}">{{ $shift->max_volunteers - $shift->users->count()}} Openings</dd>
                          </div>
                        </dl>
                      </li>
                    @empty
                    <li class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 sm:flex-nowrap">
                      <div>
                        <p class="text-sm/6 mt-4">
                          <span class="text-gray-400">No slots are currently available.</span>
                        </p>
                    </li>
                    @endforelse
                  </ul>
                </div>
            </div>
        </div>
    </div>
</div>

        

</x-guestv2-layout>
