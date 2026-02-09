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

                {{-- Application Form Section --}}
                @feature('job_applications')
                @guest
                <div class="mt-12 border-t border-gray-200 pt-8">
                  <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">Interested in this position?</h2>
                    <p class="text-gray-600 mb-6">Fill out the form below and we'll get back to you soon!</p>

                    @if(session('application_success'))
                      <div class="mb-6 rounded-md bg-green-50 p-4">
                        <div class="flex">
                          <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                          </div>
                          <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                              {{ session('application_success') }}
                            </p>
                          </div>
                        </div>
                      </div>
                    @endif

                    <form action="{{ route('job-listings-public.apply', $jobListing->id) }}" method="POST" class="space-y-6">
                      @csrf

                      <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                          Name / Alias <span class="text-red-500">*</span>
                        </label>
                        <input 
                          type="text" 
                          name="name" 
                          id="name" 
                          required
                          value="{{ old('name') }}"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('name') border-red-300 @enderror"
                          placeholder="Your name or preferred alias"
                        />
                        @error('name')
                          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                      </div>

                      <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                          Email Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                          type="email" 
                          name="email" 
                          id="email" 
                          required
                          value="{{ old('email') }}"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('email') border-red-300 @enderror"
                          placeholder="your.email@example.com"
                        />
                        @error('email')
                          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                      </div>

                      <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700">
                          Additional Comments
                        </label>
                        <textarea 
                          name="comments" 
                          id="comments" 
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('comments') border-red-300 @enderror"
                          placeholder="Tell us why you're interested in this position, your relevant experience, or any questions you have..."
                        >{{ old('comments') }}</textarea>
                        @error('comments')
                          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                      </div>

                      <div class="flex items-center justify-between pt-4">
                        <p class="text-xs text-gray-500">
                          <span class="text-red-500">*</span> Required fields
                        </p>
                        <button 
                          type="submit" 
                          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                          </svg>
                          Submit Application
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
                @endguest
                @endfeature

                @auth
                <div class="mt-12 border-t border-gray-200 pt-8">
                  <div class="bg-blue-50 rounded-lg p-6">
                    <p class="text-sm text-blue-800">
                      <strong>Note:</strong> You are currently logged in. To apply for this position, please contact your department head or an administrator directly.
                    </p>
                  </div>
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>

        

</x-guestv2-layout>
