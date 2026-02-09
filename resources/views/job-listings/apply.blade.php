<x-app-layout>
    @section('title', 'Apply for ' . $jobListing->position_title)
    
    <x-slot name="header">
        {{ __('Apply for Position') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('job-listings.show', $jobListing) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Position
        </a>
    </x-slot>

    <div class="sm:px-6 lg:px-8">
        <div class="p-6">
            <!-- Position Summary -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    {{ $jobListing->position_title }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $jobListing->department->name }} • {{ $jobListing->department->sector->name }}
                </p>
                @if($jobListing->closing_date)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <x-heroicon-o-calendar class="w-4 h-4 inline"/> 
                        Applications close {{ $jobListing->closing_date->format('F j, Y') }}
                    </p>
                @endif
            </div>

            @if($userDepartments->count() > 0)
                <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400"/>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                You are currently in {{ $userDepartments->count() }} {{ $userDepartments->count() === 1 ? 'department' : 'departments' }}
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p class="mb-2">While department overlap may be acceptable, please ensure you don't spread yourself too thin:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($userDepartments as $dept)
                                        <li>{{ $dept->name }} ({{ $dept->sector->name }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Application Form -->
            <form method="POST" action="{{ route('job-listings.apply.submit', $jobListing) }}">
                @csrf

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Your Application
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        We've pre-filled your name and email from your account. Please let us know why you're interested in this position.
                    </p>
                </div>

                <!-- Name (Read-only) -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Your Name
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        value="{{ auth()->user()->name }}"
                        readonly
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 shadow-sm cursor-not-allowed"
                    >
                </div>

                <!-- Email (Read-only) -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Your Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        value="{{ auth()->user()->email }}"
                        readonly
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 shadow-sm cursor-not-allowed"
                    >
                </div>

                <!-- Comments -->
                <div class="mb-6">
                    <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Why are you interested in this position?
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        Share your relevant experience, skills, or why you'd be a good fit for this role.
                    </p>
                    <textarea 
                        name="comments" 
                        id="comments" 
                        rows="6"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('comments') border-red-300 @enderror"
                        placeholder="Tell us about yourself and why you're interested..."
                    >{{ old('comments') }}</textarea>
                    @error('comments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('job-listings.show', $jobListing) }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-brand-green text-white text-sm font-medium rounded-md hover:bg-brand-green/90"
                    >
                        <x-heroicon-o-paper-airplane class="w-4 h-4 mr-1.5"/>
                        Submit Application
                    </button>
                </div>
            </form>

            <!-- Position Description Reference -->
            <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Position Description
                </h3>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! $jobListing->parsedDescription !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
