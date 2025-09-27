<x-app-layout>
    <x-slot name="header">
        {{ isset($election) ? 'Edit Election: '. $election->title : 'Create Election' }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Elections
        </a>
        @if(isset($election))
            <a href="{{ route('elections.show', $election) }}" target="_blank"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-eye class="w-4 inline"/> View Public Page
            </a>
            <form action="{{ route('admin.elections.destroy', $election) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        onclick="return confirm('Are you sure you want to delete this election? This will also delete all candidates and votes. This cannot be undone.');">
                        <x-heroicon-s-trash class="w-4 inline"/> Delete
                </button>
            </form>
        @endif
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($election) ? route('admin.elections.update', $election) : route('admin.elections.store') }}">
                @csrf
                @if(isset($election))
                    @method('PUT')
                @endif

                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                            Election Details
                        </h3>

                        <div class="space-y-6">
                            <!-- Title -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Election Title</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-full max-w-lg text-sm" type="text" name="title" id="title"
                                        :value="old('title', $election->title ?? '')" required placeholder="Board of Directors Election 2025" />
                                    <x-form-validation for="title" />
                                </dd>
                            </div>

                            <!-- Description -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Description</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <textarea name="description" id="description" rows="4" 
                                        class="block w-full max-w-lg rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                        placeholder="Describe the election, positions available, and any requirements...">{{ old('description', $election->description ?? '') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Supports Markdown formatting (e.g., **bold**, *italic*, [links](url), etc.)</p>
                                    <x-form-validation for="description" />
                                </dd>
                            </div>

                            <!-- Nomination Start Date -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Nomination Start Date</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-64 text-sm" type="datetime-local" name="nomination_start_date" id="nomination_start_date"
                                        :value="old('nomination_start_date', isset($election) && $election->nomination_start_date ? $election->nomination_start_date->format('Y-m-d\TH:i') : '')" />
                                    <p class="mt-1 text-xs text-gray-500">Optional: When nominations can begin (leave blank to allow nominations during entire election period)</p>
                                    <x-form-validation for="nomination_start_date" />
                                </dd>
                            </div>

                            <!-- Nomination End Date -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Nomination End Date</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-64 text-sm" type="datetime-local" name="nomination_end_date" id="nomination_end_date"
                                        :value="old('nomination_end_date', isset($election) && $election->nomination_end_date ? $election->nomination_end_date->format('Y-m-d\TH:i') : '')" />
                                    <p class="mt-1 text-xs text-gray-500">Optional: When nominations must end (leave blank to allow nominations during entire election period)</p>
                                    <x-form-validation for="nomination_end_date" />
                                </dd>
                            </div>

                            <!-- Start Date -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Voting Start Date</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-64 text-sm" type="datetime-local" name="start_date" id="start_date"
                                        :value="old('start_date', isset($election) ? $election->start_date->format('Y-m-d\TH:i') : '')" required />
                                    <x-form-validation for="start_date" />
                                </dd>
                            </div>

                            <!-- End Date -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Voting End Date</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-64 text-sm" type="datetime-local" name="end_date" id="end_date"
                                        :value="old('end_date', isset($election) ? $election->end_date->format('Y-m-d\TH:i') : '')" required />
                                    <x-form-validation for="end_date" />
                                </dd>
                            </div>

                            <!-- Max Positions -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Maximum Positions</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-32 text-sm" type="number" name="max_positions" id="max_positions"
                                        min="1" :value="old('max_positions', $election->max_positions ?? 1)" required />
                                    <p class="mt-1 text-xs text-gray-500">Number of positions available for this election</p>
                                    <x-form-validation for="max_positions" />
                                </dd>
                            </div>

                            <!-- Volunteer Hours Requirements -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Hours Requirements</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <div class="space-y-4">
                                        <div>
                                            <label for="min_candidate_hours" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                Minimum hours to be a candidate
                                            </label>
                                            <x-text-input class="block w-32 text-sm mt-1" type="number" name="min_candidate_hours" id="min_candidate_hours"
                                                min="0" step="0.5" :value="old('min_candidate_hours', $election->min_candidate_hours ?? 0)" />
                                            <p class="mt-1 text-xs text-gray-500">Hours required in current fiscal year to nominate yourself (0 = no requirement)</p>
                                            <x-form-validation for="min_candidate_hours" />
                                        </div>
                                        <div>
                                            <label for="min_voter_hours" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                Minimum hours to vote
                                            </label>
                                            <x-text-input class="block w-32 text-sm mt-1" type="number" name="min_voter_hours" id="min_voter_hours"
                                                min="0" step="0.5" :value="old('min_voter_hours', $election->min_voter_hours ?? 0)" />
                                            <p class="mt-1 text-xs text-gray-500">Hours required in current fiscal year to vote (0 = no requirement)</p>
                                            <x-form-validation for="min_voter_hours" />
                                        </div>
                                    </div>
                                </dd>
                            </div>

                            <!-- Settings -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Election Settings</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="allow_self_nomination" id="allow_self_nomination" value="1"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                {{ old('allow_self_nomination', $election->allow_self_nomination ?? true) ? 'checked' : '' }}>
                                            <label for="allow_self_nomination" class="ml-2 text-sm text-gray-900 dark:text-gray-100">
                                                Allow self-nomination
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="requires_approval" id="requires_approval" value="1"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                {{ old('requires_approval', $election->requires_approval ?? false) ? 'checked' : '' }}>
                                            <label for="requires_approval" class="ml-2 text-sm text-gray-900 dark:text-gray-100">
                                                Require candidate approval
                                            </label>
                                        </div>
                                        @if(isset($election))
                                            <div class="flex items-center">
                                                <input type="checkbox" name="active" id="active" value="1"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                    {{ old('active', $election->active ?? true) ? 'checked' : '' }}>
                                                <label for="active" class="ml-2 text-sm text-gray-900 dark:text-gray-100">
                                                    Election is active
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </dd>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('admin.elections.index') }}" 
                                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                {{ isset($election) ? 'Update Election' : 'Create Election' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>