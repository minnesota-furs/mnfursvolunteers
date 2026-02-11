<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Page') }}
            </h2>
            <a href="{{ route('settings.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Back to Settings
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('pages.update', $page) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Current Page Info -->
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Current Page</h3>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <p><strong>Slug:</strong> {{ $page->slug }}</p>
                                <p><strong>URL:</strong> <a href="{{ url($page->slug === 'home' ? '/' : '/' . $page->slug) }}" target="_blank" class="underline hover:no-underline">{{ $page->slug === 'home' ? '/' : '/' . $page->slug }}</a></p>
                                <p><strong>Created:</strong> {{ $page->created_at->format('M d, Y g:i A') }}</p>
                                <p><strong>Last Updated:</strong> {{ $page->updated_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>

                        <!-- Slug Field -->
                        <div class="mb-6">
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Page Slug <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                name="slug" 
                                id="slug" 
                                value="{{ old('slug', $page->slug) }}"
                                required
                                pattern="[a-z0-9-]+"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="page-slug">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Use lowercase letters, numbers, and hyphens only. This will be the URL path.
                            </p>
                            @error('slug')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($page->slug === 'home')
                            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                            Warning: Home Page
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                            <p>This is your home page. Changing the slug will affect your site's homepage routing. Make sure you understand the implications before proceeding.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex gap-3">
                                <a href="{{ route('page.editor', $page) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Open Editor
                                </a>
                                
                                <a href="{{ url($page->slug === 'home' ? '/' : '/' . $page->slug) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    View Page
                                </a>
                            </div>

                            <div class="flex gap-3">
                                <a href="{{ route('settings.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Cancel
                                </a>
                                
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-green-dark focus:bg-brand-green-dark active:bg-brand-green-darker focus:outline-none focus:ring-2 focus:ring-brand-green focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Update Page
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
