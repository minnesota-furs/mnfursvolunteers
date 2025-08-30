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
                        <dt class="text-sm font-medium leading-6 text-gray-900">Assign Users</dt>
                        <p class="text-gray-500 text-sm mt-1">Manually add volunteers. Be sure to save after</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input type="text" id="user_search_input" name="user_search_input" class="block w-full text-sm" placeholder="Search by name or email..." />
                        <div id="user_search_results" class="mt-2"></div>
                        <div id="selected_users_container"></div>
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
        {{-- asds --}}
        {{-- <form method="POST" action="{{ route('shifts.quick-add', $shift) }}" class="flex items-center gap-2">
            @csrf
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="form-label">Shift Name</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                    <x-text-input class="block w-full text-sm" type="text" name="vol_code" maxlength="6"
                         placeholder="VOLCODE" oninput="this.value=this.value.toUpperCase()" required />
                    <x-form-validation for="name" />
                </dd>
            </div>
        </form> --}}

        {{-- <form method="POST" action="{{ route('shifts.quick-add', $shift) }}" class="flex items-center gap-2">
            @csrf
            <input type="text" name="vol_code" maxlength="6" class="border rounded px-2 py-1 uppercase tracking-widest w-28"
                    placeholder="VOLCODE" oninput="this.value=this.value.toUpperCase()" required />
            <button class="bg-emerald-600 text-white rounded px-3 py-1">Quick add</button>
        </form> --}}
    </x-slot>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('user_search_input');
    const searchResultsContainer = document.getElementById('user_search_results');
    const selectedUsersContainer = document.getElementById('selected_users_container');
    
    // An array to keep track of the IDs of selected users
    let selectedUserIds = new Set(); 

    // Re-populate selected users from old data (if any) on page load
    const oldUserIds = JSON.parse("{{ json_encode(old('user_id', [])) }}");
    if (oldUserIds.length > 0) {
        // You'll need to fetch user names here if you want to display them on reload
        // A simple solution is to just display the ID or re-fetch them.
        // For now, let's just add the IDs to our set.
        oldUserIds.forEach(id => {
            selectedUserIds.add(id);
            // You can add a placeholder display here, e.g., 'User (ID: ${id})'
        });
    }

    searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value.trim();
        searchResultsContainer.innerHTML = '';

        if (searchTerm.length < 2) {
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
                // Filter out users who are already selected
                const unselectedUsers = users.filter(user => !selectedUserIds.has(user.id.toString()));

                if (unselectedUsers.length > 0) {
                    const ul = document.createElement('ul');
                    ul.className = 'border border-gray-300 rounded-md mt-1 max-h-60 overflow-y-auto bg-white shadow-lg';
                    unselectedUsers.forEach(user => {
                        const li = document.createElement('li');
                        li.className = 'p-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200';
                        const firstName = user.first_name || '';
                        const lastName = user.last_name || '';
                        const displayName = `${user.name} - ${user.email}`.trim();
                        li.textContent = displayName;
                        li.dataset.userId = user.id;
                        li.dataset.userName = `${user.name}`.trim();

                        li.addEventListener('click', function () {
                            const userId = this.dataset.userId;
                            const userName = this.dataset.userName;
                            
                            // Check if the user is already selected before adding
                            if (!selectedUserIds.has(userId)) {
                                selectedUserIds.add(userId);
                                appendSelectedUser(userId, userName);
                                
                                searchInput.value = ''; // Clear search input
                                searchResultsContainer.innerHTML = ''; // Clear results list
                            }
                        });
                        ul.appendChild(li);
                    });
                    searchResultsContainer.appendChild(ul);
                } else {
                    searchResultsContainer.innerHTML = '<p class="text-gray-500 p-2">No users found or all found users are already selected.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching users:', error);
                searchResultsContainer.innerHTML = '<p class="text-red-500 p-2">Error searching users. Please try again.</p>';
            });
    });

    function appendSelectedUser(userId, userName) {
        const userDisplay = document.createElement('div');
        userDisplay.className = 'flex items-center justify-between p-2 mt-2 bg-gray-100 rounded-md';
        userDisplay.innerHTML = `
            <span>Selected: <strong>${userName}</strong></span>
            <button type="button" class="remove-user-button text-red-600 ml-2 text-sm hover:underline font-semibold" data-user-id="${userId}">
                Remove
            </button>
            <input type="hidden" name="user_id[]" value="${userId}">
        `;
        selectedUsersContainer.appendChild(userDisplay);
        
        // Add listener for the remove button
        userDisplay.querySelector('.remove-user-button').addEventListener('click', function() {
            const idToRemove = this.dataset.userId;
            selectedUserIds.delete(idToRemove);
            userDisplay.remove();
        });
    }

});
</script>
