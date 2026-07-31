<x-app-layout>
    <x-slot name="header">
        {{ $department->name }} Department Management
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('departments.show', $department) }}"
            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            Department Profile
        </a>
        <a href="{{ route('departments.staff-export', array_merge(['department' => $department], request()->only(['search', 'status']))) }}"
            class="rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-md hover:opacity-90">
            <x-heroicon-o-arrow-down-tray class="inline w-4" /> Export CSV
        </a>
    </x-slot>

    <div class="mx-auto flex max-w-7xl flex-col gap-6 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['label' => 'Department Staff', 'value' => $summary['staff']],
                ['label' => 'Active Staff', 'value' => $summary['active']],
                ['label' => 'Inactive Staff', 'value' => $summary['inactive']],
                ['label' => 'Current Period Hours', 'value' => format_hours($summary['current_hours'])],
            ] as $stat)
                <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800" x-data="{ copied: false }">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Email active staff</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Copies a BCC-safe list of all active members in this department.</p>
                </div>
                <button type="button"
                    x-on:click="navigator.clipboard.writeText(@js($bccList)); copied = true; setTimeout(() => copied = false, 2000)"
                    class="rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white hover:opacity-90"
                    @disabled($bccList === '')>
                    <span x-show="!copied">Copy BCC List</span>
                    <span x-show="copied" x-cloak>Copied</span>
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="border-b border-gray-200 p-5 dark:border-gray-700">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Staff directory</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Hours shown are credited to {{ $department->name }}.
                        </p>
                    </div>
                    <form method="GET" action="{{ route('departments.manage', $department) }}" class="flex flex-col gap-3 sm:flex-row">
                        <div>
                            <label for="search" class="sr-only">Search staff</label>
                            <input id="search" name="search" type="search" value="{{ request('search') }}"
                                placeholder="Name, email, or code"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white sm:w-64">
                        </div>
                        <div>
                            <label for="status" class="sr-only">Status</label>
                            <select id="status" name="status"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <option value="">All statuses</option>
                                <option value="active" @selected(request('status') === 'active')>Active</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <button class="rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white dark:bg-gray-600">Filter</button>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            @foreach(['Staff member', 'Email', 'Status', 'Joined', 'Current hours', 'Lifetime hours', 'Last volunteered'] as $heading)
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse($members as $member)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 text-sm">
                                    <a href="{{ route('users.show', $member) }}" class="font-semibold text-brand-green hover:underline">
                                        {{ $member->displayName() }}
                                    </a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $member->pronouns ?: 'No pronouns listed' }}
                                        @if($member->vol_code) · {{ $member->vol_code }} @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm">
                                    <a class="text-brand-green hover:underline" href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $member->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                        {{ $member->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $member->pivot->created_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ format_hours($member->current_period_hours ?? 0) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ format_hours($member->lifetime_department_hours ?? 0) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $member->last_volunteered_at ? \Illuminate\Support\Carbon::parse($member->last_volunteered_at)->format('M j, Y') : 'Never' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No staff match these filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($members->hasPages())
                <div class="border-t border-gray-200 p-4 dark:border-gray-700">{{ $members->links() }}</div>
            @endif
        </div>

        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming staff assignments</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">The next assignments held by current department members.</p>

            <div class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($upcomingShifts as $shift)
                    <div class="flex flex-col justify-between gap-2 py-4 sm:flex-row">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $shift->event->name }} — {{ $shift->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $shift->start_time->format('M j, Y g:i A') }}</p>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $shift->users->pluck('name')->join(', ') }}</p>
                    </div>
                @empty
                    <p class="py-6 text-sm text-gray-500 dark:text-gray-400">No upcoming assignments for this department.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
