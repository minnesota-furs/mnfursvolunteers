<x-app-layout>
    @section('title', $election->title)
    <x-slot name="header">
        {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Elections
        </a>
        <a href="{{ route('elections.show', $election) }}" target="_blank"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-eye class="w-4 inline"/> View Public Page
        </a>
        <a href="{{ route('admin.elections.edit', $election) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-pencil class="w-4 inline"/> Edit
        </a>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Election Status -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Election Status
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Current status and key metrics for this election
                            </p>
                        </div>
                        <div class="text-right">
                            @if($election->isActive())
                                <span class="inline-flex items-center rounded-md bg-green-50 px-3 py-2 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                    <x-heroicon-s-check-circle class="w-4 h-4 mr-1"/> Active - Voting Open
                                </span>
                            @elseif(now() < $election->start_date)
                                <span class="inline-flex items-center rounded-md bg-yellow-50 px-3 py-2 text-sm font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                    <x-heroicon-s-clock class="w-4 h-4 mr-1"/> Upcoming
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                    <x-heroicon-s-lock-closed class="w-4 h-4 mr-1"/> Voting Closed
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-4">
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Candidates</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $candidates->count() }}</dd>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Voter Turnout</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $uniqueVoters }} / {{ $eligibleVoters }}
                            </dd>
                            <dd class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($eligibleVoters > 0)
                                    {{ number_format(($uniqueVoters / $eligibleVoters) * 100, 1) }}% turnout
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Positions Available</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $election->max_positions }}</dd>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:p-6 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Days Remaining</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                                @if($election->isActive())
                                    {{ now()->diffInDays($election->end_date) }}
                                @else
                                    0
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Election Details -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Election Details
                    </h3>
                    
                    <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                        @if($election->description)
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Description</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <div class="prose prose-sm max-w-none dark:prose-invert">
                                        {!! $election->parsedDescription !!}
                                    </div>
                                </dd>
                            </div>
                        @endif
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Voting Period</dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                {{ $election->start_date->format('M j, Y g:i A') }} - {{ $election->end_date->format('M j, Y g:i A') }}
                            </dd>
                        </div>
                        @if($election->hasHoursRequirements())
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Hours Requirements</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <div class="space-y-1">
                                        @if($election->min_candidate_hours > 0)
                                            <div>Candidate minimum: {{ $election->min_candidate_hours }} hours</div>
                                        @endif
                                        @if($election->min_voter_hours > 0)
                                            <div>Voter minimum: {{ $election->min_voter_hours }} hours</div>
                                        @endif
                                    </div>
                                </dd>
                            </div>
                        @endif
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Settings</dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                <div class="space-y-1">
                                    <div>Self-nomination: {{ $election->allow_self_nomination ? 'Allowed' : 'Not allowed' }}</div>
                                    <div>Candidate approval: {{ $election->requires_approval ? 'Required' : 'Not required' }}</div>
                                    <div>Election status: {{ $election->active ? 'Active' : 'Inactive' }}</div>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Quick Actions
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <a href="{{ route('admin.elections.candidates', $election) }}"
                            class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400"/>
                            <span class="mt-2 block text-sm font-semibold text-gray-900 dark:text-gray-100">
                                Manage Candidates
                            </span>
                            <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                                {{ $candidates->count() }} candidate(s)
                            </span>
                        </a>

                        <a href="{{ route('admin.elections.voters', $election) }}"
                            class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400"/>
                            <span class="mt-2 block text-sm font-semibold text-gray-900 dark:text-gray-100">
                                View Voters
                            </span>
                            <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                                {{ $totalVotes }} vote(s) cast
                            </span>
                        </a>

                        @if($election->resultsAreVisible())
                            <a href="{{ route('admin.elections.results', $election) }}"
                                class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <x-heroicon-o-chart-bar class="mx-auto h-12 w-12 text-gray-400"/>
                                <span class="mt-2 block text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    View Results
                                </span>
                                <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                                    See election results
                                </span>
                            </a>
                        @else
                            <div class="relative block w-full rounded-lg border-2 border-dashed border-gray-200 p-12 text-center bg-gray-50">
                                <x-heroicon-o-lock-closed class="mx-auto h-12 w-12 text-gray-300"/>
                                <span class="mt-2 block text-sm font-semibold text-gray-500">
                                    Results Unavailable
                                </span>
                                <span class="mt-1 block text-xs text-gray-400">
                                    Available after voting ends<br>
                                    {{ $election->end_date->format('M j, Y g:i A') }}
                                </span>
                            </div>
                        @endif

                        <a href="{{ route('elections.show', $election) }}" target="_blank"
                            class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-heroicon-o-eye class="mx-auto h-12 w-12 text-gray-400"/>
                            <span class="mt-2 block text-sm font-semibold text-gray-900 dark:text-gray-100">
                                Public Voting Page
                            </span>
                            <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                                Open in new tab
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>