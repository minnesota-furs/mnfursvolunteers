<x-app-layout>
    @section('title', 'Report: No-Shows')
    <x-slot name="header">
        Report: No-Shows
    </x-slot>

    <x-slot name="actions">
        {{-- intentionally empty --}}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 flex items-center gap-4">
                <div class="rounded-full bg-red-100 dark:bg-red-900/30 p-3">
                    <x-heroicon-s-x-circle class="w-6 h-6 text-red-500"/>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalNoShows }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total No-Shows</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 flex items-center gap-4">
                <div class="rounded-full bg-amber-100 dark:bg-amber-900/30 p-3">
                    <x-heroicon-s-users class="w-6 h-6 text-amber-500"/>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $uniqueNoShowUsers }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Unique Volunteers</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 flex items-center gap-4">
                <div class="rounded-full bg-orange-100 dark:bg-orange-900/30 p-3">
                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-orange-500"/>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $repeatOffenders }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Repeat Offenders (2+)</p>
                </div>
            </div>
        </div>

        {{-- Recent no-shows (last 30 days) --}}
        @if($recentNoShows->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                    <x-heroicon-o-clock class="w-4 h-4 text-red-500"/>
                    Recent No-Shows (Last 30 Days)
                </h3>
                <div class="space-y-2">
                    @foreach($recentNoShows as $entry)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 px-3 py-2">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('users.show', $entry->user_id) }}"
                                   class="text-sm font-medium text-brand-green hover:underline">
                                    {{ $entry->user_name }}
                                </a>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $entry->shift_name }} &mdash; {{ $entry->event_name }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ \Carbon\Carbon::parse($entry->no_show_marked_at)->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('report.noShows') }}"
              class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                {{-- Event filter --}}
                <div>
                    <label for="event_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event</label>
                    <select name="event_id" id="event_id"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ (string)$eventId === (string)$event->id ? 'selected' : '' }}>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search --}}
                <div class="flex-1 w-full sm:w-auto">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}"
                           placeholder="Search by name or email..."
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-o-funnel class="w-4 inline mr-1"/> Filter
                    </button>
                    @if($eventId || $search)
                        <a href="{{ route('report.noShows') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:underline">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Results table --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            @if($users->isEmpty())
                <div class="p-12 text-center">
                    <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-green-400"/>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No No-Shows Found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($eventId || $search)
                            No results match the current filters.
                        @else
                            There are no recorded no-shows.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                @php
                                    $sortLink = fn(string $col, string $label) =>
                                        '<a href="' . route('report.noShows', array_merge(request()->query(), ['sort' => $col, 'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc'])) . '" '
                                        . 'class="group inline-flex items-center gap-1 hover:underline">'
                                        . e($label)
                                        . ($sort === $col ? ($direction === 'asc' ? ' ↑' : ' ↓') : '')
                                        . '</a>';
                                @endphp
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {!! $sortLink('name', 'Volunteer') !!}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {!! $sortLink('email', 'Email') !!}
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {!! $sortLink('no_show_count', 'No-Shows') !!}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {!! $sortLink('latest_no_show', 'Last No-Show') !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($users as $user)
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <a href="{{ route('users.show', $user->id) }}"
                                           class="text-sm font-medium text-brand-green hover:underline">
                                            {{ $user->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold
                                            {{ $user->no_show_count >= 3 ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' :
                                               ($user->no_show_count >= 2 ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' :
                                               'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300') }}">
                                            {{ $user->no_show_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($user->latest_no_show)
                                            {{ \Carbon\Carbon::parse($user->latest_no_show)->format('M j, Y') }}
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                ({{ \Carbon\Carbon::parse($user->latest_no_show)->diffForHumans() }})
                                            </span>
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
