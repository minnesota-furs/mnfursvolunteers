<x-app-layout>
    @section('title', 'Election Results - ' . $election->title)
    <x-slot name="header">
        Election Results: {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.show', $election) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Election
        </a>
        <a href="{{ route('admin.elections.export-voter-turnout', $election) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-arrow-down-tray class="w-4 inline"/> Export Voter Turnout
        </a>
        <a href="{{ route('admin.elections.candidates', $election) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-users class="w-4 inline"/> Manage Candidates
        </a>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Results Overview -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Election Results Overview
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-5">
                        <div class="bg-blue-50 dark:bg-blue-900/20 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 truncate">Unique Voters</dt>
                            <dd class="mt-1 text-3xl font-semibold text-blue-900 dark:text-blue-100">{{ $uniqueVoters }}</dd>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">Total Votes Cast</dt>
                            <dd class="mt-1 text-3xl font-semibold text-indigo-900 dark:text-indigo-100">{{ $totalVotes }}</dd>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-green-600 dark:text-green-400 truncate">Eligible Voters</dt>
                            <dd class="mt-1 text-3xl font-semibold text-green-900 dark:text-green-100">{{ $eligibleVoters }}</dd>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-purple-600 dark:text-purple-400 truncate">Turnout Rate</dt>
                            <dd class="mt-1 text-3xl font-semibold text-purple-900 dark:text-purple-100">
                                @if($eligibleVoters > 0)
                                    {{ round(($uniqueVoters / $eligibleVoters) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </dd>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/20 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-orange-600 dark:text-orange-400 truncate">Positions Available</dt>
                            <dd class="mt-1 text-3xl font-semibold text-orange-900 dark:text-orange-100">{{ $election->max_positions }}</dd>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Voting Period:</strong> 
                            {{ $election->start_date->format('M j, Y g:i A') }} - {{ $election->end_date->format('M j, Y g:i A') }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <strong>Election Status:</strong> 
                            @if($election->isActive())
                                <span class="text-green-600 dark:text-green-400">Voting in progress</span>
                            @elseif(now() < $election->start_date)
                                <span class="text-yellow-600 dark:text-yellow-400">Not yet started</span>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">Voting closed</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Candidate Results
                    </h3>

                    @if($candidates->count() > 0)
                        <div class="space-y-4">
                            @foreach($candidates as $index => $candidate)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 
                                    {{ $index < $election->max_positions ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700' : 'bg-gray-50 dark:bg-gray-700' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <!-- Position Indicator -->
                                            <div class="flex-shrink-0">
                                                @if($index < $election->max_positions)
                                                    <div class="flex items-center justify-center w-12 h-12 bg-green-600 text-white rounded-full font-bold text-lg">
                                                        {{ $index + 1 }}
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-center w-12 h-12 bg-gray-400 text-white rounded-full font-bold text-lg">
                                                        {{ $index + 1 }}
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Candidate Info -->
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $candidate->user->name }}
                                                    </h4>
                                                    @if($index < $election->max_positions)
                                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                                            <x-heroicon-s-trophy class="w-4 h-4 mr-1"/> Elected
                                                        </span>
                                                    @endif
                                                    @if(!$candidate->approved)
                                                        <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                                            Pending Approval
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $candidate->user->email }}
                                                    @if($candidate->user->primaryDepartment)
                                                        • {{ $candidate->user->primaryDepartment->name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Vote Count and Percentage -->
                                        <div class="text-right">
                                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {{ $candidate->votes_count ?? 0 }}
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                @if($totalVotes > 0)
                                                    {{ round((($candidate->votes_count ?? 0) / $totalVotes) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Vote Progress Bar -->
                                    @if($totalVotes > 0)
                                        <div class="mt-4">
                                            <div class="bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                <div class="{{ $index < $election->max_positions ? 'bg-green-600' : 'bg-blue-600' }} h-2 rounded-full" 
                                                     style="width: {{ round((($candidate->votes_count ?? 0) / $totalVotes) * 100, 1) }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Campaign Statement -->
                                    @if($candidate->statement)
                                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                            <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Campaign Statement:</h5>
                                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $candidate->statement }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($totalVotes == 0)
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No votes have been cast yet.
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-chart-bar class="mx-auto h-12 w-12 text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No candidates</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                There are no candidates registered for this election yet.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>