<x-app-layout>
    @section('title', 'Report: Volunteers in Multiple Departments')

    <x-slot name="header">
        Report: Volunteers in Multiple Departments
    </x-slot>

    <x-slot name="actions">
        {{-- intentionally empty --}}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Active volunteers currently assigned to two or more departments.
        </p>

        <form method="GET" action="{{ route('report.volunteersWithMultipleDepartments') }}"
              class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search volunteers</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Name or email..."
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort by</label>
                    <select id="sort" name="sort"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        <option value="department_count" @selected($sort === 'department_count')>Department count</option>
                        <option value="name" @selected($sort === 'name')>Volunteer name</option>
                    </select>
                    <input type="hidden" name="direction" value="{{ $sort === 'name' ? 'asc' : 'desc' }}">
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                        Filter
                    </button>
                    @if($search)
                        <a href="{{ route('report.volunteersWithMultipleDepartments') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $users->total() }} {{ Str::plural('volunteer', $users->total()) }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Volunteer</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Departments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-3">
                                    <a href="{{ route('users.show', $user) }}" class="text-sm font-medium text-brand-green hover:underline">{{ $user->name }}</a>
                                    @if(Auth::user()->isAdmin())
                                        <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $user->department_count }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($user->departments as $department)
                                            <a href="{{ route('departments.show', $department) }}"
                                               class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-1 text-xs hover:text-brand-green">
                                                {{ $department->sector->name }}: {{ $department->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No volunteers match this report.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
