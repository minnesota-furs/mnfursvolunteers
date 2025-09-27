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

                @if (isset($shift))
                {{-- User Search Section - Only show when editing existing shift --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900">Add Volunteers</dt>
                        <p class="text-gray-500 text-sm mt-1">Search and instantly add volunteers to this shift</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <div class="relative">
                            <x-text-input type="text" id="volunteer_search" class="block w-full text-sm pr-10" placeholder="Type volunteer name or email..." />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <x-heroicon-m-magnifying-glass class="w-4 h-4 text-gray-400"/>
                            </div>
                        </div>
                        <div id="search_results" class="mt-2 hidden"></div>
                        <div id="search_message" class="mt-2 text-sm text-gray-500 hidden"></div>
                        <p class="text-xs text-gray-400 mt-1">
                            💡 Tip: Type at least 2 characters to search. Volunteers are added instantly when you click "Add".
                        </p>
                    </dd>
                </div>
                @endif

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
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-3 dark:text-white">
                Volunteers Assigned 
                <span class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $shift->users->count() }} / {{ $shift->max_volunteers }}</span>
            </h2>
            <div class="volunteers-list">
                @forelse ($shift->users as $user)
                    <div class="volunteer-item flex items-center justify-between p-3 mb-2 bg-gray-50 rounded-lg border" data-user-id="{{ $user->id }}">
                        <div class="flex items-center">
                            @if($user->pivot->hours_logged_at)
                                <x-heroicon-m-check-badge title="Hours Credited" class="w-5 mr-2 text-green-600"/> 
                            @endif
                            <div>
                                <div class="font-medium">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('users.show', $user->id) }}" 
                               class="text-blue-600 hover:underline text-sm">
                                View Profile
                            </a>
                            <button type="button" 
                                    class="remove-volunteer-btn text-red-600 hover:bg-red-50 px-2 py-1 rounded text-sm"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}">
                                <x-heroicon-m-trash class="w-4 inline mr-1"/> Remove
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <x-heroicon-m-user-group class="w-12 h-12 mx-auto mb-2 text-gray-300"/>
                        <p>No volunteers assigned yet</p>
                        <p class="text-sm">Use the search above to add volunteers</p>
                    </div>
                @endforelse
            </div>
        </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <x-heroicon-m-information-circle class="w-12 h-12 mx-auto mb-2 text-gray-300"/>
                <h2 class="text-xl font-semibold mb-2 dark:text-white">Create Shift First</h2>
                <p class="text-gray-500 dark:text-gray-400">Save the shift to start adding volunteers</p>
            </div>
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

