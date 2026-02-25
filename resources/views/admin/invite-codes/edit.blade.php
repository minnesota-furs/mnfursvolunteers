<x-app-layout>
    @section('title', 'Edit Invite Code')
    <x-slot name="header">
        {{ __('Edit Invite Code') }}
    </x-slot>

    <div class="mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.invite-codes.update', $inviteCode) }}">
                    @csrf
                    @method('PUT')

                    <!-- Label -->
                    <div class="mb-6">
                        <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Label <span class="text-gray-500 text-xs">(internal reference, not shown to users)</span>
                        </label>
                        <input type="text" name="label" id="label" value="{{ old('label', $inviteCode->label) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('label') border-red-500 @enderror">
                        @error('label')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div class="mb-6">
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Code
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="text" name="code" id="code" value="{{ old('code', $inviteCode->code) }}"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono uppercase @error('code') border-red-500 @enderror">
                            <form action="{{ route('admin.invite-codes.regenerate', $inviteCode) }}" method="POST"
                                    onsubmit="return confirm('Regenerate the code? The old code will stop working immediately.');">
                                @csrf
                                <button type="submit"
                                        class="mt-1 px-3 py-2 text-xs bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    Regenerate
                                </button>
                            </form>
                        </div>
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
                            @php $selectedTagIds = old('tags', $inviteCode->tags->pluck('id')->toArray()); @endphp
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($tags as $tag)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                                {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}
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
                        <input type="number" name="max_uses" id="max_uses"
                                value="{{ old('max_uses', $inviteCode->max_uses) }}"
                                min="1" placeholder="Unlimited"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('max_uses') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $inviteCode->uses_count }} use(s) so far.</p>
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
                                value="{{ old('expires_at', $inviteCode->expires_at?->format('Y-m-d\TH:i')) }}"
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
                                    {{ old('is_active', $inviteCode->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active (can be used for registration)</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between">
                        <form action="{{ route('admin.invite-codes.destroy', $inviteCode) }}" method="POST"
                                onsubmit="return confirm('Permanently delete this invite code?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                Delete Code
                            </button>
                        </form>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('admin.invite-codes.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
