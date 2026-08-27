<x-app-layout>
    @section('title', (isset($shift) ? 'Edit Shift' : 'Create Shift') . ' — ' . $event->name)
    <x-slot name="header">
        {{ isset($shift) ? 'Edit Shift' : 'Create Shift' }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            <x-heroicon-m-arrow-left class="h-4 w-4"/>
            Back to shifts
        </a>
    </x-slot>

    <div>
        <div class="mx-auto max-w-7xl">
            <div class="mb-6">
                <p class="text-sm font-medium text-brand-green">{{ $event->name }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ isset($shift) ? 'Update the shift details and manage its volunteer roster.' : 'Add a volunteer opportunity to this event.' }}
                </p>
            </div>

            @if(isset($shift) && $shift->users->isNotEmpty())
                <div class="mb-6 flex gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200" role="alert">
                    <x-heroicon-m-exclamation-triangle class="mt-0.5 h-5 w-5 flex-none"/>
                    <div>
                        <p class="font-semibold">This shift has assigned volunteers</p>
                        <p class="mt-1 text-sm">Changing the schedule may affect {{ $shift->users->count() }} {{ Str::plural('volunteer', $shift->users->count()) }}. Let them know if the time changes.</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200" role="alert">
                    <p class="font-semibold">Please fix the highlighted fields before saving.</p>
                </div>
            @endif

            <form method="POST"
                action="{{ isset($shift) ? route('admin.events.shifts.update', [$event, $shift]) : route('admin.events.shifts.store', $event) }}">
                @csrf
                @if (isset($shift))
                    @method('PUT')
                @endif

                <section aria-labelledby="shift-details-heading">
                    <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                        <h2 id="shift-details-heading" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-brand-green"/>
                            Shift details
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">What volunteers will see when deciding whether to sign up.</p>
                    </div>

                <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <label for="name" class="form-label">Shift name</label>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-full text-sm" type="text" name="name" id="name"
                            :value="old('name', $shift->name ?? '')" required />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                <div class="border-t border-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 dark:border-gray-800">
                    <div>
                        <label for="max_volunteers" class="form-label">Volunteers needed</label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The signup capacity for this shift.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-32 text-sm" type="number" name="max_volunteers"
                            id="max_volunteers" min="1"
                            value="{{ old('max_volunteers', $shift->max_volunteers ?? 1) }}" required />
                        <x-form-validation for="max_volunteers" />
                    </dd>
                </div>

                <div class="border-t border-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 dark:border-gray-800">
                    <div>
                        <label for="description" class="form-label">Description</label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Include duties, location, and anything volunteers should bring.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-textarea-input id="description" rows="5" name="description"
                            class="block w-full text-sm">{{ old('description', $shift->description ?? '') }}</x-textarea-input>
                        <x-form-validation for="description" />
                    </dd>
                </div>

                <div class="border-t border-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 dark:border-gray-800">
                    <div>
                        <dt class="form-label">Shift Tags</dt>
                        <p class="text-gray-500 text-sm mt-1">Categorize this shift for reporting (e.g., Cashier, BadgeChecker).</p>
                        <p class="text-amber-600 dark:text-amber-400 text-xs mt-2">
                            <x-heroicon-s-information-circle class="w-3.5 h-3.5 inline -mt-0.5 mr-0.5"/>
                            Tags are for reporting only and do <strong>not</strong> restrict who can sign up for this shift.
                            To require volunteers to have specific tags before signing up, edit the <a href="{{ route('admin.events.edit', $event) }}" class="underline hover:text-amber-700 dark:hover:text-amber-300">event settings</a> instead.
                        </p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        @if(isset($tags) && $tags->isNotEmpty())
                            <div class="flex flex-wrap gap-3">
                                @foreach ($tags as $tag)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="shift_tags[]"
                                            value="{{ $tag->id }}"
                                            {{ (isset($shift) && $shift->tags->contains($tag->id)) || (is_array(old('shift_tags')) && in_array($tag->id, old('shift_tags'))) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="inline-flex items-center text-sm text-gray-800 dark:text-gray-200">
                                            @if($tag->color)
                                                <span class="inline-block w-3 h-3 rounded mr-1" style="background-color: {{ $tag->color }}"></span>
                                            @endif
                                            {{ $tag->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 italic">
                                No tags available.
                                <a href="{{ route('admin.tags.create') }}" class="text-brand-green hover:underline">Create tags</a>
                                to use this feature.
                            </p>
                        @endif
                        <x-form-validation for="shift_tags" />
                    </dd>
                </div>

                <div class="border-t border-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 dark:border-gray-800">
                    <div>
                        <dt class="form-label">Event Categories</dt>
                        <p class="text-gray-500 text-sm mt-1">Categories are specific to this event and will be shown to volunteers on the shift sign-up page.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        @if(isset($categories) && $categories->isNotEmpty())
                            <div class="flex flex-wrap gap-3">
                                @foreach ($categories as $category)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="event_category_ids[]"
                                            value="{{ $category->id }}"
                                            {{ (isset($shift) && $shift->categories->contains($category->id)) || (is_array(old('event_category_ids')) && in_array($category->id, old('event_category_ids'))) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="inline-flex items-center text-sm text-gray-800 dark:text-gray-200">
                                            @if($category->color)
                                                <span class="inline-block w-3 h-3 rounded mr-1" style="background-color: {{ $category->color }}"></span>
                                            @endif
                                            {{ $category->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 italic">
                                No event categories available.
                                <a href="{{ route('admin.events.categories.create', $event) }}" class="text-brand-green hover:underline">Create a category</a>
                                to use this feature.
                            </p>
                        @endif
                        <x-form-validation for="event_category_ids" />
                    </dd>
                </div>
                </section>

                @php
                    $defaultStart = isset($shift) ? $shift->start_time : $event->start_date;
                    $defaultEnd = isset($shift) ? $shift->end_time : $event->start_date->copy()->addHour();
                @endphp

                <section class="mt-8" aria-labelledby="shift-schedule-heading">
                    <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                        <h2 id="shift-schedule-heading" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-calendar-days class="h-5 w-5 text-brand-green"/>
                            Schedule
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Times are shown in the event’s configured timezone.</p>
                    </div>

                <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <label for="start_time" class="form-label">Starts</label>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-full sm:w-72 text-sm" type="datetime-local" name="start_time" id="start_time"
                            value="{{ old('start_time', $defaultStart->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="start_time" />
                    </dd>
                </div>

                <div class="border-t border-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 dark:border-gray-800">
                    <label for="end_time" class="form-label">Ends</label>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-full sm:w-72 text-sm" type="datetime-local" name="end_time" id="end_time"
                            value="{{ old('end_time', $defaultEnd->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="end_time" />
                    </dd>
                </div>
                </section>

                <section class="mt-8" aria-labelledby="shift-settings-heading">
                    <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                        <h2 id="shift-settings-heading" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                            <x-heroicon-o-adjustments-horizontal class="h-5 w-5 text-brand-green"/>
                            Settings
                        </h2>
                    </div>

                <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <label for="double_hours" class="form-label">Double hours</label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Credit twice the scheduled hours to volunteers.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-checkbox-input class="block w-64 text-sm" name="double_hours" id="double_hours"
                            checked="{{ old('double_hours', isset($shift) ? $shift->double_hours : false) }}" />
                        <x-form-validation for="double_hours" />
                    </dd>
                </div>

                @if (isset($shift) && feature_enabled('accessibility_disclosures'))
                <div class="border-t border-gray-100 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 dark:border-gray-800">
                    <div>
                        <dt class="form-label">Accessibility Conflicts</dt>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Select any accessibility needs that may conflict with the duties or environment of this shift.
                        </p>
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                            These selections identify potential concerns and do not prevent a volunteer from signing up.
                        </p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-200 sm:col-span-2 sm:mt-0">
                        @php
                            $selectedAccessibilityConflicts = old(
                                'accessibility_conflicts',
                                $shift->accessibility_conflicts ?? []
                            );
                        @endphp
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($accessibilityNeeds as $accessibilityNeed)
                                <label class="flex cursor-pointer items-start gap-2">
                                    <input
                                        type="checkbox"
                                        name="accessibility_conflicts[]"
                                        value="{{ $accessibilityNeed }}"
                                        @checked(in_array($accessibilityNeed, $selectedAccessibilityConflicts, true))
                                        class="mt-0.5 rounded border-gray-300 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                    <span>{{ $accessibilityNeed }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-form-validation for="accessibility_conflicts" />
                        <x-form-validation for="accessibility_conflicts.*" />
                    </dd>
                </div>
                @endif
                </section>

                @if (isset($shift))
                {{-- User Search Section - Only show when editing existing shift --}}
                <section class="mt-8" aria-labelledby="add-volunteers-heading">
                <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 id="add-volunteers-heading" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-user-plus class="h-5 w-5 text-brand-green"/>
                        Add volunteers
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search by name, email, or volunteer code. Selecting Add updates the roster immediately.</p>
                </div>
                <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <label for="volunteer_search" class="form-label">Find a volunteer</label>
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
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Enter at least 2 characters to search.</p>
                    </dd>
                </div>
                </section>
                @endif

                <div class="sticky bottom-0 -mx-6 mt-8 flex items-center justify-end gap-3 border-t border-gray-200 bg-white/95 px-6 py-4 backdrop-blur dark:border-gray-700 dark:bg-slate-900/95">
                    <a href="{{ route('admin.events.shifts.index', $event) }}"
                        class="rounded-md px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-500 dark:text-gray-200 dark:hover:bg-gray-800">Cancel</a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-brand-green px-4 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-m-check class="h-4 w-4"/>
                        {{ isset($shift) ? 'Save changes' : 'Create shift' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    <x-slot name="right">
        @if (isset($shift))
        <div>
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned volunteers</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage attendance and roster access.</p>
                </div>
                <span id="volunteer-count" class="whitespace-nowrap rounded-full bg-gray-100 px-2.5 py-1 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ $shift->users->count() }} / {{ $shift->max_volunteers }}
                </span>
            </div>
            <div class="volunteers-list flex flex-col gap-3">
                @forelse ($shift->users as $user)
                    <div class="volunteer-item rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/60" data-user-id="{{ $user->id }}">
                        <div class="flex items-start gap-2">
                            @if($user->pivot->hours_logged_at)
                                <x-heroicon-m-check-badge title="Hours credited" class="mt-0.5 h-5 w-5 flex-none text-green-600"/>
                            @endif
                            <div class="min-w-0">
                                @if($user->pivot->no_show)
                                    <span class="no-show-badge mb-1 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200">No show</span>
                                @else
                                    <span class="no-show-badge mb-1 hidden inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-200">No show</span>
                                @endif
                                <div class="truncate font-medium">
                                    <a href="{{ route('users.show', $user->id) }}" class="text-blue-600 hover:underline dark:text-blue-300">{{ $user->displayName() }}</a>
                                </div>
                                <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 border-t border-gray-200 pt-2 dark:border-gray-700">
                            <button type="button"
                                    class="no-show-toggle-btn rounded px-2 py-1 text-xs font-medium text-amber-700 hover:bg-amber-100 disabled:opacity-50 dark:text-amber-300 dark:hover:bg-amber-950"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-no-show="{{ $user->pivot->no_show ? 1 : 0 }}">
                                <x-heroicon-m-exclamation-triangle class="w-4 inline mr-1"/>
                                {{ $user->pivot->no_show ? 'Unmark No Show' : 'Mark No Show' }}
                            </button>
                            <button type="button"
                                    class="remove-volunteer-btn rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-100 disabled:opacity-50 dark:text-red-300 dark:hover:bg-red-950"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}">
                                <x-heroicon-m-trash class="w-4 inline mr-1"/> Remove
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 py-8 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <x-heroicon-m-user-group class="mx-auto mb-2 h-10 w-10 text-gray-300 dark:text-gray-600"/>
                        <p class="font-medium">No volunteers assigned</p>
                        <p class="mt-1 text-sm">Use the search form to add someone.</p>
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
    </x-slot>
</x-app-layout>

@if (isset($shift))
<!-- Notification area -->
<div id="notification" class="fixed right-4 top-4 z-50 hidden" role="status" aria-live="polite">
    <div class="max-w-sm rounded-lg border border-gray-200 bg-white p-4 shadow-lg">
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
            fetch(searchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Search request failed');
                    }

                    return response.json();
                })
                .then(users => {
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
        ul.className = 'max-h-60 overflow-y-auto rounded-md border border-gray-300 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900';

        users.forEach(user => {
            const li = document.createElement('li');
            li.className = 'flex items-center justify-between gap-3 border-b border-gray-200 p-3 last:border-b-0 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800';

            const identity = document.createElement('div');
            identity.className = 'min-w-0';

            const name = document.createElement('div');
            name.className = 'truncate font-medium text-gray-900 dark:text-gray-100';
            name.textContent = user.name;

            const email = document.createElement('div');
            email.className = 'truncate text-sm text-gray-500 dark:text-gray-400';
            email.textContent = user.email;

            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'add-volunteer-btn rounded bg-brand-green px-3 py-1.5 text-sm font-semibold text-white hover:bg-green-800 disabled:opacity-50';
            addButton.dataset.userId = user.id;
            addButton.textContent = 'Add';

            identity.append(name, email);
            li.append(identity, addButton);

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

    // Handle no-show toggle buttons
    document.querySelectorAll('.no-show-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            const isNoShow = this.dataset.noShow === '1';
            const actionLabel = isNoShow ? 'unmark' : 'mark';

            if (confirm(`Are you sure you want to ${actionLabel} ${userName} as a no show?`)) {
                toggleNoShow(userId, this);
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

    function toggleNoShow(userId, button) {
        const volunteerItem = button.closest('.volunteer-item');
        const badge = volunteerItem.querySelector('.no-show-badge');
        const isNoShow = button.dataset.noShow === '1';
        const nextNoShow = !isNoShow;

        button.disabled = true;
        button.innerHTML = '<span class="inline-block w-4 h-4 mr-1 animate-spin">⟳</span> Updating...';

        fetch(`{{ route('admin.events.shifts.no-show', [$event, $shift, '']) }}/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ no_show: nextNoShow ? 1 : 0 })
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || !data.success) {
                throw new Error(data.message || 'Failed to update no show status');
            }

            showNotification(data.message, 'success');
            button.dataset.noShow = data.no_show ? '1' : '0';
            button.innerHTML = data.no_show
                ? '<svg class="w-4 inline mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v6h-2V7h2zm0 8v2h-2v-2h2z"/></svg> Unmark No Show'
                : '<svg class="w-4 inline mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v6h-2V7h2zm0 8v2h-2v-2h2z"/></svg> Mark No Show';

            if (badge) {
                if (data.no_show) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            button.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'An error occurred while updating no show status', 'error');
            button.disabled = false;
            button.innerHTML = isNoShow
                ? '<svg class="w-4 inline mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v6h-2V7h2zm0 8v2h-2v-2h2z"/></svg> Unmark No Show'
                : '<svg class="w-4 inline mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v6h-2V7h2zm0 8v2h-2v-2h2z"/></svg> Mark No Show';
        });
    }

    function updateVolunteerCount() {
        const volunteerItems = document.querySelectorAll('.volunteer-item');
        const countSpan = document.getElementById('volunteer-count');
        if (countSpan) {
            countSpan.textContent = `${volunteerItems.length} / {{ $shift->max_volunteers }}`;
        }
    }
});
</script>
@endif
