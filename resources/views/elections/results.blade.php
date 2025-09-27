<x-app-layout>
    @section('title', 'Election Results - ' . $election->title)
    <x-slot name="header">
        Election Results: {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('elections.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Elections
        </a>
        <a href="{{ route('elections.show', $election) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-eye class="w-4 inline"/> View Election Details
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

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Votes Cast</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $totalVotes }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Candidates</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $candidates->count() }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Positions Available</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $election->max_positions }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Voting Ended</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $election->end_date->format('M j, Y g:i A') }}
                            </dd>
                        </div>
                    </div>

                    @if($election->description)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $election->description }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Results -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Final Results
                    </h3>

                    @if($candidates->count() > 0)
                        <div class="space-y-6">
                            @foreach($candidates as $index => $candidate)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6
                                    @if($index < $election->max_positions) 
                                        bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700
                                    @endif">
                                    
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3">
                                                <!-- Position indicator -->
                                                @if($index < $election->max_positions)
                                                    <div class="flex-shrink-0">
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-600 text-white text-sm font-bold">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="flex-shrink-0">
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm font-bold">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    </div>
                                                @endif

                                                <div>
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $candidate->user->name }}
                                                        @if($index < $election->max_positions)
                                                            <span class="ml-2 inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-800 ring-1 ring-inset ring-green-700/10">
                                                                <x-heroicon-s-trophy class="w-3 h-3 mr-1"/> Elected
                                                            </span>
                                                        @endif
                                                    </h4>
                                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $candidate->user->email }}
                                                        @if($candidate->user->primaryDepartment)
                                                            • {{ $candidate->user->primaryDepartment->name }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if($candidate->statement)
                                                <div class="mt-4 ml-11">
                                                    <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Campaign Statement:</h5>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 p-3 rounded border">
                                                        {{ $candidate->statement }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Vote Count and Percentage -->
                                        <div class="ml-6 text-right">
                                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {{ $candidate->votes_count ?? 0 }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                @if($totalVotes > 0)
                                                    {{ number_format(($candidate->votes_count / $totalVotes) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                                {{ Str::plural('vote', $candidate->votes_count ?? 0) }}
                                            </div>

                                            <!-- Vote percentage bar -->
                                            @if($totalVotes > 0)
                                                <div class="mt-2 w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="h-2 rounded-full 
                                                        @if($index < $election->max_positions) bg-green-600 @else bg-gray-400 @endif"
                                                        style="width: {{ min(100, ($candidate->votes_count / $totalVotes) * 100) }}%">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No candidates</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No candidates participated in this election.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>