<x-app-layout>
    @section('title', 'Board Elections')
    <x-slot name="header">
        {{ __('Board Elections') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($elections->count() > 0)
                <div class="space-y-6">
                    @foreach($elections as $election)
                        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $election->title }}
                                        </h3>
                                        
                                        @if($election->description)
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                {{ $election->description }}
                                            </p>
                                        @endif

                                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
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
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Candidates</dt>
                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $election->candidates->count() }} {{ Str::plural('candidate', $election->candidates->count()) }}
                                                </dd>
                                            </div>
                                        </div>

                                        <!-- Hours Requirements -->
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
                                                            <div class="mt-1 text-xs">Hours must be completed in the current fiscal year</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Voting Status -->
                                        <div class="mt-4">
                                            @if($election->userHasVoted(Auth::user()))
                                                <div class="inline-flex items-center rounded-md bg-green-50 px-3 py-2 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                                    <x-heroicon-s-check-circle class="w-4 h-4 mr-2"/> You have voted in this election
                                                </div>
                                            @elseif($election->isVotingPeriod())
                                                @if($election->userCanVote(Auth::user()))
                                                    <div class="inline-flex items-center rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                        <x-heroicon-s-clock class="w-4 h-4 mr-2"/> Voting is open
                                                    </div>
                                                @else
                                                    <div class="inline-flex items-center rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-700/10">
                                                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 mr-2"/> Insufficient volunteer hours to vote
                                                    </div>
                                                @endif
                                            @elseif(now() < $election->start_date)
                                                <div class="inline-flex items-center rounded-md bg-yellow-50 px-3 py-2 text-sm font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                                    <x-heroicon-s-clock class="w-4 h-4 mr-2"/> Voting opens {{ $election->start_date->diffForHumans() }}
                                                </div>
                                            @else
                                                <div class="inline-flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                    <x-heroicon-s-lock-closed class="w-4 h-4 mr-2"/> Voting has ended
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="ml-6 flex-shrink-0">
                                        @php
                                            $linkRoute = ($election->userHasVoted(Auth::user()) && $election->resultsAreVisible()) 
                                                ? route('elections.results', $election) 
                                                : route('elections.show', $election);
                                        @endphp
                                        <a href="{{ $linkRoute }}"
                                            class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                            @if($election->userHasVoted(Auth::user()) && $election->resultsAreVisible())
                                                <x-heroicon-s-chart-bar class="w-4 h-4 mr-1"/> View Results
                                            @elseif($election->userHasVoted(Auth::user()) && !$election->resultsAreVisible())
                                                <x-heroicon-s-check-circle class="w-4 h-4 mr-1"/> Vote Cast
                                            @elseif($election->isVotingPeriod())
                                                @if($election->userCanVote(Auth::user()))
                                                    <x-heroicon-s-check-circle class="w-4 h-4 mr-1"/> Vote Now
                                                @else
                                                    <x-heroicon-s-eye class="w-4 h-4 mr-1"/> View Details
                                                @endif
                                            @else
                                                <x-heroicon-s-eye class="w-4 h-4 mr-1"/> View Details
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    {{-- <x-heroicon-o-ballot class="mx-auto h-12 w-12 text-gray-400"/> --}}
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No active elections</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        There are currently no board elections accepting votes. Check back later or contact an administrator for more information.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>