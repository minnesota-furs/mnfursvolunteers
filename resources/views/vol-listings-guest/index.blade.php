<x-guestv2-layout
  ogTitle="MNFurs Help Wanted (Volunteers)"
  ogDescription="Want to help out? We're always looking for volunteers to help make our events possible!"
  ogImage="{{URL('/images/dashboard/image4.jpg')}}"
  ogUrl="{{ url()->current() }}"
  ogType="article"
>

  <div class="relative isolate"
    {{-- <div class="absolute left-1/2 right-0 top-0 -ml-24 transform-gpu overflow-hidden blur-3xl lg:ml-24 xl:ml-48"
        aria-hidden="true">
        <div class="aspect-[801/1036] w-[50.0625rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30"
            style="clip-path: polygon(63.1% 29.5%, 100% 17.1%, 76.6% 3%, 48.4% 0%, 44.6% 4.7%, 54.5% 25.3%, 59.8% 49%, 55.2% 57.8%, 44.4% 57.2%, 27.8% 47.9%, 35.1% 81.5%, 0% 97.7%, 39.2% 100%, 35.2% 81.4%, 97.2% 52.8%, 63.1% 29.5%)">
        </div>
    </div> --}}
    <div class="overflow-hidden">
        <div class="mx-auto max-w-7xl px-6 pb-32 pt-36 sm:pt-60 lg:px-8 lg:pt-32">
          <h1 class="text-4xl font-semibold tracking-tight sm:text-4xl">Events Seeking Volunteers ({{count($events)}})</h1>
            <div class="mx-auto max-w-2xl gap-x-14 lg:mx-0 lg:max-w-none lg:items-center">
                {{-- Start --}}
                {{-- {{ $events->links() }} --}}
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
                    <ul role="list" class="divide-y divide-gray-100">
                        @forelse($events as $event)
                        <li class="flex items-center justify-between gap-x-6 py-5 hover:bg-gray-50 p-2">
                          <div class="min-w-0">
                            <div class="flex items-start gap-x-3">
                              <a href="{{route('vol-listings-public.show', $event)}}" class="text-sm/6 font-semibold text-blue-700">
                                {{$event->name}}</a>
                                <span class="text-sm/6">{{ $event->start_date->format('M j, Y') }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-x-2 text-xs/5 text-gray-500">
                                {{$event->description ?? ''}}
                            </div>
                          </div>
                          <div class="flex flex-none items-center gap-x-4">
                            <a href="{{route('vol-listings-public.show', $event)}}" class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">
                              View Opportunities & Details</a>
                          </div>
                        </li>
                        @empty
                        <div class="text-center py-6">
                            <x-heroicon-o-information-circle class="w-12 mx-auto text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No Events Seeking Volunteers</h3>
                            <p class="mt-1 text-sm text-gray-500">Maybe there'll be more later!</p>
                          </div>
                        @endforelse
                      </ul>  
                </div>
                {{-- {{ $events->links() }} --}}
                {{-- End --}}
            </div>
        </div>
    </div>
</div>



</x-guestv2-layout>
