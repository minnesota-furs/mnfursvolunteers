<x-app-layout>
    @section('title', 'Import Users — Results')
    <x-slot name="header">
        Import Users — Results
    </x-slot>

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-4 text-center">
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $results['total'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total rows</p>
            </div>
            <div class="rounded-lg bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 shadow-sm p-4 text-center">
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $results['created'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Created</p>
            </div>
            <div class="rounded-lg bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 shadow-sm p-4 text-center">
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $results['skipped'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Skipped (existing)</p>
            </div>
            <div class="rounded-lg bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 shadow-sm p-4 text-center">
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $results['failed'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Failed</p>
            </div>
        </div>

        {{-- Imported at timestamp --}}
        <p class="text-xs text-gray-400 dark:text-gray-500 text-right -mt-2">
            Import completed at {{ \Carbon\Carbon::parse($results['imported_at'])->format('F j, Y \a\t g:i A') }}
        </p>

        {{-- Failed rows --}}
        @if (!empty($results['failed_rows']))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-red-600 dark:text-red-400">
                            <x-heroicon-s-x-circle class="w-5 h-5 inline" />
                            Failed Rows ({{ count($results['failed_rows']) }})
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            These rows were not imported. Fix the issues in your CSV and re-import.
                        </p>
                    </div>
                    <a href="{{ route('users.import.results.download') }}"
                       class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Download Failed Rows (CSV)
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">Row</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Row Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($results['failed_rows'] as $failure)
                                <tr x-data="{ expanded: false }" class="hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors">
                                    <td class="px-4 py-3 font-mono text-gray-400 dark:text-gray-500 align-top">
                                        {{ $failure['row'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 align-top">
                                        {{ $failure['name'] ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium align-top">
                                        {{ $failure['email'] ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">
                                            {{ $failure['reason'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <button @click="expanded = !expanded"
                                                class="text-xs text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                            <span x-text="expanded ? 'Hide' : 'Show'">Show</span> raw data
                                        </button>
                                        <div x-show="expanded" x-cloak x-transition class="mt-2">
                                            <dl class="text-xs space-y-0.5">
                                                @foreach ($failure['raw'] as $col => $val)
                                                    @if ($val !== '')
                                                        <div class="flex gap-2">
                                                            <dt class="font-mono font-semibold text-gray-500 dark:text-gray-400 shrink-0">{{ $col }}:</dt>
                                                            <dd class="text-gray-700 dark:text-gray-300 break-all">{{ $val }}</dd>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </dl>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Skipped rows --}}
        @if (!empty($results['skipped_rows']))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-data="{ showSkipped: false }">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-amber-600 dark:text-amber-400">
                            <x-heroicon-s-arrow-path class="w-5 h-5 inline" />
                            Skipped Rows ({{ count($results['skipped_rows']) }})
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            These users already exist in the system and were not updated.
                        </p>
                    </div>
                    <button @click="showSkipped = !showSkipped"
                            class="text-sm text-amber-600 dark:text-amber-400 hover:underline">
                        <span x-text="showSkipped ? 'Hide list' : 'Show list'">Show list</span>
                    </button>
                </div>

                <div x-show="showSkipped" x-cloak x-transition>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">Row</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($results['skipped_rows'] as $skipped)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-4 py-2 font-mono text-gray-400 dark:text-gray-500">{{ $skipped['row'] }}</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $skipped['name'] ?: '—' }}</td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300 font-medium">{{ $skipped['email'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- All good, no failures --}}
        @if (empty($results['failed_rows']) && $results['created'] > 0)
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 p-6 text-center">
                <x-heroicon-s-check-circle class="w-10 h-10 mx-auto text-green-500 mb-2" />
                <p class="text-base font-semibold text-green-800 dark:text-green-200">All rows imported successfully!</p>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('users.import') }}"
               class="inline-flex items-center gap-2 rounded-md bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                Import Another File
            </a>
            <a href="{{ route('users.index') }}"
               class="inline-flex items-center gap-2 rounded-md bg-brand-green px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark">
                View All Users
                <x-heroicon-s-arrow-right class="w-4 h-4" />
            </a>
        </div>

    </div>

    @push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush
</x-app-layout>
