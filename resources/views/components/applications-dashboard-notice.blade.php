@props(['unclaimedPendingCount', 'claimedApplications'])

@can('manage-staff-applications')
    @if($unclaimedPendingCount > 0)
        <div class="mt-5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-600 dark:text-yellow-400"/>
                    <div>
                        <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">
                            {{ $unclaimedPendingCount }} Unclaimed Application{{ $unclaimedPendingCount !== 1 ? 's' : '' }} Pending Review
                        </h3>
                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-0.5">
                            These applications are waiting to be claimed and reviewed.
                        </p>
                    </div>
                </div>
                <a href="{{ route('job-listings.applicants', ['status' => 'pending']) }}" 
                   class="px-4 py-2 text-sm font-medium text-yellow-800 dark:text-yellow-200 bg-yellow-100 dark:bg-yellow-900/40 hover:bg-yellow-200 dark:hover:bg-yellow-900/60 rounded-md border border-yellow-300 dark:border-yellow-700 transition">
                    View Applications
                </a>
            </div>
        </div>
    @endif

    @if($claimedApplications->count() > 0)
        <div class="mt-5 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-inbox class="w-5 h-5 inline mb-1"/> 
                    Your Pending Staff Applications ({{ $claimedApplications->count() }})
                </h2>
                <a href="{{ route('job-listings.applicants', ['show_mine' => '1']) }}" 
                   class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    View All
                </a>
            </div>
            <div class="space-y-3">
                @foreach($claimedApplications as $application)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-brand-green dark:hover:border-brand-green transition">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('job-listings.applicants.show', $application) }}" 
                                   class="font-medium text-gray-900 dark:text-gray-100 hover:text-brand-green">
                                    {{ $application->name }}
                                </a>
                                @if($application->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-yellow-50 dark:bg-yellow-900/20 px-2 py-1 text-xs font-medium text-yellow-800 dark:text-yellow-200 ring-1 ring-inset ring-yellow-600/20">
                                        Pending
                                    </span>
                                @elseif($application->status === 'reviewed')
                                    <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-200 ring-1 ring-inset ring-blue-700/10">
                                        Reviewed
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $application->jobListing->position_title }} • {{ $application->email }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                Applied {{ $application->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <a href="{{ route('job-listings.applicants.show', $application) }}" 
                           class="ml-4 px-3 py-1.5 text-sm font-medium text-brand-green hover:bg-brand-green hover:text-white rounded-md border border-brand-green transition">
                            Review
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endcan
