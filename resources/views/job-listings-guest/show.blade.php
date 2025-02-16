<x-guestv2-layout
  title="My Custom Page Title"
  ogTitle="Help wanted! {{$jobListing->department->sector->name}} - {{$jobListing->position_title}}"
  ogDescription="{{\Str::limit($jobListing->plainTextDescription, 200)}}"
  ogImage="{{URL('/images/dashboard/image1.jpg')}}"
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
          <a class="text-blue-800" href="{{route('job-listings-public.index')}}">&larr; Back to positions</a>
            <div class="mx-auto max-w-2xl gap-x-14 lg:mx-0 lg:max-w-none lg:items-center">
                {{-- Start --}}
                <h1 class="text-5xl font-semibold tracking-tight sm:text-6xl">{{$jobListing->position_title}}</h1>
                <div class="mt-6">
                  <dl class="grid grid-cols-3 gap-4">
                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Position Name</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">{{$jobListing->position_title}}</dd>
                    </div>
                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Department</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">{{$jobListing->department->name}}</dd>
                    </div>
                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Sector</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">{{$jobListing->department->sector->name}}</dd>
                    </div>
                    {{-- <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                      <dt class="text-sm/6 font-medium text-gray-900">Reports To</dt>
                      <dd class="mt-1 text-sm/6 text-gray-700 sm:mt-2">{{$jobListing->department->head->name ?? 'Not specified'}}</dd>
                    </div> --}}
                  </dl>
                </div>
                {{-- End --}}
                <div class="prose prose-sm max-w-none mt-8">
                  <h1 class="text-3xl font-semibold tracking-tight sm:text-3xl">Position Description</h1>
                  {!! $jobListing->parsedDescription !!}
                </div>
            </div>
        </div>
    </div>
</div>

        

</x-guestv2-layout>
