<x-app-layout>
    @section('title', 'Shift Tag Report – ' . $event->name)
    <x-slot name="header">
        Shift Tag Report: {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            ← Back to Shifts
        </a>
        <a href="{{ route('admin.shift-tag-report') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-o-arrow-trending-up class="w-4 inline"/> Cross-Event Report
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        {{-- Summary table --}}
        @if($stats->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Summary by Tag</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tag</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Shifts</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Slots</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Filled</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fill Rate</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No-Shows</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hrs Credited</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($stats as $row)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if($row['tag']->color)
                                            <span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color:{{ $row['tag']->color }}"></span>
                                        @endif
                                        <a href="#tag-{{ $row['tag']->id }}" class="hover:underline text-brand-green">{{ $row['tag']->name }}</a>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $row['shifts'] }}</td>
                                <td class="px-6 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $row['slots'] }}</td>
                                <td class="px-6 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $row['filled'] }}</td>
                                <td class="px-6 py-3 text-center text-sm">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold
                                        {{ $row['fill_rate'] >= 100 ? 'bg-green-100 text-green-800' : ($row['fill_rate'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $row['fill_rate'] }}%
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $row['no_shows'] }}</td>
                                <td class="px-6 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $row['credited'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
                <x-heroicon-o-tag class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                <p class="font-semibold">No tagged shifts found for this event.</p>
                <p class="text-sm mt-1">Add tags to shifts when creating or editing them.</p>
            </div>
        @endif

        {{-- Per-tag shift detail --}}
        @foreach($tags as $tag)
            <div id="tag-{{ $tag->id }}" class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                    @if($tag->color)
                        <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background-color:{{ $tag->color }}"></span>
                    @endif
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $tag->name }}</h2>
                    @if($tag->description)
                        <span class="text-sm text-gray-500 dark:text-gray-400">— {{ $tag->description }}</span>
                    @endif
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Slots</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volunteers</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($tag->shifts->sortBy('start_time') as $shift)
                            @php
                                $filled = $shift->users->count();
                                $full = $filled >= $shift->max_volunteers;
                            @endphp
                            <tr>
                                <td class="px-6 py-3 text-sm font-medium">
                                    <a href="{{ route('admin.events.shifts.edit', [$event, $shift]) }}" class="text-brand-green hover:underline">
                                        {{ $shift->name }}
                                    </a>
                                    @if($shift->double_hours)
                                        <span class="ml-1 text-xs text-amber-600" title="Double hours">★ 2×</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    {{ $shift->start_time->format('D g:i A') }} – {{ $shift->end_time->format('g:i A') }}
                                </td>
                                <td class="px-6 py-3 text-center text-sm">
                                    <span class="{{ $full ? 'text-green-700 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ $filled }} / {{ $shift->max_volunteers }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    @forelse($shift->users as $user)
                                        <span class="inline-flex items-center mr-2 {{ $user->pivot->no_show ? 'line-through text-red-500' : '' }}">
                                            <a href="{{ route('users.show', $user->id) }}" class="hover:underline text-gray-700 dark:text-gray-300">{{ $user->name }}</a>
                                            @if($user->pivot->no_show)
                                                <span class="ml-1 text-xs text-red-500">(no-show)</span>
                                            @endif
                                            @if($user->pivot->hours_logged_at)
                                                <x-heroicon-m-check-badge title="Hours credited" class="w-3.5 h-3.5 ml-1 text-green-600 inline"/>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-gray-400 italic text-xs">No volunteers</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <x-slot name="right">
        <p class="py-4 text-sm text-gray-600 dark:text-gray-400">
            This report shows volunteer coverage grouped by shift tag for
            <strong>{{ $event->name }}</strong>.
            Tags can be added or changed on the shift's edit page.
        </p>
        <p class="py-2 text-sm text-gray-600 dark:text-gray-400">
            To compare tag coverage across multiple events, use the
            <a href="{{ route('admin.shift-tag-report') }}" class="text-brand-green hover:underline">Cross-Event Tag Report</a>.
        </p>
    </x-slot>
</x-app-layout>
