<x-app-layout>
        <x-slot name="header">
            {{ __('Open Position:') }} {{ $jobListing->position_title }} for {{ $jobListing->department->name}}
        </x-slot>

        <x-slot name="actions">
                <a href="{{route('job-listings.index')}}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Back
                </a>
            @if( Auth::user()->isAdmin() )
                <a href="{{route('job-listings.edit', $jobListing->id)}}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Edit
                </a>
            <form action="{{ route('job-listings.destroy', $jobListing->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        onclick="return confirm('Are you sure you want to delete this job listing?');">
                        <x-heroicon-s-trash class="w-4 inline"/> Delete
                </button>
            </form>
                {{-- <a href="{{route('job-listings.destroy', $jobListing->id)}}"
                    class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <x-heroicon-s-trash class="w-4 inline"/> Delete
                </a> --}}
            @endif
                {{-- <a href="{{route('job-listings.index')}}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Submit Interest / Apply
                </a> --}}
                @if ($jobListing->visibility === 'public')
                    <button
                        class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        onclick="copyToClipboard('{{ route('job-listings-public.show', $jobListing->id) }}')">
                        <x-heroicon-s-link class="w-4 inline"/> Copy Public URL
                    </button>
                @endif
        </x-slot>

        @if($jobListing->visibility == 'draft')
        <div class="bg-slate-200 border-l-4 border-slate-500 text-slate-700 p-4 mb-6" role="alert">
            <p class="font-bold">This is a draft!</p>
            <p>Nobody can see this listing as long as it remains a draft.</p>
          </div>
        @endif

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-1">
                    {{-- Start Left Column --}}
                    <div>
                        <div class="px-4 sm:px-0">
                            <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Position General Info</h3>
                        </div>
                        <div class="mt-6 border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Role Name</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $jobListing->position_title }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Sector</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $jobListing->department->sector->name}}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Department</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $jobListing->department->name}}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Reports To</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $jobListing->department->head->name ?? 'None' }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Applications Close</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ $jobListing->closing_date ? $jobListing->closing_date->format('F j, Y') : 'No closing date' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Position Description</h3>
                    </div>
                    <div class="prose prose-sm max-w-none mt-8">
                        {!! $jobListing->parsedDescription !!}
                    </div>
                    
                </div>
            </div>
        </div>
        <script>
            function copyToClipboard(url) {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Public URL copied to clipboard!');
                }, function(err) {
                    console.error('Failed to copy URL: ', err);
                });
            }
        </script>
</x-app-layout>
