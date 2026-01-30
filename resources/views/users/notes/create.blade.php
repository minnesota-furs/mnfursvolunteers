<x-app-layout>
    @auth
        @section('title', 'New Note - ' . $user->name)
        <x-slot name="header">
            {{ __('New Note: ') }}{{ $user->name }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('users.notes.index', $user) }}" 
                class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Back to Notes
            </a>
        </x-slot>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Add New Note for {{ $user->name }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create a new note to document information about this user.
                        </p>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <form action="{{ route('users.notes.store', $user) }}" method="POST">
                            @csrf
                            <div class="space-y-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Title <span class="text-gray-500 dark:text-gray-400 font-normal">(Optional)</span>
                                    </label>
                                    <input type="text" id="title" name="title" value="{{ old('title') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm"
                                        placeholder="Brief title for this note">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Note Type <span class="text-red-500">*</span>
                                    </label>
                                    <select id="type" name="type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm">
                                        <option value="Standard" {{ old('type') === 'Standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="Writeup" {{ old('type') === 'Writeup' ? 'selected' : '' }}>Writeup</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Writeup notes will be highlighted with special formatting.
                                    </p>
                                    @error('type')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Content <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="content" name="content" rows="8" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm"
                                        placeholder="Enter the note content here...">{{ old('content') }}</textarea>
                                    @error('content')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="private" name="private" type="checkbox" value="1" {{ old('private') ? 'checked' : '' }}
                                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-brand-green focus:ring-brand-green">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="private" class="font-medium text-gray-700 dark:text-gray-300">
                                                Private Note
                                            </label>
                                            <p class="text-gray-500 dark:text-gray-400">
                                                Private notes are only visible to you and administrators. When not private, the user can see this note.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('users.notes.index', $user) }}"
                                        class="inline-flex justify-center rounded-md bg-white dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        Cancel
                                    </a>
                                    <button type="submit"
                                        class="inline-flex justify-center rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                                        Create Note
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
