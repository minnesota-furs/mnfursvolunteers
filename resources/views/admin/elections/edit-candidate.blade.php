<x-app-layout>
    @section('title', 'Edit Candidate - ' . $election->title)
    <x-slot name="header">
        Edit Candidate: {{ $candidate->user->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.candidates', $election) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Candidates
        </a>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Election Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400"/>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            Editing Candidate for {{ $election->title }}
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>
                                You can edit the campaign statement and approval status for this candidate.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidate Edit Form -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Candidate Information
                    </h3>

                    <form method="POST" action="{{ route('admin.elections.candidates.update', [$election, $candidate]) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Candidate Details (Read-only) -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Candidate</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <div class="font-semibold">{{ $candidate->user->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $candidate->user->email }}</div>
                                    @if($candidate->user->primaryDepartment)
                                        <div class="text-xs text-gray-500">{{ $candidate->user->primaryDepartment->name }}</div>
                                    @endif
                                </dd>
                            </div>

                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Nomination Date</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    {{ $candidate->created_at->format('M j, Y g:i A') }}
                                </dd>
                            </div>

                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Current Status</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    @if($candidate->approved && !$candidate->withdrawn)
                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-700/10">
                                            <x-heroicon-s-check-circle class="w-3 h-3 mr-1"/> Approved
                                        </span>
                                    @elseif($candidate->withdrawn)
                                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-700/10">
                                            <x-heroicon-s-x-circle class="w-3 h-3 mr-1"/> Withdrawn
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                                            <x-heroicon-s-clock class="w-3 h-3 mr-1"/> Pending Approval
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <!-- Campaign Statement (Editable) -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Campaign Statement
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-normal">Optional</p>
                                </dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <textarea name="statement" id="statement" rows="8" 
                                        class="block w-full max-w-lg rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-green sm:text-sm sm:leading-6"
                                        placeholder="Enter a campaign statement for this candidate...">{{ old('statement', $candidate->statement) }}</textarea>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        This will be visible to all voters on the election page. Supports Markdown formatting (e.g., **bold**, *italic*, [links](url), etc.).
                                    </p>
                                    <x-form-validation for="statement" />
                                </dd>
                            </div>

                            <!-- Approval Status (Editable if election requires approval) -->
                            @if($election->requires_approval && !$candidate->withdrawn)
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Approval Status</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="approved" id="approved" value="1"
                                                class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green"
                                                {{ old('approved', $candidate->approved) ? 'checked' : '' }}>
                                            <label for="approved" class="ml-2 text-sm text-gray-900 dark:text-gray-100">
                                                Candidate is approved for ballot
                                            </label>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Approved candidates will appear on the voting ballot.
                                        </p>
                                    </dd>
                                </div>
                            @endif
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('admin.elections.candidates', $election) }}" 
                                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                <x-heroicon-s-check class="w-4 h-4 inline mr-1"/> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
