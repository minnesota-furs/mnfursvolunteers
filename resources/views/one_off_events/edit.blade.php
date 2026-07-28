<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Edit Simple Volunteer Event') }}
        </x-slot>

        <x-slot name="actions">
            @if($oneOffEvent->isRsvpType())
                <a href="{{ route('simple-volunteer-events.rsvps', $oneOffEvent) }}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                    <x-heroicon-o-user-group class="w-4 inline"/> View RSVPs
                </a>
            @else
                <a href="{{ route('simple-volunteer-events.check-ins', $oneOffEvent) }}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                    <x-heroicon-o-user-group class="w-4 inline"/> View Check-ins
                </a>
            @endif
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Edit Simple Volunteer Event</h1>

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
            
                <form method="POST" action="{{ route('simple-volunteer-events.update', $oneOffEvent) }}" x-data="{ type: '{{ old('type', $oneOffEvent->type) }}', endTime: '{{ old('end_time', $oneOffEvent->end_time?->format('Y-m-d\TH:i')) }}' }">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $oneOffEvent->name) }}"
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
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $oneOffEvent->description) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                        <input type="text" name="location" id="location" value="{{ old('location', $oneOffEvent->location) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div class="mb-4" x-show="type === 'rsvp'" x-cloak>
                        <label for="max_rsvps" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max RSVPs (optional)</label>
                        <input type="number" name="max_rsvps" id="max_rsvps" min="1" step="1" value="{{ old('max_rsvps', $oneOffEvent->max_rsvps) }}"
                            class="mt-1 block w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank for unlimited RSVPs. Once reached, RSVPs will close automatically.</p>
                        @if($oneOffEvent->isRsvpType() && $oneOffEvent->max_rsvps !== null)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Currently {{ $oneOffEvent->rsvps()->count() }} of {{ $oneOffEvent->max_rsvps }} RSVPs used.</p>
                        @endif
                        <x-form-validation for="max_rsvps" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                            <input type="datetime-local" name="start_time" id="start_time" required
                                value="{{ old('start_time', $oneOffEvent->start_time->format('Y-m-d\TH:i')) }}"
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
                        <label class="inline-flex items-center" :class="!endTime ? 'opacity-50 cursor-not-allowed' : ''">
                            <input type="checkbox" name="auto_credit_hours" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('auto_credit_hours', $oneOffEvent->auto_credit_hours) ? 'checked' : '' }}
                                :disabled="!endTime"
                                x-effect="if (!endTime && $el.checked) $el.checked = false">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Automatically credit hours when users check in</span>
                        </label>
                        @if($oneOffEvent->end_time)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-6">
                                Hours will be calculated based on event duration ({{ $oneOffEvent->start_time->floatDiffInHours($oneOffEvent->end_time) }} hours) and granted instantly the moment a volunteer checks in
                            </p>
                        @else
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-6">
                                Hours will be calculated based on event duration and granted instantly the moment a volunteer checks in
                            </p>
                        @endif
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 ml-6" x-show="!endTime" x-cloak>Set an end time above to enable automatic hour crediting.</p>
                        <x-form-validation for="auto_credit_hours" />
                    </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" x-show="type === 'check_in'" x-cloak>
                <div>
                    <label for="checkin_hours_before" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Opens (Hours Before Start)</label>
                    <input type="number" name="checkin_hours_before" id="checkin_hours_before" min="0" max="48" step="1" value="{{ old('checkin_hours_before', $oneOffEvent->checkin_hours_before) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Volunteers can check in this many hours before the event starts</p>
                </div>

                <div>
                    <label for="checkin_hours_after" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Closes (Hours After End)</label>
                    <input type="number" name="checkin_hours_after" id="checkin_hours_after" min="0" max="72" step="1" value="{{ old('checkin_hours_after', $oneOffEvent->checkin_hours_after) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Volunteers can check in this many hours after the event ends</p>
                </div>
            </div>

                    @include('one_off_events._restrictions-fields')

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('simple-volunteer-events.show', $oneOffEvent) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancel
                        </a>
            
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand-green hover:bg-indigo-700">
                            Update Event
                        </button>
                    </div>
                </form>

                {{-- Delete Form (Outside Update Form) --}}
                <form method="POST" action="{{ route('simple-volunteer-events.destroy', $oneOffEvent) }}" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
                      onsubmit="return confirm('Are you sure you want to delete this event? This will also delete all check-ins/RSVPs.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700">
                        <x-heroicon-m-trash class="w-4 inline mr-1"/> Delete Event
                    </button>
                </form>
            </div>
        </div>
    @endauth
</x-app-layout>
