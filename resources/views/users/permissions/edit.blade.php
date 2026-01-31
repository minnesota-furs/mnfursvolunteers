<x-app-layout>
    @section('title', 'Edit Permissions - ' . $user->name)
    
    <x-slot name="header">
        {{ __('Edit User Permissions: ') }} {{ $user->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('users.show', $user) }}" 
            class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Profile
        </a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">User Permissions</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Manage what {{ $user->name }} can do in the system. Select the appropriate permissions below.
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-3 py-1 text-xs font-medium text-blue-800 dark:text-blue-200">
                                {{ count($user->permissions ?? []) }} Permissions Active
                            </span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('users.permissions.update', $user) }}" class="px-6 py-6">
                    @csrf

                    <fieldset>
                        <legend class="sr-only">User Permissions</legend>
                        <div class="space-y-1 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach (config('permissions') as $key => $permission)
                                <div class="relative flex items-start py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 -mx-4 px-4 rounded-lg transition-colors duration-150">
                                    <div class="flex h-6 items-center">
                                        <input 
                                            id="permission-{{ $key }}" 
                                            name="permissions[]" 
                                            value="{{ $permission['label'] }}"
                                            type="checkbox"
                                            {{ in_array($permission['label'], $user->permissions ?? []) ? 'checked' : '' }}
                                            class="h-5 w-5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-brand-green focus:ring-brand-green dark:focus:ring-offset-gray-800 transition-colors duration-150 cursor-pointer">
                                    </div>
                                    <div class="ml-4 flex-1 cursor-pointer" onclick="document.getElementById('permission-{{ $key }}').click()">
                                        <label for="permission-{{ $key }}" class="block">
                                            <div class="flex items-center">
                                                <span class="font-semibold text-gray-900 dark:text-white text-base">
                                                    {{ $permission['label'] }}
                                                </span>
                                                @if(in_array($permission['label'], $user->permissions ?? []))
                                                    <svg class="ml-2 w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $permission['description'] }}
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Changes take effect immediately after saving
                        </p>
                        <div class="flex space-x-3">
                            <a href="{{ route('users.show', $user) }}" 
                                class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="inline-flex items-center rounded-md bg-brand-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green transition-colors duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Permissions
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Permission Guide -->
            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Permission Tips</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Admin users automatically have all permissions</li>
                                <li>Users only see features they have permission to access</li>
                                <li>Multiple permissions can be granted to a single user</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
