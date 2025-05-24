<x-app-layout>
    @section('title', 'Manage Shift for ' . $event->name)
    <x-slot name="header">
        {{ isset($shift) ? 'Edit Shift' : 'Create Shift' }} for Event: {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Cancel
        </a>
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(isset($shift) && $shift->users->isNotEmpty())
                <div class="mb-6 p-4 bg-yellow-100 border-l-4 border-yellow-400 text-yellow-700">
                    ⚠️ Warning: Volunteers have already signed up for this shift. 
                    Be cautious when changing shift times or deleting this shift.
                </div>
            @endif
            <form method="POST"
                action="{{ isset($shift) ? route('admin.events.shifts.update', [$event, $shift]) : route('admin.events.shifts.store', $event) }}">
                @csrf
                @if (isset($shift))
                    @method('PUT')
                @endif
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="form-label">Shift Name</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="text" name="name" id="name"
                            :value="old('name', $shift->name ?? '')" required />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="form-label">Volunteers Neeed</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="number" name="max_volunteers"
                            id="max_volunteers" min="1"
                            value="{{ old('max_volunteers', $shift->max_volunteers ?? 1) }}" required />
                        <x-form-validation for="max_volunteers" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="form-label">Description</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-textarea-input id="notes" rows="6" name="description"
                            class="block w-full text-sm">{{ old('description', $shift->description ?? '') }}</x-textarea-input>
                        <x-form-validation for="description" />
                    </dd>
                </div>

                @php
                    $defaultStart = isset($shift) ? $shift->start_time : $event->start_date;
                    $defaultEnd = isset($shift) ? $shift->end_time : $event->start_date->copy()->addHour();
                @endphp

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="form-label">Start Date/Time</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="datetime-local" name="start_time" id="start_time"
                            value="{{ old('start_time', $defaultStart->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="start_time" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="form-label">End Date/Time</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="datetime-local" name="end_time" id="end_time"
                            value="{{ old('end_time', $defaultEnd->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="end_time" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900">Double Hours</dt>
                        <p class="text-gray-500 text-sm mt-1">When crediting volunteer hours, should these count as double</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-checkbox-input class="block w-64 text-sm" name="double_hours" id="double_hours"
                            checked="{{ old('double_hours', isset($shift) ? $shift->double_hours : false) }}" />
                        <x-form-validation for="double_hours" />
                    </dd>
                </div>

                {{-- User Search Section --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900">Assign User</dt>
                        <p class="text-gray-500 text-sm mt-1">Manually add a volunteer. Be sure to save after</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input type="text" id="user_search_input" name="user_search_input" class="block w-full text-sm" placeholder="Search by name or email..." />
                        <div id="user_search_results" class="mt-2"></div>
                        <input type="hidden" name="user_id" id="selected_user_id">
                        <div id="selected_user_display" class="mt-2"></div>
                    </dd>
                </div>

                <div class="py-6 flex justify-end space-x-2">
                    <a type="submit" id="submit" href="{{ url()->previous() }}"
                        class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                    <button type="submit"
                        class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        {{ isset($shift) ? 'Update Shift' : 'Create Shift' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    <x-slot name="right">
        @if (isset($shift))
        <h2 class="text-xl font-semibold mb-3 dark:text-white">Volunteers Signed Up ({{ $shift->users->count() }})</h2>
            <ul class="list-disc pl-1 text-sm text-gray-800">
                @forelse ($shift->users as $user)
                    <li class="flex items-center justify-between hover:bg-gray-100 p-1">
                        <span>
                            @if($user->pivot->hours_logged_at)
                                <x-heroicon-m-check-badge title="Hours Credited" class="w-5 inline text-green-600"/> 
                            @endif
                            {{ $user->name }}</span>
                        <form class="text-xs" action="{{ route('admin.events.shifts.remove-volunteer', [$event, $shift, $user]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <a href="{{ route('users.show', $user->id) }}" class="text-blue-600 hover:underline px-1">
                                View
                            </a>
                            <button type="submit" class="text-red-600 px-1 hover:underline" onclick="return confirm('Are you sure you want to remove {{$user->name}}?')">
                                <x-heroicon-m-trash title="Hours Credited" class="w-3 mb-1 inline"/> Remove</button>
                        </form>
                    </li>
                @empty
                    <li class="flex items-center justify-between hover:bg-gray-100 p-3">
                        <span class="text-gray-400">No volunteer signups...</span>
                    </li>
                @endforelse
            </ul>
        @else
            <h2 class="text-xl font-semibold mb-3 dark:text-white">Volunteers Signed Up (0)</h2>
            <p class="text-gray-500 dark:text-gray-400">This is where your signed up volunteers will appear.</p>
        @endif
    </x-slot>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('user_search_input');
    const searchResultsContainer = document.getElementById('user_search_results');
    const selectedUserIdInput = document.getElementById('selected_user_id');
    const selectedUserDisplay = document.getElementById('selected_user_display');

    // Populate hidden field and display if old user_id exists (e.g., due to validation error)
    const oldUserId = "{{ old('user_id') }}";
    const oldUserSearchInput = "{{ old('user_search_input') }}"; // Get old search term

    if (oldUserId) {
        selectedUserIdInput.value = oldUserId;
        // Displaying name here is tricky without another query or passing more data.
        // For now, we'll just show the ID if we don't have the old search input to re-trigger a display.
        // If oldUserSearchInput is available, we might be able to repopulate and show it.
        // However, the primary goal is that the ID is preserved.
        // A simple approach: if an ID is set, show a generic message or the ID itself.
        // selectedUserDisplay.innerHTML = `User ID: ${oldUserId} <button type="button" id="clear_selected_user" class="text-red-500 ml-2 text-sm hover:underline">Clear</button>`;
        // addClearButtonListener();
        // If you want to attempt to show the name, you'd need the name associated with oldUserId.
        // For now, the selection logic below will handle displaying the name upon a new selection.
    }
    if (oldUserSearchInput) {
        searchInput.value = oldUserSearchInput; // Repopulate search input if there was an old value
    }


    searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value.trim();
        searchResultsContainer.innerHTML = ''; // Clear previous results

        if (searchTerm.length < 2) {
            // Do not clear selected user display here if a user is already selected.
            // Only clear it if the user actively clears the selection via the "Clear" button.
            // selectedUserDisplay.innerHTML = '';
            // selectedUserIdInput.value = '';
            return;
        }

        fetch(`{{ route('admin.users.search') }}?term=${encodeURIComponent(searchTerm)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(users => {
                if (users.length > 0) {
                    const ul = document.createElement('ul');
                    ul.className = 'border border-gray-300 rounded-md mt-1 max-h-60 overflow-y-auto bg-white shadow-lg';
                    users.forEach(user => {
                        const li = document.createElement('li');
                        li.className = 'p-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200';
                        // Handle null last_name gracefully
                        const firstName = user.first_name ? user.first_name : '';
                        const lastName = user.last_name ? user.last_name : '';
                        const displayName = `${firstName} ${lastName} (${user.name}) - ${user.email}`.trim();
                        li.textContent = displayName;
                        li.dataset.userId = user.id;
                        // li.dataset.userName = `${user.first_name} ${lastName}`.trim(); // Store name for display

                        li.addEventListener('click', function () {
                            selectedUserIdInput.value = this.dataset.userId;
                            selectedUserDisplay.innerHTML = `<div class="flex items-center justify-between p-2 bg-gray-100 rounded-md"><span>Selected: <strong>\${this.dataset.userName}</strong></span><button type="button" id="clear_selected_user" class="text-red-600 ml-2 text-sm hover:underline font-semibold">Clear</button></div>`;
                            searchInput.value = ''; // Clear search input
                            searchResultsContainer.innerHTML = ''; // Clear results list
                            addClearButtonListener(); // Re-add listener for the new clear button
                            searchInput.disabled = true; // Disable search input when a user is selected
                        });
                        ul.appendChild(li);
                    });
                    searchResultsContainer.appendChild(ul);
                } else {
                    searchResultsContainer.innerHTML = '<p class="text-gray-500 p-2">No users found.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching users:', error);
                searchResultsContainer.innerHTML = '<p class="text-red-500 p-2">Error searching users. Please try again.</p>';
            });
    });

    function addClearButtonListener() {
        const clearButton = document.getElementById('clear_selected_user');
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                selectedUserIdInput.value = '';
                selectedUserDisplay.innerHTML = '';
                searchInput.value = ''; // Clear search input text
                searchResultsContainer.innerHTML = ''; // Clear any stray results
                searchInput.disabled = false; // Re-enable search input
                searchInput.focus();
            });
        }
    }

    if (selectedUserIdInput.value && selectedUserIdInput.value !== '') {
        searchInput.disabled = true;
        if (!document.getElementById('clear_selected_user') && selectedUserDisplay.innerHTML === '') {
             // If the display is empty but an ID is set (from old()), offer a way to clear.
             selectedUserDisplay.innerHTML = `<div class="flex items-center justify-between p-2 bg-gray-100 rounded-md"><span>User ID: ${selectedUserIdInput.value} (Search to update)</span><button type="button" id="clear_selected_user" class="text-red-600 ml-2 text-sm hover:underline font-semibold">Clear</button></div>`;
             addClearButtonListener();
        } else if (document.getElementById('clear_selected_user')) {
            // If a clear button already exists (e.g. from a previous dynamic add that failed validation)
            addClearButtonListener();
        }
    }
});
</script>
