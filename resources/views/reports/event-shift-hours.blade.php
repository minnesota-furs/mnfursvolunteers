<x-app-layout>
    @section('title', 'Report: Event Shift Hours')
    <x-slot name="header">
        Report: Volunteers by Shift Hours
    </x-slot>

    <x-slot name="actions">
        {{-- intentionally empty --}}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        {{-- Filter form --}}
        <form method="GET" action="{{ route('report.eventShiftHours') }}"
              class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                Select one or more events and set a minimum hours threshold. The report will list every
                volunteer whose total shift hours (for the chosen events) meets or exceeds that threshold.
                No-shows are excluded from the hour totals.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Events --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        <x-heroicon-o-calendar class="w-4 inline mr-1"/>Events
                        <span class="font-normal text-gray-500 ml-1">(select one or more)</span>
                    </h3>
                    <div class="space-y-1 max-h-72 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded p-2">
                        @forelse($events as $event)
                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 rounded px-1 py-0.5">
                                <input type="checkbox" name="event_ids[]" value="{{ $event->id }}"
                                    {{ in_array($event->id, $selectedEventIds) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-green shadow-sm focus:ring-green-200">
                                <span class="text-gray-800 dark:text-gray-200">
                                    {{ $event->name }}
                                    <span class="text-gray-400 text-xs ml-1">{{ $event->start_date->format('M j, Y') }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-400 italic">No events found.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Threshold --}}
                <div class="flex flex-col justify-start gap-4">
                    <div>
                        <label for="min_hours" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            <x-heroicon-o-clock class="w-4 inline mr-1"/>Minimum Hours Threshold
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="min_hours" name="min_hours"
                                   value="{{ $minHours }}" min="0" step="0.5"
                                   class="w-28 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                            <span class="text-sm text-gray-500">hours or more</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">
                            Only volunteers with hours &ge; this value will appear in the results.
                        </p>
                    </div>

                    <div class="mt-auto">
                        <button type="submit"
                                class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                            <x-heroicon-o-magnifying-glass class="w-4 inline mr-1"/> Generate Report
                        </button>
                        @if(request()->has('event_ids'))
                            <a href="{{ route('report.eventShiftHours') }}"
                               class="ml-3 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:underline">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- Results --}}
        @if(request()->has('event_ids'))

            @if($selectedEvents->isEmpty())
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6 text-center text-yellow-800 dark:text-yellow-300">
                    <x-heroicon-o-exclamation-triangle class="w-8 h-8 mx-auto mb-2"/>
                    <p class="font-semibold">No events selected.</p>
                    <p class="text-sm mt-1">Please check at least one event above and re-run the report.</p>
                </div>

            @elseif($results->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-user-group class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                    <p class="font-semibold">No volunteers found with {{ number_format($minHours, 1) }} or more hours.</p>
                    <p class="text-sm mt-1">Try lowering the threshold or selecting additional events.</p>
                </div>

            @else
                {{-- Summary header --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                Results
                                <span class="ml-2 inline-flex items-center rounded-full bg-brand-green/10 px-2.5 py-0.5 text-xs font-medium text-brand-green">
                                    {{ $results->count() }} {{ Str::plural('volunteer', $results->count()) }}
                                </span>
                            </h2>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Showing volunteers with &ge; {{ number_format($minHours, 1) }} shift hours across:
                                {{ $selectedEvents->pluck('name')->join(', ', ' & ') }}
                            </p>
                        </div>
                        <a href="{{ route('report.eventShiftHours.export', array_merge(request()->only('event_ids', 'min_hours'))) }}"
                           class="inline-flex items-center gap-1.5 rounded-md bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                            Export CSV
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volunteer</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Shifts</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Shift Breakdown</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($results as $i => $row)
                                @php
                                    $user    = $row['user'];
                                    $total   = $row['total_hours'];
                                    $shifts  = collect($row['shifts'])->sortBy(fn ($s) => $s['shift']->start_time);
                                    $allCredited = $shifts->every(fn ($s) => $s['credited']);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <a href="{{ route('users.show', $user->id) }}"
                                                   class="text-sm font-medium text-brand-green hover:underline">
                                                    {{ $user->name }}
                                                </a>
                                                @if(Auth::user()->isAdmin())
                                                    <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">
                                        {{ $shifts->count() }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-sm font-bold
                                            {{ $total >= $minHours * 2 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                               : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                            {{ number_format($total, 1) }}h
                                        </span>
                                        @if($allCredited)
                                            <x-heroicon-m-check-badge title="All hours credited" class="w-4 h-4 inline ml-1 text-green-500"/>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 hidden md:table-cell">
                                        <div class="space-y-0.5">
                                            @foreach($shifts as $entry)
                                                @php $shift = $entry['shift']; @endphp
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-gray-400 text-xs whitespace-nowrap">
                                                        {{ $shift->start_time->format('M j, g:i A') }}–{{ $shift->end_time->format('g:i A') }}
                                                    </span>
                                                    <span class="text-gray-700 dark:text-gray-300 truncate max-w-xs">
                                                        {{ $shift->name }}
                                                    </span>
                                                    @if($shift->double_hours)
                                                        <span class="text-xs text-amber-600 font-medium" title="Double hours shift">★ 2×</span>
                                                    @endif
                                                    <span class="ml-auto text-xs font-medium text-gray-500 whitespace-nowrap">
                                                        {{ number_format($entry['hours'], 1) }}h
                                                    </span>
                                                    @if($entry['credited'])
                                                        <x-heroicon-m-check-badge title="Hours credited" class="w-3.5 h-3.5 text-green-500 flex-shrink-0"/>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
