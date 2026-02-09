<div class="p-6">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $application->name }}
                </h3>
                @if($application->status == 'pending')
                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                        Pending
                    </span>
                @elseif($application->status == 'reviewed')
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                        Reviewed
                    </span>
                @elseif($application->status == 'accepted')
                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                        Accepted
                    </span>
                @else
                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                        Rejected
                    </span>
                @endif
            </div>

            <div class="space-y-1 text-sm">
                <p class="text-gray-700 dark:text-gray-300">
                    <span class="font-medium">Email:</span>
                    <a href="mailto:{{ $application->email }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        {{ $application->email }}
                    </a>
                </p>
                @if(!isset($hidePosition) || !$hidePosition)
                    <p class="text-gray-700 dark:text-gray-300">
                        <span class="font-medium">Position:</span>
                        {{ $application->jobListing->position_title }}
                    </p>
                @endif
                <p class="text-gray-500 dark:text-gray-400 text-xs">
                    Applied {{ $application->created_at->diffForHumans() }}
                </p>

                @if($application->claimed_by)
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                            <x-heroicon-s-user class="w-3 h-3 mr-1"/>
                            Claimed by {{ $application->claimedBy->name }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="ml-6 flex flex-col gap-2">
            <!-- View Details Button -->
            <a 
                href="{{ route('job-listings.applicants.show', $application) }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-brand-green text-white text-sm font-medium rounded-md hover:bg-brand-green/90"
            >
                <x-heroicon-o-eye class="w-4 h-4 mr-1.5"/> View Details
            </a>

            <!-- Quick Status Change -->
            <form method="POST" action="{{ route('job-listings.applicants.update', $application) }}" class="inline">
                @csrf
                @method('PATCH')
                <select 
                    name="status" 
                    onchange="this.form.submit()"
                    class="text-xs rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white w-full"
                >
                    <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reviewed" {{ $application->status == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    <option value="accepted" {{ $application->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </form>
        </div>
    </div>
</div>
