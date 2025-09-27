<x-app-layout>
    @section('title', $election->title)
    <x-slot name="header">
        {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('elections.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Elections
        </a>
        @if($election->resultsAreVisible())
            <a href="{{ route('elections.results', $election) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-chart-bar class="w-4 inline"/> View Results
            </a>
        @elseif($election->allow_self_nomination && $election->isNominationPeriod() && !$candidates->where('user_id', Auth::id())->count() && $election->userCanBeCandidate(Auth::user()))
            <a href="{{ route('elections.nominate', $election) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-user-plus class="w-4 inline"/> Nominate Yourself
            </a>
        @endif
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Election Information -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            @if($election->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {{ $election->description }}
                                </p>
                            @endif

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                @if($election->hasNominationPeriod())
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nomination Period</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $election->nomination_start_date->format('M j, Y g:i A') }}<br>
                                        to {{ $election->nomination_end_date->format('M j, Y g:i A') }}
                                    </dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Voting Period</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $election->start_date->format('M j, Y g:i A') }}<br>
                                        to {{ $election->end_date->format('M j, Y g:i A') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Positions Available</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $election->max_positions }} {{ Str::plural('position', $election->max_positions) }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @php
                                            $nominationStatus = $election->getNominationStatus();
                                        @endphp
                                        @if($election->isVotingPeriod())
                                            <span class="text-blue-600 font-medium">Voting Open</span><br>
                                            {{ $election->end_date->diffForHumans() }}
                                        @elseif($nominationStatus === 'open')
                                            <span class="text-green-600 font-medium">Nominations Open</span><br>
                                            Ends {{ $election->nomination_end_date->diffForHumans() }}
                                        @elseif($nominationStatus === 'upcoming')
                                            <span class="text-yellow-600 font-medium">Nominations Soon</span><br>
                                            {{ $election->nomination_start_date->diffForHumans() }}
                                        @elseif($nominationStatus === 'closed' && now() < $election->start_date)
                                            <span class="text-orange-600 font-medium">Nominations Closed</span><br>
                                            Voting {{ $election->start_date->diffForHumans() }}
                                        @elseif(now() < $election->start_date)
                                            <span class="text-yellow-600 font-medium">Upcoming</span><br>
                                            Starts {{ $election->start_date->diffForHumans() }}
                                        @else
                                            <span class="text-gray-600 font-medium">Ended</span>
                                        @endif
                                    </dd>
                                </div>
                            </div>

                            <!-- Hours Requirements Info -->
                            @if($election->hasHoursRequirements())
                                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400"/>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                                Volunteer Hours Requirements
                                            </h4>
                                            <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                                @if($election->min_candidate_hours > 0)
                                                    <div>• {{ $election->min_candidate_hours }} hours required to be a candidate</div>
                                                @endif
                                                @if($election->min_voter_hours > 0)
                                                    <div>• {{ $election->min_voter_hours }} hours required to vote</div>
                                                @endif
                                                <div class="mt-1 text-xs">
                                                    Hours must be completed in the current fiscal year. 
                                                    You currently have {{ Auth::user()->getCurrentFiscalYearHours() }} hours.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Voting Status -->
                        <div class="ml-6 flex-shrink-0">
                            @if($userHasVoted)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-3 py-2 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                    <x-heroicon-s-check-circle class="w-5 h-5 mr-2"/> 
                                    @if($election->max_positions > 1)
                                        All votes cast ({{ $userVoteCount }}/{{ $election->max_positions }})
                                    @else
                                        You have voted
                                    @endif
                                </span>
                            @elseif($userVoteCount > 0 && $election->isVotingPeriod())
                                <span class="inline-flex items-center rounded-md bg-yellow-50 px-3 py-2 text-sm font-medium text-yellow-700 ring-1 ring-inset ring-yellow-700/10">
                                    <x-heroicon-s-clock class="w-5 h-5 mr-2"/> {{ $remainingVotes }} {{ Str::plural('vote', $remainingVotes) }} remaining
                                </span>
                            @elseif($election->isVotingPeriod())
                                @if($election->userCanVote(Auth::user()))
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                        <x-heroicon-s-clock class="w-5 h-5 mr-2"/> Voting Open
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-700/10">
                                        <x-heroicon-s-exclamation-triangle class="w-5 h-5 mr-2"/> Insufficient Hours
                                    </span>
                                @endif
                            @elseif(now() < $election->start_date)
                                <span class="inline-flex items-center rounded-md bg-yellow-50 px-3 py-2 text-sm font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                    <x-heroicon-s-clock class="w-5 h-5 mr-2"/> Upcoming
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                    <x-heroicon-s-lock-closed class="w-5 h-5 mr-2"/> Voting Closed
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidates -->
            @if($candidates->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                            @if($userHasVoted || !$election->isVotingPeriod())
                                Candidates
                            @elseif($election->max_positions > 1)
                                Choose Your Candidates (up to {{ $election->max_positions }})
                            @else
                                Choose Your Candidate
                            @endif
                        </h3>

                        @if($remainingVotes > 0 && $election->isVotingPeriod() && $election->userCanVote(Auth::user()))
                            <!-- Voting Instructions -->
                            @if($election->max_positions > 1)
                                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                                    <div class="flex items-center">
                                        <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400 mr-2"/>
                                        <div class="text-sm text-blue-800 dark:text-blue-200">
                                            <strong>Multi-Seat Election:</strong> You can vote for up to {{ $election->max_positions }} candidates.
                                            @if($userVoteCount > 0)
                                                You have {{ $remainingVotes }} {{ Str::plural('vote', $remainingVotes) }} remaining.
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Voting Form -->
                            <form method="POST" action="{{ route('elections.vote', $election) }}" id="voting-form">
                                @csrf
                                <div class="space-y-4">
                                    @foreach($candidates as $candidate)
                                        @php
                                            $alreadyVoted = in_array($candidate->id, $userVotedCandidates);
                                        @endphp
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 
                                            @if($alreadyVoted) bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700
                                            @else hover:bg-gray-50 dark:hover:bg-gray-700 
                                            @endif transition-colors">
                                            
                                            @if($alreadyVoted)
                                                <!-- Already voted for this candidate -->
                                                <div class="flex items-start space-x-4">
                                                    <div class="mt-1">
                                                        <x-heroicon-s-check-circle class="h-5 w-5 text-green-600"/>
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex items-center space-x-3">
                                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                                {{ $candidate->user->name }}
                                                            </h4>
                                                            <span class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-800 ring-1 ring-inset ring-green-700/10">
                                                                Voted
                                                            </span>
                                                        </div>
                                                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $candidate->user->email }}
                                                            @if($candidate->user->primaryDepartment)
                                                                • {{ $candidate->user->primaryDepartment->name }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Available for voting -->
                                                <label class="flex items-start space-x-4 cursor-pointer">
                                                    @if($election->max_positions == 1)
                                                        <input type="radio" name="candidate_id" value="{{ $candidate->id }}" 
                                                            class="mt-1 h-4 w-4 border-gray-300 text-brand-green focus:ring-brand-green candidate-input"
                                                            required>
                                                    @else
                                                        <input type="checkbox" name="candidate_ids[]" value="{{ $candidate->id }}" 
                                                            class="mt-1 h-4 w-4 border-gray-300 text-brand-green focus:ring-brand-green candidate-input"
                                                            data-max-votes="{{ $remainingVotes }}">
                                                    @endif
                                                    
                                                    <div class="flex-1">
                                                        <div class="flex items-center space-x-3">
                                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                                {{ $candidate->user->name }}
                                                            </h4>
                                                            @if($candidate->user_id === Auth::id())
                                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                                    You
                                                                </span>
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $candidate->user->email }}
                                                            @if($candidate->user->primaryDepartment)
                                                                • {{ $candidate->user->primaryDepartment->name }}
                                                            @endif
                                                        </div>

                                                        @if($candidate->statement)
                                                            <div class="mt-3">
                                                                <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Campaign Statement:</h5>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                                                                    {{ $candidate->statement }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6 flex justify-center">
                                    <button type="submit" id="vote-button"
                                        class="inline-flex items-center rounded-md bg-brand-green px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                        onclick="return confirm('Are you sure you want to cast your vote(s)? This action cannot be undone.');">
                                        <x-heroicon-s-check-circle class="w-5 h-5 mr-2"/> 
                                        <span id="vote-button-text">Cast Your Vote</span>
                                    </button>
                                </div>
                            </form>

                            @if($election->max_positions > 1)
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const checkboxes = document.querySelectorAll('input[name="candidate_ids[]"]');
                                        const voteButton = document.getElementById('vote-button');
                                        const voteButtonText = document.getElementById('vote-button-text');
                                        const maxVotes = {{ $remainingVotes }};

                                        function updateVoteButton() {
                                            const checked = document.querySelectorAll('input[name="candidate_ids[]"]:checked').length;
                                            
                                            if (checked === 0) {
                                                voteButton.disabled = true;
                                                voteButtonText.textContent = 'Select at least one candidate';
                                            } else {
                                                voteButton.disabled = false;
                                                const voteText = checked === 1 ? 'Cast Your Vote' : `Cast Your ${checked} Votes`;
                                                voteButtonText.textContent = voteText;
                                            }
                                            
                                            // Disable unchecked boxes if max reached
                                            checkboxes.forEach(checkbox => {
                                                if (!checkbox.checked) {
                                                    checkbox.disabled = checked >= maxVotes;
                                                    checkbox.closest('.border').classList.toggle('opacity-50', checked >= maxVotes);
                                                }
                                            });
                                        }

                                        checkboxes.forEach(checkbox => {
                                            checkbox.addEventListener('change', updateVoteButton);
                                        });

                                        updateVoteButton(); // Initial state
                                    });
                                </script>
                            @endif
                        @elseif(!$userHasVoted && $election->isVotingPeriod() && !$election->userCanVote(Auth::user()))
                            <!-- Insufficient Hours Message -->
                            <div class="text-center py-8">
                                <div class="inline-flex items-center rounded-md bg-red-50 px-4 py-2 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-700/10 mb-4">
                                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 mr-2"/> Insufficient Volunteer Hours
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    You need at least {{ $election->min_voter_hours }} volunteer hours in the current fiscal year to vote in this election.<br>
                                    You currently have {{ Auth::user()->getCurrentFiscalYearHours() }} hours.
                                </p>
                            </div>
                            
                            <!-- Still show candidates for viewing -->
                            <div class="space-y-4 mt-6">
                                @foreach($candidates as $candidate)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 opacity-75">
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $candidate->user->name }}
                                                    </h4>
                                                    @if($candidate->user_id === Auth::id())
                                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                            You
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $candidate->user->email }}
                                                    @if($candidate->user->primaryDepartment)
                                                        • {{ $candidate->user->primaryDepartment->name }}
                                                    @endif
                                                </div>

                                                @if($candidate->statement)
                                                    <div class="mt-3">
                                                        <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Campaign Statement:</h5>
                                                        <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                                                            {{ $candidate->statement }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- Display Only (Already Voted or Voting Closed) -->
                            <div class="space-y-4">
                                @foreach($candidates as $candidate)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $candidate->user->name }}
                                                    </h4>
                                                    @if($candidate->user_id === Auth::id())
                                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                            You
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $candidate->user->email }}
                                                    @if($candidate->user->primaryDepartment)
                                                        • {{ $candidate->user->primaryDepartment->name }}
                                                    @endif
                                                </div>

                                                @if($candidate->statement)
                                                    <div class="mt-3">
                                                        <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Campaign Statement:</h5>
                                                        <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                                                            {{ $candidate->statement }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($userHasVoted)
                                <div class="mt-6 text-center">
                                    <div class="inline-flex items-center rounded-md bg-green-50 px-4 py-2 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                        <x-heroicon-s-check-circle class="w-5 h-5 mr-2"/> Thank you for voting! Your vote has been recorded.
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="text-center py-12">
                            <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No candidates yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($election->allow_self_nomination && $election->isActive())
                                    Be the first to nominate yourself for this election!
                                @else
                                    No candidates have been nominated for this election yet.
                                @endif
                            </p>
                            @if($election->allow_self_nomination && $election->isActive())
                                <div class="mt-6">
                                    <a href="{{ route('elections.nominate', $election) }}"
                                        class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                        <x-heroicon-s-user-plus class="w-4 h-4 mr-1"/> Nominate Yourself
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>