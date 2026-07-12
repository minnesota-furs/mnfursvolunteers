<x-app-layout>
    <x-slot name="header">
        {{ __('Ledger:') }} {{ $ledger->name }}
    </x-slot>

    <x-slot name="actions">
        @if (Auth::user()->isAdmin())
            <a href="{{ route('ledger.edit', $ledger->id) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Edit
            </a>
        @endif
    </x-slot>

    <x-slot name="postHeader">
        <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Hours</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ format_hours($ledger->totalVolunteerHours()) }}
                </dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Volunteers</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ $volunteerCount }}
                </dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Hour Entries</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ $entryCount }}
                </dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow-lg sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Elections</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ $electionCount }}
                </dd>
            </div>
        </dl>
    </x-slot>

    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
        <dt class="text-sm font-medium leading-6 text-gray-900">Date Range</dt>
        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
            {{ $ledger->start_date->format('F j, Y') }} &ndash; {{ $ledger->end_date->format('F j, Y') }}
        </dd>
    </div>

    <div class="px-4 py-6 sm:px-0">
        <dt class="text-sm font-medium leading-6 text-gray-900 mb-4">Top Volunteers</dt>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volunteer</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($topVolunteers as $i => $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($row->user)
                                    <a href="{{ route('users.show', $row->user->id) }}" class="text-sm font-medium text-brand-green hover:underline">
                                        {{ $row->user->name }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">Unknown volunteer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                    {{ format_hours($row->total_hours) }}h
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500 text-center" colspan="3">No hours have been logged for this ledger yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-slot name="right">
        <p class="py-4 text-justify">A Fiscal Ledger represents a specific reporting period, such as a fiscal year, within which an
            organization's activities are tracked and recorded. This page summarizes volunteer activity logged
            against <b>{{ $ledger->name }}</b>.</p>
        <a href="{{ route('ledgers.export-csv', $ledger->id) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-s-arrow-up-on-square-stack class="w-4 inline"/> Export Hour Report (CSV)
        </a>
    </x-slot>
</x-app-layout>
