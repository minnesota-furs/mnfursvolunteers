<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Create New One Off Event') }}
        </x-slot>

        <x-slot name="actions">
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Create One-Off Event</h1>

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
            
                <form method="POST" action="{{ route('one-off-events.store') }}">
                    @csrf
            
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
            
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                    </div>
            
                    <div class="mb-4">
                        <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                        <input type="datetime-local" name="start_time" id="start_time" required value="{{ old('start_time') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
            
                    <div class="mb-4">
                        <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
                        <input type="datetime-local" name="end_time" id="end_time" required value="{{ old('end_time') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
            
                    <div class="mb-6">
                        <label class="inline-flex items-start">
                            <input type="checkbox" name="auto_credit_hours" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 mt-0.5"
                                {{ old('auto_credit_hours', true) ? 'checked' : '' }}>
                            <span class="ml-2">
                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Automatically credit hours when users check in</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">Hours will be calculated based on event duration</span>
                            </span>
                        </label>
                    </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
    
                    <div class="flex justify-end">
                        <a href="{{ route('one-off-events.index') }}"
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
