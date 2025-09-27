<x-app-layout>
    @section('title', 'Add Candidate - ' . $election->title)
    <x-slot name="header">
        Add Candidate: {{ $election->title }}
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
                            Manual Candidate Addition
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>
                                You are manually adding a candidate to this election. 
                                @if($election->requires_approval)
                                    You can choose whether to pre-approve them or require separate approval.
                                @else
                                    They will be automatically approved for the ballot.
                                @endif
                            </p>
                            <div class="mt-2">
                                <strong>Positions Available:</strong> {{ $election->max_positions }}<br>
                                <strong>Voting Period:</strong> {{ $election->start_date->format('M j, Y g:i A') }} - {{ $election->end_date->format('M j, Y g:i A') }}
                                @if($election->min_candidate_hours > 0)
                                    <br><strong>Hours Requirement:</strong> {{ $election->min_candidate_hours }} hours in current fiscal year
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Message -->
            @if(session('warning'))
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-yellow-400"/>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Hours Requirement Warning
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                {{ session('warning') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Candidate Creation Form -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-6">
                        Candidate Information
                    </h3>

                    @if($availableUsers->count() > 0)
                        <form method="POST" action="{{ route('admin.elections.candidates.store', $election) }}">
                            @csrf

                            <div class="space-y-6">
                                <!-- User Selection -->
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Select User</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        <select name="user_id" id="user_id" required 
                                            class="block w-full max-w-lg rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-green sm:text-sm sm:leading-6">
                                            <option value="">Choose a user...</option>
                                            @foreach($availableUsers as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}
                                                    data-hours="{{ $user->getCurrentFiscalYearHours() }}"
                                                    data-email="{{ $user->email }}"
                                                    data-department="{{ $user->primaryDepartment?->name ?? 'No Department' }}">
                                                    {{ $user->name }} ({{ $user->getCurrentFiscalYearHours() }} hours)
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-form-validation for="user_id" />
                                        
                                        <!-- User Details Display -->
                                        <div id="user-details" class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded border hidden">
                                            <div class="text-sm">
                                                <div><strong>Email:</strong> <span id="user-email"></span></div>
                                                <div><strong>Department:</strong> <span id="user-department"></span></div>
                                                <div><strong>Current Fiscal Year Hours:</strong> <span id="user-hours"></span></div>
                                                @if($election->min_candidate_hours > 0)
                                                    <div id="hours-status" class="mt-2"></div>
                                                @endif
                                            </div>
                                        </div>
                                    </dd>
                                </div>

                                <!-- Campaign Statement -->
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 border-b border-gray-200 dark:border-gray-700">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                        Campaign Statement
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-normal">Optional</p>
                                    </dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        <textarea name="statement" id="statement" rows="6" 
                                            class="block w-full max-w-lg rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-green sm:text-sm sm:leading-6"
                                            placeholder="Enter a campaign statement for this candidate...">{{ old('statement') }}</textarea>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            You can add or edit this later if needed.
                                        </p>
                                        <x-form-validation for="statement" />
                                    </dd>
                                </div>

                                <!-- Settings -->
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Settings</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300 sm:col-span-2 sm:mt-0">
                                        <div class="space-y-3">
                                            @if($election->requires_approval)
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="approved" id="approved" value="1"
                                                        class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green"
                                                        {{ old('approved') ? 'checked' : '' }}>
                                                    <label for="approved" class="ml-2 text-sm text-gray-900 dark:text-gray-100">
                                                        Pre-approve this candidate
                                                    </label>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    If unchecked, candidate will need separate approval before appearing on ballot.
                                                </p>
                                            @endif
                                            
                                            @if(session('warning'))
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="force_create" id="force_create" value="1"
                                                        class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-600">
                                                    <label for="force_create" class="ml-2 text-sm text-red-900 dark:text-red-100">
                                                        Force create despite insufficient hours
                                                    </label>
                                                </div>
                                            @endif
                                        </div>
                                    </dd>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6 flex justify-end gap-3">
                                <a href="{{ route('admin.elections.candidates', $election) }}" 
                                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" 
                                    class="inline-flex items-center rounded-md bg-brand-green px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                    <x-heroicon-s-user-plus class="w-5 h-5 mr-2"/> Add Candidate
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No available users</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                All active users are already candidates for this election, or there are no active users to add.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('admin.elections.candidates', $election) }}"
                                    class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                    Back to Candidates
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($availableUsers->count() > 0)
        <script>
            document.getElementById('user_id').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const userDetails = document.getElementById('user-details');
                
                if (selectedOption.value) {
                    const hours = selectedOption.dataset.hours;
                    const email = selectedOption.dataset.email;
                    const department = selectedOption.dataset.department;
                    const minHours = {{ $election->min_candidate_hours }};
                    
                    document.getElementById('user-email').textContent = email;
                    document.getElementById('user-department').textContent = department;
                    document.getElementById('user-hours').textContent = hours;
                    
                    @if($election->min_candidate_hours > 0)
                        const hoursStatus = document.getElementById('hours-status');
                        if (parseFloat(hours) >= minHours) {
                            hoursStatus.innerHTML = '<span class="text-green-600 dark:text-green-400">✓ Meets hours requirement</span>';
                        } else {
                            hoursStatus.innerHTML = '<span class="text-red-600 dark:text-red-400">⚠ Below required ' + minHours + ' hours</span>';
                        }
                    @endif
                    
                    userDetails.classList.remove('hidden');
                } else {
                    userDetails.classList.add('hidden');
                }
            });
        </script>
    @endif
</x-app-layout>