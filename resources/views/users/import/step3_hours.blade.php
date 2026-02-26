<x-app-layout>
    @section('title', 'Import Users — Hours Configuration')
    <x-slot name="header">
        Import Users
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Wizard step indicator --}}
        @include('users.import._steps', ['current' => 3])

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">

                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Step 3: Hours Configuration</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Optionally import a flat-rate volunteer hours entry for each user from a CSV column.
                    Each volunteer will receive a single <strong>VolunteerHours</strong> record using the value in the column you select.
                </p>

                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700 dark:text-red-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.import.store-hours') }}" x-data="hoursStep()">
                    @csrf

                    {{-- Skip / Enable toggle --}}
                    <div class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 overflow-hidden">

                        {{-- Enable option --}}
                        <label class="flex items-start gap-4 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                               :class="!skipping ? 'bg-brand-green/5 dark:bg-brand-green/10' : ''">
                            <input type="radio" name="skip_hours" value="0"
                                   x-model="skipValue"
                                   class="mt-1 h-4 w-4 text-brand-green border-gray-300 focus:ring-brand-green"
                                   {{ old('skip_hours', $currentConfig['skip'] ?? true) == '0' || ($currentConfig['skip'] ?? true) === false ? 'checked' : '' }}>
                            <div>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    <x-heroicon-s-clock class="w-4 h-4 inline text-brand-green" />
                                    Import hours from a CSV column
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Each row's hours value will create a volunteer hours record for that user.
                                </span>
                            </div>
                        </label>

                        {{-- Skip option --}}
                        <label class="flex items-start gap-4 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                               :class="skipping ? 'bg-gray-50 dark:bg-gray-700/20' : ''">
                            <input type="radio" name="skip_hours" value="1"
                                   x-model="skipValue"
                                   class="mt-1 h-4 w-4 text-brand-green border-gray-300 focus:ring-brand-green"
                                   {{ old('skip_hours', ($currentConfig['skip'] ?? true) ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    <x-heroicon-s-x-mark class="w-4 h-4 inline text-gray-400" />
                                    Skip — don't import hours
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    No volunteer hours records will be created during this import.
                                </span>
                            </div>
                        </label>
                    </div>

                    {{-- Hours config panel (shown only when not skipping) --}}
                    <div x-show="!skipping" x-cloak x-transition class="space-y-5 mb-6">

                        {{-- Hours column selector --}}
                        <div>
                            <label for="hours_column" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Which column contains hours? <span class="text-red-500">*</span>
                            </label>
                            <select name="hours_column" id="hours_column"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-brand-green focus:ring-brand-green">
                                <option value="">— Select a column —</option>
                                @foreach ($headers as $header)
                                    <option value="{{ $header }}"
                                        {{ old('hours_column', $currentConfig['hours_column'] ?? '') === $header ? 'selected' : '' }}>
                                        {{ $header }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hours_column')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                The column must contain numeric values (e.g. <code class="font-mono">2</code>, <code class="font-mono">3.5</code>).
                                Rows with a blank or non-numeric value will have no hours record created.
                            </p>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="hours_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Description for these hour entries
                            </label>
                            <input type="text" name="hours_description" id="hours_description"
                                   value="{{ old('hours_description', $currentConfig['description'] ?? 'Imported volunteer hours') }}"
                                   maxlength="255"
                                   placeholder="e.g. Convention 2024 volunteer hours"
                                   class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-brand-green focus:ring-brand-green" />
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                This description will be applied to every hours record created by this import.
                            </p>
                        </div>

                        {{-- Fiscal Ledger --}}
                        <div>
                            <label for="ledger_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Apply to fiscal ledger <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            @if ($ledgers->isEmpty())
                                <p class="text-sm text-amber-600 dark:text-amber-400">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 inline" />
                                    No fiscal ledgers exist yet. Hours will be created without a ledger assignment.
                                </p>
                                <input type="hidden" name="ledger_id" value="">
                            @else
                                <select name="ledger_id" id="ledger_id"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-brand-green focus:ring-brand-green">
                                    <option value="">— No ledger / unassigned —</option>
                                    @foreach ($ledgers as $ledger)
                                        <option value="{{ $ledger->id }}"
                                            {{ (string) old('ledger_id', $currentConfig['ledger_id'] ?? '') === (string) $ledger->id ? 'selected' : '' }}>
                                            {{ $ledger->name }}
                                            @if ($ledger->start_date && $ledger->end_date)
                                                ({{ $ledger->start_date->format('M Y') }} – {{ $ledger->end_date->format('M Y') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    All imported hours records will be attributed to the selected fiscal ledger.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('users.import.map') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Back: Adjust column mapping
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-md bg-brand-green px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                            Next: Review &amp; Confirm
                            <x-heroicon-s-arrow-right class="w-4 h-4" />
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    @endpush

    @push('scripts')
    <script>
        function hoursStep() {
            return {
                skipValue: '{{ old('skip_hours', ($currentConfig['skip'] ?? true) ? '1' : '0') }}',
                get skipping() {
                    return this.skipValue === '1';
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
