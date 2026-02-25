<x-app-layout>
    @section('title', 'Create Invite Code')
    <x-slot name="header">
        {{ __('Create Invite Code') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.invite-codes.store') }}">
                        @csrf

                        <!-- Label -->
                        <div class="mb-6">
                            <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Label <span class="text-gray-500 text-xs">(internal reference, not shown to users)</span>
                            </label>
                            <input type="text" name="label" id="label" value="{{ old('label') }}"
                                   placeholder="e.g. 2026 Convention Volunteers"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('label') border-red-500 @enderror">
                            @error('label')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Code -->
                        <div class="mb-6">
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Code <span class="text-gray-500 text-xs">(auto-generated if left blank)</span>
                            </label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}"
                                   placeholder="Leave blank to auto-generate"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono uppercase @error('code') border-red-500 @enderror">
                            @error('code')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tags to Assign on Registration
                            </label>
                            @if($tags->isEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                                    No tags exist yet.
                                    <a href="{{ route('admin.tags.create') }}" class="text-brand-green hover:underline">Create a tag</a> first.
                                </p>
                            @else
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    @foreach($tags as $tag)
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                                   {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                                            <span class="flex items-center space-x-1 text-sm text-gray-700 dark:text-gray-300">
                                                @if($tag->color)
                                                    <span class="inline-block w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $tag->color }}"></span>
                                                @endif
                                                <span>{{ $tag->name }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('tags')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Uses -->
                        <div class="mb-6">
                            <label for="max_uses" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max Uses <span class="text-gray-500 text-xs">(leave blank for unlimited)</span>
                            </label>
                            <input type="number" name="max_uses" id="max_uses" value="{{ old('max_uses') }}"
                                   min="1" placeholder="Unlimited"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('max_uses') border-red-500 @enderror">
                            @error('max_uses')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expires At -->
                        <div class="mb-6">
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Expiry Date <span class="text-gray-500 text-xs">(leave blank for no expiry)</span>
                            </label>
                            <input type="datetime-local" name="expires_at" id="expires_at"
                                   value="{{ old('expires_at') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('expires_at') border-red-500 @enderror">
                            @error('expires_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active -->
                        <div class="mb-6">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active (can be used immediately)</span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('admin.invite-codes.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Create Invite Code
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
