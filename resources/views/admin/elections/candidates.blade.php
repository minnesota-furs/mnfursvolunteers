<x-app-layout>
    @section('title', 'Manage Candidates - ' . $election->title)
    <x-slot name="header">
        Manage Candidates: {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.show', $election) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Election
        </a>
        <a href="{{ route('admin.elections.candidates.create', $election) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-user-plus class="w-4 inline"/> Add Candidate
        </a>
        @if($election->resultsAreVisible())
            <a href="{{ route('admin.elections.results', $election) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-chart-bar class="w-4 inline"/> View Results
            </a>
        @else
            <span class="block rounded-md bg-gray-100 px-3 py-2 text-center text-sm font-semibold text-gray-500 cursor-not-allowed">
                <x-heroicon-s-lock-closed class="w-4 inline"/> Results Available After Voting Ends
            </span>
        @endif
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Election Status -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Candidate Management
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($election->requires_approval)
                                    Review and approve candidates for this election
                                @else
                                    View all candidates for this election
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                {{ $candidates->count() }} Total Candidates
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidates List -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    @if($candidates->count() > 0)
                        <div class="space-y-6">
                            @foreach($candidates as $candidate)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 {{ $candidate->approved ? 'bg-green-50 dark:bg-green-900/20' : ($candidate->withdrawn ? 'bg-red-50 dark:bg-red-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20') }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3">
                                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $candidate->user->name }}
                                                </h4>
                                                @if($candidate->approved && !$candidate->withdrawn)
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                                        <x-heroicon-s-check-circle class="w-3 h-3 mr-1"/> Approved
                                                    </span>
                                                @elseif($candidate->withdrawn)
                                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10">
                                                        <x-heroicon-s-x-circle class="w-3 h-3 mr-1"/> Withdrawn
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                                        <x-heroicon-s-clock class="w-3 h-3 mr-1"/> Pending Approval
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="mt-2 space-y-2">
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    <strong>Email:</strong> {{ $candidate->user->email }}
                                                </div>
                                                @if($candidate->user->primary_dept_id)
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                        <strong>Department:</strong> {{ $candidate->user->primaryDepartment->name ?? 'N/A' }}
                                                    </div>
                                                @endif
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    <strong>Nominated:</strong> {{ $candidate->created_at ? $candidate->created_at->format('M j, Y g:i A') : 'Unknown' }}
                                                </div>
                                            </div>

                                            @if($candidate->statement)
                                                <div class="mt-4">
                                                    <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Campaign Statement:</h5>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 p-3 rounded border">
                                                        {{ $candidate->statement }}
                                                    </p>
                                                </div>
                                            @endif

                                            <!-- Vote Count (only visible after voting ends) -->
                                            @if($election->resultsAreVisible())
                                                <div class="mt-3">
                                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                        <x-heroicon-s-chart-bar class="w-4 h-4 mr-1"/> {{ $candidate->votes_count ?? 0 }} votes
                                                    </span>
                                                </div>
                                            @elseif($election->isVotingPeriod())
                                                <div class="mt-3">
                                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-sm font-medium text-yellow-700 ring-1 ring-inset ring-yellow-700/10">
                                                        <x-heroicon-s-clock class="w-4 h-4 mr-1"/> Voting in progress
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-col space-y-2 ml-4">
                                            @if($election->requires_approval && !$candidate->withdrawn)
                                                @if(!$candidate->approved)
                                                    <form action="{{ route('admin.elections.candidates.approve', [$election, $candidate]) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                                            <x-heroicon-s-check class="w-4 h-4 mr-1"/> Approve
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.elections.candidates.reject', [$election, $candidate]) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                                                            onclick="return confirm('Are you sure you want to reject this candidate?');">
                                                            <x-heroicon-s-x-mark class="w-4 h-4 mr-1"/> Reject
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                            
                                            <!-- Remove Candidate Button -->
                                            @php
                                                $hasVotes = $election->votes()->where('candidate_id', $candidate->id)->exists();
                                            @endphp
                                            @if(!$hasVotes)
                                                <form action="{{ route('admin.elections.candidates.destroy', [$election, $candidate]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                        class="inline-flex items-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                                                        onclick="return confirm('Are you sure you want to remove {{ $candidate->user->name }} from this election?');">
                                                        <x-heroicon-s-trash class="w-4 h-4 mr-1"/> Remove
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-500 cursor-not-allowed" title="Cannot remove candidate who has received votes">
                                                    <x-heroicon-s-lock-closed class="w-4 h-4 mr-1"/> Has Votes
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No candidates yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($election->allow_self_nomination)
                                    Candidates can nominate themselves on the public voting page.
                                @else
                                    No candidates have been nominated for this election yet.
                                @endif
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('elections.show', $election) }}" target="_blank"
                                    class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                    <x-heroicon-s-eye class="w-4 h-4 mr-1"/> View Public Page
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>