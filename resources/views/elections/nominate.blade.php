<x-app-layout>
    @section('title', 'Nominate Yourself - ' . $election->title)
    <x-slot name="header">
        Nominate Yourself: {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('elections.show', $election) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Election
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
                            Self-Nomination for {{ $election->title }}
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>
                                You are nominating yourself as a candidate for this election. 
                                @if($election->requires_approval)
                                    Your nomination will need to be approved by an administrator before you appear on the ballot.
                                @else
                                    Once submitted, you will appear on the ballot for voting.
                                @endif
                            </p>
                            <div class="mt-2">
                                <strong>Positions Available:</strong> {{ $election->max_positions }}<br>
                                <strong>Voting Period:</strong> {{ $election->start_date->format('M j, Y g:i A') }} - {{ $election->end_date->format('M j, Y g:i A') }}
                                @if($election->min_candidate_hours > 0)
                                    <br><strong>Hours Requirement:</strong> {{ $election->min_candidate_hours }} hours (you have {{ Auth::user()->getCurrentFiscalYearHours() }} hours)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nomination Form -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Candidate Information
                    </h3>

                    <form method="POST" action="{{ route('elections.nominate.store', $election) }}">
                        @csrf

                        <!-- Candidate Details (Read-only) -->
                        <div class="space-y-6">
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Your Name</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    {{ Auth::user()->name }}
                                </dd>
                            </div>

                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Email Address</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    {{ Auth::user()->email }}
                                </dd>
                            </div>

                            @if(Auth::user()->primaryDepartment)
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Department</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        {{ Auth::user()->primaryDepartment->name }}
                                    </dd>
                                </div>
                            @endif

                            <!-- Campaign Statement -->
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Campaign Statement
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-normal">Optional</p>
                                </dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                    <textarea name="statement" id="statement" rows="6" 
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-green sm:text-sm sm:leading-6"
                                        placeholder="Tell voters about yourself, your qualifications, and why you're running for this position...">{{ old('statement') }}</textarea>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Share your background, experience, and vision. This will be visible to all voters.
                                    </p>
                                    <x-form-validation for="statement" />
                                </dd>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div class="mt-8 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-yellow-400"/>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        Confirmation Required
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>
                                            By submitting this nomination, you confirm that:
                                        </p>
                                        <ul class="mt-2 list-disc list-inside space-y-1">
                                            <li>You are eligible to serve in this position</li>
                                            <li>You understand the responsibilities involved</li>
                                            <li>You commit to serving if elected</li>
                                            @if($election->requires_approval)
                                                <li>Your nomination is subject to administrator approval</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('elections.show', $election) }}" 
                                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="inline-flex items-center rounded-md bg-brand-green px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                                onclick="return confirm('Are you sure you want to nominate yourself for this election?');">
                                <x-heroicon-s-user-plus class="w-5 h-5 mr-2"/> Submit Nomination
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>