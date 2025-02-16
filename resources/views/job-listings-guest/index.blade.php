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
          <h1 class="text-4xl font-semibold tracking-tight sm:text-4xl">Current Openings ({{count($jobListings)}})</h1>
            <div class="mx-auto max-w-2xl gap-x-14 lg:mx-0 lg:max-w-none lg:items-center">
                {{-- Start --}}
                {{ $jobListings->links() }}
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
                        <div class="text-center py-6">
                            <x-heroicon-o-information-circle class="w-12 mx-auto text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No Positions Open</h3>
                            <p class="mt-1 text-sm text-gray-500">Maybe there'll be more later!</p>
                          </div>
                        @endforelse
                      </ul>  
                </div>
                {{ $jobListings->links() }}
                {{-- End --}}
            </div>
        </div>
    </div>
</div>



</x-guestv2-layout>
