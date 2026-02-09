@props(['activeElections'])

@feature('elections')
    @if ($activeElections->count() > 0)
        <div class="mt-5">
            <div
                class="overflow-hidden rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-2 border-blue-200 dark:border-blue-700 px-4 py-5 shadow-lg sm:p-6">
                <h3 class="text-xl font-bold mb-3 text-blue-800 dark:text-blue-200 flex items-center">
                    <x-heroicon-o-check-badge class="w-6 h-6 mr-2" />
                    Active Elections ({{ $activeElections->count() }})
                </h3>
                <div class="space-y-4">
                    @foreach ($activeElections as $election)
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-blue-100 dark:border-blue-700">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $election->name }}
                                    </h4>
                                    @if ($election->description)
                                        <div class="text-gray-600 dark:text-gray-300 mt-1 prose prose-sm max-w-none">
                                            {!! $election->parsedDescription !!}
                                        </div>
                                    @endif

                                    @php
                                        $now = now();
                                        $isNominationPeriod =
                                            $election->nomination_start_date &&
                                            $now >= $election->nomination_start_date &&
                                            $now <= $election->nomination_end_date;
                                        $isVotingPeriod = $now >= $election->start_date && $now <= $election->end_date;
                                    @endphp

                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                                        @if ($isNominationPeriod)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                                Nomination Period
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                Ends {{ $election->nomination_end_date->format('M j, Y g:i A') }}
                                            </span>
                                        @elseif($isVotingPeriod)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                Voting Open
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                Ends {{ $election->end_date->format('M j, Y g:i A') }}
                                            </span>
                                            @if ($election->userHasVoted)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                    <x-heroicon-s-check-circle class="w-4 h-4 mr-1" />
                                                    You've Voted
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <a href="{{ route('elections.show', $election) }}"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        @if ($isNominationPeriod)
                                            View Nominations
                                        @elseif($isVotingPeriod)
                                            Vote Now
                                        @else
                                            View Election
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endfeature
