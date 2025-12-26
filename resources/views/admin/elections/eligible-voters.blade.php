<x-app-layout>
    @section('title', 'Eligible Non-Voters - ' . $election->title)
    <x-slot name="header">
        Eligible Non-Voters: {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.show', $election) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Election
        </a>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('success')['message'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Warning Message -->
            @if (session('warning'))
                <div class="mb-4 rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                {{ session('warning')['message'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Message -->
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Card -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            The following users are eligible to vote (they meet the 
                            @if($election->min_voter_hours > 0)
                                <strong>{{ format_hours($election->min_voter_hours) }} hour requirement</strong>
                                @if($fiscalLedger)
                                    for <strong>{{ $fiscalLedger->name }}</strong>
                                @endif
                            @else
                                eligibility requirements
                            @endif
                            ) but have not yet cast their vote{{ $election->max_positions > 1 ? 's' : '' }}.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Eligible Non-Voters List -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="sm:flex sm:items-center sm:justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Eligible Non-Voters ({{ $eligibleNonVoters->count() }})
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Select users and send them a reminder email to vote.
                            </p>
                        </div>
                    </div>

                    @if($eligibleNonVoters->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">All eligible voters have voted!</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                There are no eligible users who haven't voted yet.
                            </p>
                        </div>
                    @else
                        <form action="{{ route('admin.elections.send-reminders', $election) }}" method="POST" id="reminderForm">
                            @csrf
                            
                            <div class="mb-4 flex items-center justify-between bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                                <div class="flex items-center">
                                    <input type="checkbox" id="selectAll"
                                        class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green">
                                    <label for="selectAll" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Select All
                                    </label>
                                    <span class="ml-4 text-sm text-gray-600 dark:text-gray-400" id="selectedCount">
                                        0 selected
                                    </span>
                                </div>
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green disabled:opacity-50 disabled:cursor-not-allowed"
                                    id="sendButton" disabled>
                                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    Send Reminder Emails
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">
                                                <span class="sr-only">Select</span>
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                Name
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                Email
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                Hours
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                Vol Code
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @foreach($eligibleNonVoters as $user)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                                        class="user-checkbox h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green">
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <a href="{{ route('users.show', $user->id) }}" class="hover:text-brand-green">
                                                        {{ $user->name }}
                                                    </a>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $user->email }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ format_hours($user->hours_for_period) }} hrs
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $user->vol_code ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            const selectedCountSpan = document.getElementById('selectedCount');
            const sendButton = document.getElementById('sendButton');
            const reminderForm = document.getElementById('reminderForm');

            function updateSelectedCount() {
                const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
                selectedCountSpan.textContent = `${checkedCount} selected`;
                sendButton.disabled = checkedCount === 0;
            }

            selectAllCheckbox?.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });

            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedCount();
                    
                    // Update select all checkbox state
                    const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
                    const noneChecked = Array.from(userCheckboxes).every(cb => !cb.checked);
                    
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
                    }
                });
            });

            reminderForm?.addEventListener('submit', function(e) {
                const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one user to send reminders to.');
                    return false;
                }
                
                const confirmMessage = `Are you sure you want to send voting reminder emails to ${checkedCount} user${checkedCount !== 1 ? 's' : ''}?`;
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
            });

            // Initial count
            updateSelectedCount();
        });
    </script>
</x-app-layout>
