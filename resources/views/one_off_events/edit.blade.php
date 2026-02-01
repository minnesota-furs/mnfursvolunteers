<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Edit One Off Event') }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('one-off-events.check-ins', $oneOffEvent) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                <x-heroicon-o-user-group class="w-4 inline"/> View Check-ins
            </a>
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Edit One-Off Event</h1>

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
            
                <form method="POST" action="{{ route('one-off-events.update', $oneOffEvent) }}">
                    @csrf
                    @method('PUT')
            
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $oneOffEvent->name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
            
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $oneOffEvent->description) }}</textarea>
                    </div>
            
                    <div class="mb-4">
                        <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                        <input type="datetime-local" name="start_time" id="start_time" required 
                            value="{{ old('start_time', $oneOffEvent->start_time->format('Y-m-d\TH:i')) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
            
                    <div class="mb-4">
                        <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
                        <input type="datetime-local" name="end_time" id="end_time" required 
                            value="{{ old('end_time', $oneOffEvent->end_time->format('Y-m-d\TH:i')) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
            
                    <div class="mb-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="auto_credit_hours" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('auto_credit_hours', $oneOffEvent->auto_credit_hours) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Automatically credit hours when users check in</span>
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-6">
                            Hours will be calculated based on event duration ({{ $oneOffEvent->start_time->floatDiffInHours($oneOffEvent->end_time) }} hours)
                        </p>
                    </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
    
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('one-off-events.show', $oneOffEvent) }}"
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
                <form method="POST" action="{{ route('one-off-events.destroy', $oneOffEvent) }}" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700"
                      onsubmit="return confirm('Are you sure you want to delete this event? This will also delete all check-ins.');">
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
