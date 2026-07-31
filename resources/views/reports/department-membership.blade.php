<x-app-layout>
    @section('title', 'Report: Department Membership')

    <x-slot name="header">
        Report: Department Membership Totals and Trends
    </x-slot>

    <x-slot name="actions">
        {{-- intentionally empty --}}
    </x-slot>

    <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active department volunteers</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $activeVolunteerCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active memberships</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $activeMembershipCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">In multiple departments</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $multipleDepartmentCount }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('report.departmentMembership') }}"
              class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="sector_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sector</label>
                    <select id="sector_id" name="sector_id"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        <option value="">All sectors</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" @selected($selectedSectorId === $sector->id)>{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="months" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trend period</label>
                    <select id="months" name="months"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        @foreach([6, 12, 24] as $option)
                            <option value="{{ $option }}" @selected($monthCount === $option)>Last {{ $option }} months</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                    Update
                </button>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Monthly columns show when current active memberships were added. Removed memberships are not retained in historical data.
            </p>
        </form>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-900 px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current total</th>
                            @foreach($months as $month)
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                                    {{ $month->format('M Y') }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($departments as $department)
                            <tr>
                                <td class="sticky left-0 bg-white dark:bg-gray-800 px-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('departments.show', $department) }}" class="text-sm font-medium text-brand-green hover:underline">{{ $department->name }}</a>
                                    <div class="text-xs text-gray-400">{{ $department->sector?->name }}</div>
                                </td>
                                <td class="px-4 py-3 text-center text-sm font-bold text-gray-900 dark:text-gray-100">{{ $department->active_users_count }}</td>
                                @foreach($months as $month)
                                    @php($added = $department->monthly_memberships->get($month->format('Y-m'), 0))
                                    <td class="px-4 py-3 text-center text-sm {{ $added > 0 ? 'font-semibold text-brand-green' : 'text-gray-400' }}">
                                        {{ $added }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $months->count() + 2 }}" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No departments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($departments->isNotEmpty())
                        <tfoot class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="sticky left-0 bg-gray-50 dark:bg-gray-900 px-5 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">All departments</th>
                                <th class="px-4 py-3 text-center text-sm font-bold text-gray-900 dark:text-gray-100">{{ $activeMembershipCount }}</th>
                                @foreach($months as $month)
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $monthlyTotals->get($month->format('Y-m'), 0) }}
                                    </th>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
