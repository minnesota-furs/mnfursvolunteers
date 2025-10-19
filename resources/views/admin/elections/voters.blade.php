<x-app-layout>
    @section('title', 'Voters - ' . $election->title)
    <x-slot name="header">
        Voters: {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.show', $election) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Election
        </a>
        <a href="{{ route('admin.elections.candidates', $election) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-user-group class="w-4 inline"/> View Candidates
        </a>
        @if($election->resultsAreVisible())
            <a href="{{ route('admin.elections.results', $election) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-chart-bar class="w-4 inline"/> View Results
            </a>
        @endif
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Election Info -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Voter Participation
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                This shows who voted and when, but <strong>not</strong> who they voted for to maintain ballot secrecy.
                            </p>
                        </div>
                        <div class="text-right space-y-2">
                            <div>
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                    {{ $totalVoters }} {{ Str::plural('Voter', $totalVoters) }}
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center rounded-md bg-green-50 px-3 py-2 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                    {{ $totalVotes }} Total {{ Str::plural('Vote', $totalVotes) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($election->max_positions > 1)
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                <x-heroicon-s-information-circle class="w-4 h-4 inline"/> 
                                This election allows up to {{ $election->max_positions }} votes per person.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Voters List -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    @if($voters->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Voter
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Vol Code
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Votes Cast
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Voted At
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($voters as $voter)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $voter['user']->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $voter['user']->vol_code }}</code>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $voter['user']->email }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-700/10">
                                                    {{ $voter['vote_count'] }} {{ Str::plural('vote', $voter['vote_count']) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($voter['voted_at'])->format('M j, Y g:i A') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No votes yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                No one has voted in this election yet.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
