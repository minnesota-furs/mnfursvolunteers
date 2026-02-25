<x-app-layout>
    @section('title', 'Import Users — Map Columns')
    <x-slot name="header">
        Import Users
    </x-slot>

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Wizard step indicator --}}
        @include('users.import._steps', ['current' => 2])

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">

                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Step 2: Map Columns</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    For each column in your CSV, choose the matching field in the application.
                    Columns you don't need can be set to <em>Skip</em>.
                </p>
                <p class="text-sm font-medium text-amber-600 dark:text-amber-400 mb-6">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 inline" />
                    <strong>Email Address</strong> must be mapped — it identifies each user.
                </p>

                @error('mapping')
                    <div class="mb-4 px-4 py-3 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-700 dark:text-red-300">{{ $message }}</p>
                    </div>
                @enderror

                <form method="POST" action="{{ route('users.import.store-mapping') }}">
                    @csrf

                    {{-- Column mapping table --}}
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                        CSV Column
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                        Sample Values
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">
                                        Map To
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($headers as $i => $header)
                                    @php
                                        $samples = collect($preview)->map(fn($row) => $row[$i] ?? '')->filter()->take(3)->join(', ');
                                        $selected = old("mapping.{$header}", $suggested[$header] ?? 'skip');
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <span class="font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $header }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-gray-500 dark:text-gray-400 italic truncate max-w-xs block">
                                                {{ $samples ?: '—' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select name="mapping[{{ $header }}]"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-brand-green focus:ring-brand-green
                                                        {{ $selected !== 'skip' ? 'border-brand-green ring-1 ring-brand-green' : '' }}"
                                                    x-data
                                                    @change="$el.classList.toggle('border-brand-green', $el.value !== 'skip');
                                                             $el.classList.toggle('ring-1', $el.value !== 'skip');
                                                             $el.classList.toggle('ring-brand-green', $el.value !== 'skip');">
                                                @foreach ($appFields as $value => $label)
                                                    <option value="{{ $value }}" {{ $selected === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- CSV preview table --}}
                    @if (!empty($preview))
                        <div class="mb-8">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                CSV Preview
                                <span class="ml-1 text-xs font-normal text-gray-400 dark:text-gray-500">(first {{ count($preview) }} of {{ $total }} data rows)</span>
                            </h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            @foreach ($headers as $header)
                                                <th class="px-3 py-2 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                                                    {{ $header }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach ($preview as $row)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                                @foreach ($headers as $i => $header)
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                                        {{ $row[$i] ?? '' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('users.import') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Back: Upload different file
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
</x-app-layout>