@if (isset($shift))
<!-- Notification area -->
<div id="notification" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
        <div class="flex items-center">
            <div id="notification-icon" class="mr-3"></div>
            <div id="notification-message" class="text-sm"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('volunteer_search');
    const searchResults = document.getElementById('search_results');
    const searchMessage = document.getElementById('search_message');
    const volunteersContainer = document.querySelector('.volunteers-list');
    
    let searchTimeout;

    // Notification functions
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const icon = document.getElementById('notification-icon');
        const messageEl = document.getElementById('notification-message');
        
        messageEl.textContent = message;
        
        if (type === 'success') {
            icon.innerHTML = '<div class="w-5 h-5 text-green-500">✓</div>';
            notification.querySelector('div').className = 'bg-green-50 border border-green-200 rounded-lg shadow-lg p-4 max-w-sm';
            messageEl.className = 'text-sm text-green-800';
        } else {
            icon.innerHTML = '<div class="w-5 h-5 text-red-500">✕</div>';
            notification.querySelector('div').className = 'bg-red-50 border border-red-200 rounded-lg shadow-lg p-4 max-w-sm';
            messageEl.className = 'text-sm text-red-800';
        }
        
        notification.classList.remove('hidden');
        
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 4000);
    }

    // Search for volunteers
    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideSearchResults();
            return;
        }

        // Show loading message
        showMessage('Searching...', 'text-gray-500');
        
        searchTimeout = setTimeout(() => {
            const searchUrl = `{{ route('admin.users.search') }}?term=${encodeURIComponent(query)}`;
            console.log('Searching for:', query, 'URL:', searchUrl);
            
            fetch(searchUrl)
                .then(response => {
                    console.log('Search response status:', response.status);
                    return response.json();
                })
                .then(users => {
                    console.log('Search results:', users);
                    displaySearchResults(users);
                })
                .catch(error => {
                    console.error('Search error:', error);
                    showMessage('Error searching volunteers', 'text-red-500');
                });
        }, 300);
    });

    function displaySearchResults(users) {
        if (users.length === 0) {
            showMessage('No volunteers found', 'text-gray-500');
            return;
        }

        searchResults.innerHTML = '';
        searchResults.classList.remove('hidden');
        searchMessage.classList.add('hidden');

        const ul = document.createElement('ul');
        ul.className = 'border border-gray-300 rounded-md bg-white shadow-lg max-h-60 overflow-y-auto';

        users.forEach(user => {
            const li = document.createElement('li');
            li.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 flex justify-between items-center';
            
            li.innerHTML = `
                <div>
                    <div class="font-medium">${user.name}</div>
                    <div class="text-sm text-gray-500">${user.email}</div>
                </div>
                <button type="button" class="add-volunteer-btn bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700" data-user-id="${user.id}">
                    Add
                </button>
            `;

            ul.appendChild(li);
        });

        searchResults.appendChild(ul);

        // Add click handlers for add buttons
        searchResults.querySelectorAll('.add-volunteer-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                addVolunteerToShift(userId, this);
            });
        });
    }

    function addVolunteerToShift(userId, button) {
        button.disabled = true;
        button.textContent = 'Adding...';

        fetch(`{{ route('admin.events.shifts.add-volunteer', [$event, $shift, '']) }}/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Refresh the volunteers list by reloading the page
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Failed to add volunteer', 'error');
                button.disabled = false;
                button.textContent = 'Add';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while adding the volunteer', 'error');
            button.disabled = false;
            button.textContent = 'Add';
        });
    }

    function hideSearchResults() {
        searchResults.classList.add('hidden');
        searchMessage.classList.add('hidden');
    }

    function showMessage(message, className) {
        searchMessage.textContent = message;
        searchMessage.className = `mt-2 text-sm ${className}`;
        searchMessage.classList.remove('hidden');
        searchResults.classList.add('hidden');
    }

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            hideSearchResults();
        }
    });

    // Handle remove volunteer buttons
    document.querySelectorAll('.remove-volunteer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            if (confirm(`Are you sure you want to remove ${userName} from this shift?`)) {
                removeVolunteerFromShift(userId, this);
            }
        });
    });

    function removeVolunteerFromShift(userId, button) {
        const volunteerItem = button.closest('.volunteer-item');
        button.disabled = true;
        button.innerHTML = '<span class="inline-block w-4 h-4 mr-1 animate-spin">⟳</span> Removing...';

        fetch(`{{ route('admin.events.shifts.remove-volunteer', [$event, $shift, '']) }}/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Remove the volunteer item with animation
                volunteerItem.style.transition = 'all 0.3s ease';
                volunteerItem.style.opacity = '0';
                volunteerItem.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    volunteerItem.remove();
                    // Update the count in the header
                    updateVolunteerCount();
                }, 300);
            } else {
                showNotification(data.message || 'Failed to remove volunteer', 'error');
                button.disabled = false;
                button.innerHTML = '<span class="w-4 inline mr-1">🗑</span> Remove';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while removing the volunteer', 'error');
            button.disabled = false;
            button.innerHTML = '<span class="w-4 inline mr-1">🗑</span> Remove';
        });
    }

    function updateVolunteerCount() {
        const volunteerItems = document.querySelectorAll('.volunteer-item');
        const countSpan = document.querySelector('h2 span');
        if (countSpan) {
            countSpan.textContent = `${volunteerItems.length} / {{ $shift->max_volunteers }}`;
        }
    }
});
</script>
@endif
