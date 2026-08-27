<x-app-layout>
    @section('title', 'Report: Unlinked Users')

    <x-slot name="header">
        Report: Unlinked Users
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('report.staffConcat') }}" class="block rounded-md bg-gray-500 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-gray-600">
            &larr; Staff &amp; Concat
        </a>
    </x-slot>

    <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
        <form method="GET" action="{{ route('report.staffConcat.unlinked') }}"
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
                <button type="submit" class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                    Update
                </button>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Shows active staff with no ConCat account associated.</p>
        </form>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Unlinked Users ({{ $unlinkedUsers->count() }})</h3>
                    <a href="{{ route('report.staffConcat.unlinked.export', request()->query()) }}"
                       class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500">
                        Export CSV
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Departments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($unlinkedUsers as $user)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('users.show', $user) }}" class="text-sm font-medium text-brand-green hover:underline">{{ $user->name }}</a>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $user->departments->pluck('name')->implode(', ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No unlinked staff found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
