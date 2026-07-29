<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Create New Simple Volunteer Event') }}
        </x-slot>

        <x-slot name="actions">
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Create Simple Volunteer Event</h1>

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200 rounded">
                        <strong>There were some problems with your input:</strong>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            
                <form method="POST" action="{{ route('simple-volunteer-events.store') }}" x-data="{ type: '{{ old('type', 'check_in') }}', endTime: '{{ old('end_time') }}' }">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Type</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="relative flex cursor-pointer rounded-lg border p-3 focus:outline-none"
                                   :class="type === 'check_in' ? 'border-brand-green ring-1 ring-brand-green bg-green-50 dark:bg-green-900/10' : 'border-gray-300 dark:border-gray-600'">
                                <input type="radio" name="type" value="check_in" x-model="type" class="sr-only">
                                <span class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Check-In (Earns Hours)</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Volunteers check in during the event and earn volunteer hours. Good for staff meetings, board meetings, etc.</span>
                                </span>
                            </label>
                            <label class="relative flex cursor-pointer rounded-lg border p-3 focus:outline-none"
                                   :class="type === 'rsvp' ? 'border-brand-green ring-1 ring-brand-green bg-green-50 dark:bg-green-900/10' : 'border-gray-300 dark:border-gray-600'">
                                <input type="radio" name="type" value="rsvp" x-model="type" class="sr-only">
                                <span class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">RSVP (No Hours)</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Volunteers RSVP to let you know they're coming. No hours are credited. Good for appreciation events, BBQs, pizza parties, etc.</span>
                                </span>
                            </label>
                        </div>
                        <x-form-validation for="type" />
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Supports Markdown formatting (e.g., **bold**, *italic*, [links](url), etc.)</p>
                    </div>

                    <div class="mb-4">
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                        <input type="text" name="location" id="location" value="{{ old('location') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div class="mb-4">
                        <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL (optional)</label>
                        <input type="url" name="url" id="url" value="{{ old('url') }}" placeholder="https://zoom.us/j/..."
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">For virtual meetings. If set, the location will link here.</p>
                        <x-form-validation for="url" />
                    </div>

                    <div class="mb-4" x-show="type === 'rsvp'" x-cloak>
                        <label for="max_rsvps" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max RSVPs (optional)</label>
                        <input type="number" name="max_rsvps" id="max_rsvps" min="1" step="1" value="{{ old('max_rsvps') }}"
                            class="mt-1 block w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank for unlimited RSVPs. Once reached, RSVPs will close automatically.</p>
                        <x-form-validation for="max_rsvps" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                            <input type="datetime-local" name="start_time" id="start_time" required value="{{ old('start_time') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time (optional)</label>
                            <input type="datetime-local" name="end_time" id="end_time" x-model="endTime"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank for an open-ended event. Automatic hour crediting requires an end time.</p>
                            <x-form-validation for="end_time" />
                        </div>
                    </div>

                    <div class="mb-6" x-show="type === 'check_in'" x-cloak>
                        <label class="inline-flex items-start" :class="!endTime ? 'opacity-50 cursor-not-allowed' : ''">
                            <input type="checkbox" name="auto_credit_hours" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 mt-0.5"
                                {{ old('auto_credit_hours', true) ? 'checked' : '' }}
                                :disabled="!endTime"
                                x-effect="if (!endTime && $el.checked) $el.checked = false">
                            <span class="ml-2">
                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Automatically credit hours when users check in</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Hours will be calculated based on event duration and granted instantly the moment a volunteer checks in</span>
                            </span>
                        </label>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1" x-show="!endTime" x-cloak>Set an end time above to enable automatic hour crediting.</p>
                        <x-form-validation for="auto_credit_hours" />
                    </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" x-show="type === 'check_in'" x-cloak>
                <div>
                    <label for="checkin_hours_before" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Opens (Hours Before Start)</label>
                    <input type="number" name="checkin_hours_before" id="checkin_hours_before" min="0" max="48" step="1" value="{{ old('checkin_hours_before', 1) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Volunteers can check in this many hours before the event starts</p>
                </div>

                <div>
                    <label for="checkin_hours_after" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Closes (Hours After End)</label>
                    <input type="number" name="checkin_hours_after" id="checkin_hours_after" min="0" max="72" step="1" value="{{ old('checkin_hours_after', 12) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Volunteers can check in this many hours after the event ends</p>
                </div>
            </div>

                    @include('one_off_events._restrictions-fields')

                    <div class="flex justify-end">
                        <a href="{{ route('simple-volunteer-events.index') }}"
                           class="mr-4 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancel
                        </a>
            
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand-green hover:bg-indigo-700">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endauth
</x-app-layout>
