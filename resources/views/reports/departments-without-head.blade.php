<x-app-layout>
    @section('title', 'Report: ' . $reportTitle)
    <x-slot name="header">
        Report: {{ $reportTitle }}
    </x-slot>

    <x-slot name="actions">
        {{-- intentionally empty --}}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $reportDescription }}</p>

        {{-- Filters --}}
        <form method="GET" action="{{ route('report.departmentsWithoutHead') }}"
              class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1 w-full sm:w-auto">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}"
                           placeholder="Search by department name..."
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-o-funnel class="w-4 inline mr-1"/> Filter
                    </button>
                    @if($search)
                        <a href="{{ route('report.departmentsWithoutHead') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:underline">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Results table --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            @if($departments->isEmpty())
                <div class="p-12 text-center">
                    <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-green-400"/>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No Departments Without a Head</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($search)
                            No results match the current search.
                        @else
                            Every department has at least one head assigned.
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
                                        '<a href="' . route('report.departmentsWithoutHead', array_merge(request()->query(), ['sort' => $col, 'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc'])) . '" '
                                        . 'class="group inline-flex items-center gap-1 hover:underline">'
                                        . e($label)
                                        . ($sort === $col ? ($direction === 'asc' ? ' ↑' : ' ↓') : '')
                                        . '</a>';
                                @endphp
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {!! $sortLink('name', 'Department') !!}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Sector
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Members
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {!! $sortLink('created_at', 'Created') !!}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($departments as $department)
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <a href="{{ route('departments.show', $department->id) }}"
                                           class="text-sm font-medium text-brand-green hover:underline">
                                            {{ $department->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $department->sector->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ $department->userCount() }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $department->created_at->format('M j, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $departments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
