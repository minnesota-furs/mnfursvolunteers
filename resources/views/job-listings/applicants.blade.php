<x-app-layout>
    @section('title', 'Job Applications')
    
    <x-slot name="header">
        {{ __('Job Applications') }}
    </x-slot>

    <x-slot name="actions">
        <div class="flex gap-2">
            <a href="{{ route('job-listings.applicants.create') }}"
                class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-brand-green/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                <x-heroicon-o-plus class="w-4 inline"/> Add Application
            </a>
            <a href="{{ route('job-listings.index') }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-o-briefcase class="w-4 inline"/> View Job Listings
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Application Management</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                        Review and manage job applications from potential candidates.
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <!-- View Type Toggle -->
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <a href="{{ route('job-listings.applicants', array_merge(request()->except('view'), ['view' => 'individuals'])) }}"
                           class="px-4 py-2 text-sm font-medium {{ request('view', 'individuals') === 'individuals' ? 'bg-brand-green text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} border border-gray-200 dark:border-gray-600 rounded-l-lg">
                            <x-heroicon-o-queue-list class="w-4 h-4 inline mr-1"/> Individuals
                        </a>
                        <a href="{{ route('job-listings.applicants', array_merge(request()->except('view'), ['view' => 'grouped'])) }}"
                           class="px-4 py-2 text-sm font-medium {{ request('view') === 'grouped' ? 'bg-brand-green text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} border border-gray-200 dark:border-gray-600 rounded-r-lg">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4 inline mr-1"/> Grouped
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <form method="GET" action="{{ route('job-listings.applicants') }}" class="flex flex-col gap-4">
                    <input type="hidden" name="view" value="{{ request('view', 'individuals') }}">
                    
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                        <!-- Status Filter -->
                    <div class="flex-1 w-full sm:w-auto">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <x-heroicon-o-funnel class="w-4 h-4 inline mb-0.5" /> Status
                        </label>
                        <select 
                            name="status" 
                            id="status" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Job Listing Filter -->
                    <div class="flex-1 w-full sm:w-auto">
                        <label for="job_listing" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <x-heroicon-o-briefcase class="w-4 h-4 inline mb-0.5" /> Position
                        </label>
                        <select 
                            name="job_listing" 
                            id="job_listing" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">All Positions</option>
                            @foreach ($jobListings as $listing)
                                <option value="{{ $listing->id }}" {{ request('job_listing') == $listing->id ? 'selected' : '' }}>
                                    {{ $listing->position_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sector Filter -->
                    <div class="flex-1 w-full sm:w-auto">
                        <label for="sector" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4 inline mb-0.5" /> Sector
                        </label>
                        <select 
                            name="sector" 
                            id="sector" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">All Sectors</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ request('sector') == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-brand-green text-white text-sm font-medium rounded-md hover:bg-brand-green/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-green"
                            >
                                <x-heroicon-o-funnel class="w-4 h-4 mr-1.5" />
                                Apply
                            </button>
                            @if(request()->hasAny(['status', 'job_listing', 'sector', 'show_mine']))
                                <a 
                                    href="{{ route('job-listings.applicants', ['view' => request('view', 'individuals')]) }}" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
                                >
                                    <x-heroicon-o-x-mark class="w-4 h-4 mr-1.5" />
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Show Mine Checkbox -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="show_mine" 
                            id="show_mine" 
                            value="1"
                            {{ request('show_mine') ? 'checked' : '' }}
                            onchange="this.form.submit()"
                            class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green"
                        />
                        <label for="show_mine" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <x-heroicon-o-user class="w-4 h-4 inline mb-0.5" /> Show only my claimed applications
                        </label>
                    </div>
                </form>
            </div>

            <!-- Applications List -->
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        @if($viewType === 'individuals')
                            {{ $applications->links('vendor.pagination.custom') }}
                        @endif

                        @if($applications->isEmpty())
                            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg">
                                <x-heroicon-o-inbox class="mx-auto h-12 w-12 text-gray-400"/>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No applications</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No applications match your current filters.</p>
                            </div>
                        @else
                            @if($viewType === 'grouped')
                                {{-- Grouped View --}}
                                <div class="space-y-6">
                                    @foreach($applications as $jobListingId => $listingApplications)
                                        @php
                                            $jobListing = $listingApplications->first()->jobListing;
                                        @endphp
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                            {{ $jobListing->position_title }}
                                                        </h3>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $jobListing->department->name }} • {{ $jobListing->department->sector->name }}
                                                        </p>
                                                    </div>
                                                    <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-3 py-1 text-sm font-medium text-blue-800 dark:text-blue-200">
                                                        {{ $listingApplications->count() }} {{ Str::plural('application', $listingApplications->count()) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($listingApplications as $application)
                                                    @include('job-listings.partials.application-card', ['application' => $application, 'hidePosition' => true])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- Individuals View --}}
                                <div class="space-y-4">
                                    @foreach($applications as $application)
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                            @include('job-listings.partials.application-card', ['application' => $application, 'hidePosition' => false])
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6">
                                    {{ $applications->links('vendor.pagination.custom') }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
