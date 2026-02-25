<x-app-layout>
    @section('title', 'Import Users — Confirm & Import')
    <x-slot name="header">
        Import Users
    </x-slot>

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Wizard step indicator --}}
        @include('users.import._steps', ['current' => 3])

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">

                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Step 3: Confirm &amp; Import</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Review the mapping and preview rows below, then click <strong>Run Import</strong> to create the users.
                    Users whose email address already exists in the system will be skipped.
                </p>

                {{-- Summary cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 p-4 text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $total }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total rows in CSV</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 p-4 text-center">
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ count($usedFields) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fields mapped</p>
                    </div>
                    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 text-center">
                        <p class="text-3xl font-bold text-amber-700 dark:text-amber-300">{{ count($previewRows) }}</p>
                        <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">Rows previewed below</p>
                    </div>
                </div>

                {{-- Mapping summary --}}
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Column Mapping Summary</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($mapping as $csvCol => $appField)
                            @if ($appField && $appField !== 'skip')
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium bg-brand-green/10 dark:bg-brand-green/20 text-brand-green border border-brand-green/30">
                                    <span class="font-mono">{{ $csvCol }}</span>
                                    <x-heroicon-s-arrow-right class="w-3 h-3 opacity-60" />
                                    <span>{{ $appFields[$appField] ?? $appField }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-600">
                                    <span class="font-mono">{{ $csvCol }}</span>
                                    <x-heroicon-s-x-mark class="w-3 h-3 opacity-50" />
                                    <span>skipped</span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Preview rows --}}
                @if (!empty($previewRows))
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Data Preview
                            <span class="ml-1 text-xs font-normal text-gray-400 dark:text-gray-500">(first {{ count($previewRows) }} rows, mapped fields only)</span>
                        </h3>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        @foreach ($usedFields as $field)
                                            <th class="px-3 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                                                {{ $appFields[$field] ?? $field }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($previewRows as $row)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            @foreach ($usedFields as $field)
                                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300 whitespace-nowrap
                                                    {{ $field === 'email' ? 'font-medium' : '' }}
                                                    {{ $field === 'password' ? 'font-mono text-gray-400 dark:text-gray-500' : '' }}">
                                                    @if ($field === 'password' && !empty($row[$field]))
                                                        <span class="italic">{{ Str::mask($row[$field], '*', 2) }}</span>
                                                    @else
                                                        {{ $row[$field] ?? '—' }}
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Warning --}}
                <div class="mb-6 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 text-sm text-amber-800 dark:text-amber-200">
                    <p class="font-semibold mb-1">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 inline" />
                        Before you continue
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-amber-700 dark:text-amber-300">
                        <li>Users whose email already exists will be <strong>skipped</strong> (not updated).</li>
                        <li>If no password is provided, it will default to <code class="font-mono text-xs">firstnamelastname!</code> (lower-cased).</li>
                        <li>Departments are matched by <strong>exact name</strong> (case-insensitive). Unmatched values are ignored.</li>
                        <li>This action cannot be undone automatically — make sure you're ready.</li>
                    </ul>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('users.import.map') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        ← Back: Adjust mapping
                    </a>

                    <form method="POST" action="{{ route('users.import.execute') }}">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Import {{ $total }} users now? This cannot be easily undone.')"
                                class="inline-flex items-center gap-2 rounded-md bg-brand-green px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                            <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                            Run Import ({{ $total }} {{ Str::plural('row', $total) }})
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
