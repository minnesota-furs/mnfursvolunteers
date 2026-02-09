<x-app-layout>
    @section('title', 'Application Details')
    
    <x-slot name="header">
        {{ __('Application Details') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('job-listings.applicants') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Applications
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Application Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $application->name }}
                            </h1>
                            @if($application->status == 'pending')
                                <span class="inline-flex items-center rounded-md bg-yellow-50 px-2.5 py-1 text-sm font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                    Pending
                                </span>
                            @elseif($application->status == 'reviewed')
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                    Reviewed
                                </span>
                            @elseif($application->status == 'accepted')
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    Accepted
                                </span>
                            @else
                                <span class="inline-flex items-centers rounded-md bg-red-50 px-2.5 py-1 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                    Rejected
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Email:</span>
                                <a href="mailto:{{ $application->email }}" class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    {{ $application->email }}
                                </a>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Applied:</span>
                                <span class="ml-2 text-gray-600 dark:text-gray-400">
                                    {{ $application->created_at->format('M j, Y g:i A') }}
                                    ({{ $application->created_at->diffForHumans() }})
                                </span>
                            </div>
                            @if($application->user)
                                <div class="md:col-span-2">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Associated User Account:</span>
                                    <a href="{{ route('users.show', $application->user) }}" 
                                       class="ml-2 inline-flex items-center text-brand-green hover:text-brand-green/80 dark:text-brand-green font-medium">
                                        {{-- <x-heroicon-o-user-circle class="w-4 h-4 mr-1"/> --}}
                                        {{ $application->user->name }}
                                    </a>
                                    @if($application->user->departments->count() > 0)
                                        <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">
                                            Current Departments: 
                                            @foreach($application->user->departments as $dept)
                                                {{ $dept->name }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="ml-6 flex flex-col gap-2">
                        <!-- Claim/Unclaim Button -->
                        @if($application->claimed_by)
                            @if($application->claimed_by === auth()->id() || auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('job-listings.applicants.unclaim', $application) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 font-medium text-sm"
                                    >
                                        <x-heroicon-o-x-circle class="w-4 h-4 inline"/> Unclaim
                                    </button>
                                </form>
                            @endif
                        @else
                            <form method="POST" action="{{ route('job-listings.applicants.claim', $application) }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm"
                                >
                                    <x-heroicon-o-hand-raised class="w-4 h-4 inline"/> Claim Application
                                </button>
                            </form>
                        @endif

                        <!-- Delete Button -->
                        <form method="POST" action="{{ route('job-listings.applicants.delete', $application) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this application?')">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium text-sm"
                            >
                                <x-heroicon-o-trash class="w-4 h-4 inline"/> Delete Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Position Details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-o-briefcase class="w-5 h-5 inline mr-2"/>
                            Position Applied For
                        </h2>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Position Title</span>
                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('job-listings.show', $application->jobListing->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        {{ $application->jobListing->position_title }}
                                    </a>
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Department</span>
                                    <p class="text-base text-gray-900 dark:text-gray-100">{{ $application->jobListing->department->name }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Sector</span>
                                    <p class="text-base text-gray-900 dark:text-gray-100">{{ $application->jobListing->department->sector->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Application Comments -->
                    @if($application->comments)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 inline mr-2"/>
                                Applicant Comments
                            </h2>
                            <div class="prose prose-sm max-w-none dark:prose-invert">
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $application->comments }}</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">No additional comments provided by applicant</p>
                        </div>
                    @endif

                    <!-- Internal Comments Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5 inline mr-2"/>
                            Internal Comments ({{ $application->internalComments->count() }})
                        </h2>

                        <!-- Comment Form -->
                        <form method="POST" action="{{ route('job-listings.applicants.comments.store', $application) }}" class="mb-6">
                            @csrf
                            <div class="mb-3">
                                <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Add a comment
                                </label>
                                <textarea 
                                    name="comment" 
                                    id="comment" 
                                    rows="3"
                                    required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('comment') border-red-300 @enderror"
                                    placeholder="Leave a note about this applicant..."
                                >{{ old('comment') }}</textarea>
                                @error('comment')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-brand-green text-white text-sm font-medium rounded-md hover:bg-brand-green/90"
                            >
                                <x-heroicon-o-plus class="w-4 h-4 mr-1.5"/>
                                Add Comment
                            </button>
                        </form>

                        <!-- Comments List -->
                        @if($application->internalComments->count() > 0)
                            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                                @foreach($application->internalComments as $comment)
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-brand-green text-white rounded-full flex items-center justify-center text-sm font-medium">
                                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $comment->user->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $comment->created_at->format('M j, Y g:i A') }}
                                                        ({{ $comment->created_at->diffForHumans() }})
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line ml-10">{{ $comment->comment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <p class="text-center text-gray-500 dark:text-gray-400 text-sm py-4">
                                    No internal comments yet. Be the first to leave a note!
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Management -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-o-adjustments-horizontal class="w-5 h-5 inline mr-2"/>
                            Status Management
                        </h2>
                        <form method="POST" action="{{ route('job-listings.applicants.update', $application) }}">
                            @csrf
                            @method('PATCH')
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Change Status
                            </label>
                            <select 
                                name="status" 
                                id="status"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white mb-3"
                            >
                                <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="reviewed" {{ $application->status == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="accepted" {{ $application->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <button 
                                type="submit" 
                                class="w-full px-4 py-2 bg-brand-green text-white rounded-md hover:bg-brand-green/90 font-medium text-sm"
                            >
                                Update Status
                            </button>
                        </form>
                    </div>

                    <!-- Claim Information -->
                    @if($application->claimed_by)
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 p-6">
                            <h2 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-3">
                                <x-heroicon-s-user class="w-5 h-5 inline mr-2"/>
                                Claimed By
                            </h2>
                            <p class="text-purple-800 dark:text-purple-200 font-medium">
                                {{ $application->claimedBy->name }}
                            </p>
                            <p class="text-sm text-purple-600 dark:text-purple-300 mt-1">
                                {{ $application->claimed_at->format('M j, Y g:i A') }}
                            </p>
                            <p class="text-xs text-purple-500 dark:text-purple-400 mt-1">
                                {{ $application->claimed_at->diffForHumans() }}
                            </p>
                        </div>
                    @else
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Not yet claimed</p>
                        </div>
                    @endif

                    <!-- Timeline -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            <x-heroicon-o-clock class="w-5 h-5 inline mr-2"/>
                            Timeline
                        </h2>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Application Submitted</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $application->created_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>
                            @if($application->claimed_at)
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-2 h-2 rounded-full bg-purple-500 mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Claimed</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $application->claimed_at->format('M j, Y g:i A') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                            @if($application->updated_at != $application->created_at)
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Last Updated</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $application->updated_at->format('M j, Y g:i A') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
