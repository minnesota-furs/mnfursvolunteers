<x-app-layout>
    @section('title', 'Add Recognition')
    <x-slot name="header">
        {{ __('Add Recognition & Award') }}
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8">
        <form action="{{ route('admin.recognitions.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- User -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            User <span class="text-red-600">*</span>
                        </label>
                        <select name="user_id" id="user_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('user_id') border-red-500 @enderror">
                            <option value="">Select a user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Recognition Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Recognition Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('name') border-red-500 @enderror" placeholder="e.g., Outstanding Volunteer">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type <span class="text-red-600">*</span>
                        </label>
                        <select name="type" id="type" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('type') border-red-500 @enderror">
                            <option value="">Select a type</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Date <span class="text-red-600">*</span>
                        </label>
                        <input type="date" name="date" id="date" required value="{{ old('date', today()->toDateString()) }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('date') border-red-500 @enderror">
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sector -->
                    <div>
                        <label for="sector_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Granting Sector (Optional)
                        </label>
                        <select name="sector_id" id="sector_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('sector_id') border-red-500 @enderror">
                            <option value="">Select a sector</option>
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sector_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Privacy -->
                    <div class="flex items-center md:col-span-1">
                        <input type="checkbox" name="is_private" id="is_private" value="1" {{ old('is_private') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                        <label for="is_private" class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Private (only visible to users with manage-recognition permission)
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description (Optional)
                    </label>
                    <textarea name="description" id="description" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('description') border-red-500 @enderror" placeholder="Additional details about this recognition...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" class="inline-flex justify-center rounded-md bg-brand-green px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                    Add Recognition
                </button>
                <a href="{{ route('admin.recognitions.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
