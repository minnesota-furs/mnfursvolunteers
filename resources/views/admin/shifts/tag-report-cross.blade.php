<x-app-layout>
    @section('title', 'Cross-Event Shift Tag Report')
    <x-slot name="header">
        Cross-Event Shift Tag Report
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            ← Events
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        {{-- Filter form --}}
        <form method="GET" action="{{ route('admin.shift-tag-report') }}" class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Events --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        <x-heroicon-o-calendar class="w-4 inline mr-1"/>Events
                        <span class="font-normal text-gray-500 ml-1">(leave blank for all)</span>
                    </h3>
                    <div class="space-y-1 max-h-56 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded p-2">
                        @forelse($events as $event)
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
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

                {{-- Tags --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        <x-heroicon-o-tag class="w-4 inline mr-1"/>Tags
                        <span class="font-normal text-gray-500 ml-1">(required)</span>
                    </h3>
                    <div class="space-y-1 max-h-56 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded p-2">
                        @forelse($tags as $tag)
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                    {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-green shadow-sm focus:ring-green-200">
                                <span class="inline-flex items-center text-gray-800 dark:text-gray-200">
                                    @if($tag->color)
                                        <span class="inline-block w-3 h-3 rounded-full mr-1.5" style="background-color:{{ $tag->color }}"></span>
                                    @endif
                                    {{ $tag->name }}
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-400 italic">
                                No tags found.
                                <a href="{{ route('admin.tags.create') }}" class="text-brand-green hover:underline">Create tags</a>.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                    class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                    <x-heroicon-o-magnifying-glass class="w-4 inline mr-1"/> Generate Report
                </button>
            </div>
        </form>

        {{-- Results --}}
        @if(request()->hasAny(['tag_ids', 'event_ids']))
            @if($report->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-tag class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                    <p class="font-semibold">No shifts found matching your selection.</p>
                    <p class="text-sm mt-1">Try selecting different tags or events.</p>
                </div>
            @else

                {{-- Summary table --}}
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Summary by Tag</h2>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tag</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Shifts</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Slots</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Filled</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fill Rate</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">No-Shows</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hrs Credited</th>
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
                                    <td class="px-6 py-3 text-center text-sm text-gray-700 dark:text-gray-300">
                                        {{ $row['tag']->shifts->pluck('event_id')->unique()->count() }}
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

                {{-- Per-tag detail, grouped by event --}}
                @foreach($report as $tag)
                    <div id="tag-{{ $tag->id }}" class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                            @if($tag->color)
                                <span class="inline-block w-4 h-4 rounded-full flex-shrink-0" style="background-color:{{ $tag->color }}"></span>
                            @endif
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $tag->name }}</h2>
                            @if($tag->description)
                                <span class="text-sm text-gray-500">— {{ $tag->description }}</span>
                            @endif
                        </div>

                        @foreach($tag->shifts->groupBy('event_id') as $eventId => $eventShifts)
                            @php $eventModel = $eventShifts->first()->event; @endphp
                            <div class="px-6 pt-4 pb-1">
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    <x-heroicon-o-calendar class="w-4 inline mr-1 text-gray-400"/>
                                    <a href="{{ route('admin.events.shifts.index', $eventModel) }}" class="hover:underline text-brand-green">
                                        {{ $eventModel->name }}
                                    </a>
                                    <span class="text-gray-400 font-normal ml-2 text-xs">
                                        {{ $eventModel->start_date->format('M j, Y') }}
                                    </span>
                                    <a href="{{ route('admin.events.shift-tag-report', $eventModel) }}"
                                        class="ml-3 text-xs text-blue-600 hover:underline">
                                        View event report →
                                    </a>
                                </h3>
                            </div>

                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mb-2">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                                        <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                        <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Slots</th>
                                        <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volunteers</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($eventShifts->sortBy('start_time') as $shift)
                                        @php $filled = $shift->users->count(); @endphp
                                        <tr>
                                            <td class="px-6 py-3 text-sm font-medium">
                                                <a href="{{ route('admin.events.shifts.edit', [$eventModel, $shift]) }}" class="text-brand-green hover:underline">
                                                    {{ $shift->name }}
                                                </a>
                                                @if($shift->double_hours)
                                                    <span class="ml-1 text-xs text-amber-600" title="Double hours">★ 2×</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                                {{ $shift->start_time->format('D g:i A') }} – {{ $shift->end_time->format('g:i A') }}
                                            </td>
                                            <td class="px-6 py-3 text-center text-sm {{ $filled >= $shift->max_volunteers ? 'text-green-700 font-semibold' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ $filled }} / {{ $shift->max_volunteers }}
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
                        @endforeach
                    </div>
                @endforeach
            @endif
        @endif
    </div>

    <x-slot name="right">
        <p class="py-4 text-sm text-gray-600 dark:text-gray-400">
            Use this report to compare shift tag coverage across multiple events.
            Select one or more tags (e.g., <em>Cashier</em>, <em>BadgeChecker</em>) and optionally filter to specific events.
        </p>
        <p class="py-2 text-sm text-gray-600 dark:text-gray-400">
            Tags are managed in
            <a href="{{ route('admin.tags.index') }}" class="text-brand-green hover:underline">Settings → Tags</a>.
        </p>
    </x-slot>
</x-app-layout>
