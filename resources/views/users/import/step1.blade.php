<x-app-layout>
    @section('title', 'Import Users — Upload CSV')
    <x-slot name="header">
        Import Users
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Wizard step indicator --}}
        @include('users.import._steps', ['current' => 1])

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">

                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Step 1: Upload CSV File</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Upload a <code class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">.csv</code>
                    file containing the users you want to import. The <strong>first row must be column headers</strong>.
                </p>

                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.import.upload') }}" enctype="multipart/form-data"
                      x-data="{ fileName: null, dragging: false }"
                      @dragover.prevent="dragging = true"
                      @dragleave.prevent="dragging = false"
                      @drop.prevent="dragging = false; const f = $event.dataTransfer.files[0]; if (f) { fileName = f.name; $refs.fileInput.files = $event.dataTransfer.files; }">
                    @csrf

                    {{-- Drop-zone --}}
                    <div class="mb-6"
                         :class="dragging ? 'border-brand-green bg-brand-green/5' : 'border-gray-300 dark:border-gray-600'"
                         class="border-2 border-dashed rounded-lg p-8 text-center transition-colors">

                        <x-heroicon-o-arrow-up-tray class="mx-auto w-10 h-10 text-gray-400 dark:text-gray-500 mb-3" />

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            Drag &amp; drop your CSV here, or
                        </p>

                        <label class="cursor-pointer inline-flex items-center gap-2 rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark transition">
                            <x-heroicon-s-document-plus class="w-4 h-4" />
                            Choose File
                            <input x-ref="fileInput" type="file" name="csv_file" accept=".csv,.txt"
                                   class="sr-only"
                                   @change="fileName = $event.target.files[0]?.name ?? null"
                                   required>
                        </label>

                        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500" x-show="!fileName">
                            CSV or TXT · Max 10 MB
                        </p>
                        <p class="mt-3 text-sm font-medium text-brand-green" x-show="fileName" x-cloak>
                            <x-heroicon-s-document-check class="w-4 h-4 inline" />
                            <span x-text="fileName"></span>
                        </p>
                    </div>

                    {{-- Hint box --}}
                    <div class="mb-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-semibold mb-1">Expected format</p>
                        <p class="mb-2 text-blue-700 dark:text-blue-300">Column headers on the first row — you'll map them to fields in the next step. Example:</p>
                        <code class="block font-mono text-xs bg-blue-100 dark:bg-blue-900/40 rounded px-3 py-2 overflow-x-auto whitespace-nowrap">
                            Full Name,Email,First,Last,Department,Notes<br>
                            Mocha Kangaroo,mocha@example.com,Jessie,Anderson,Operations,Loves volunteering
                        </code>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('settings.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Back to Settings
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-md bg-brand-green px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                            Next: Map Columns
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
