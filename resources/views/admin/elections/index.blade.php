<x-app-layout>
    @section('title', 'Manage Elections')
    <x-slot name="header">
        {{ __('Manage Elections') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.elections.create')}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Create New Election
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flow-root">
                    <div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col"
                                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-0">
                                            Title</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            Start Date</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-32">
                                            End Date</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-16">
                                            Candidates</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-16">
                                            Votes</th>
                                        <th scope="col"
                                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 w-16">
                                            Status</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0 w-32">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($elections as $election)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-0">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $election->title }}
                                                        </div>
                                                        @if($election->description)
                                                            <div class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                                                {{ Str::limit($election->description, 50) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                {{ $election->start_date->format('M j, Y') }}<br>
                                                <span class="text-xs">{{ $election->start_date->format('g:i A') }}</span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                {{ $election->end_date->format('M j, Y') }}<br>
                                                <span class="text-xs">{{ $election->end_date->format('g:i A') }}</span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                    {{ $election->candidates->count() }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                @if($election->resultsAreVisible())
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                                        {{ $election->votes->count() }}
                                                    </span>
                                                @elseif($election->isVotingPeriod())
                                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-700/10">
                                                        In Progress
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-700/10">
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                @if($election->isActive())
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                                        Active
                                                    </span>
                                                @elseif(now() < $election->start_date)
                                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                                        Upcoming
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                        Ended
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('admin.elections.show', $election) }}"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        View
                                                    </a>
                                                    <a href="{{ route('admin.elections.candidates', $election) }}"
                                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                        Candidates
                                                    </a>
                                                    @if($election->resultsAreVisible())
                                                        <a href="{{ route('admin.elections.results', $election) }}"
                                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                            Results
                                                        </a>
                                                    @else
                                                        <span class="text-gray-400 cursor-not-allowed" title="Results available after voting ends">
                                                            Results
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No elections found. 
                                                <a href="{{ route('admin.elections.create') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    Create the first election
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>