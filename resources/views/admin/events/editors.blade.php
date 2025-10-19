<x-app-layout>
    @section('title', 'Manage Event Editors - ' . $event->name)
    <x-slot name="header">
        {{ __('Manage Event Editors') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ url()->previous() }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Event Creator Info -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">Event Creator</h3>
                <div class="flex items-center">
                    <x-heroicon-o-user class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2"/>
                    <span class="text-sm text-blue-800 dark:text-blue-200">
                        {{ $event->creator->name ?? 'Unknown' }} 
                        <span class="text-xs text-blue-600 dark:text-blue-400">(Full Access)</span>
                    </span>
                </div>
            </div>

            <!-- Add New Editor -->
            <div class="mb-8 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Add Editor</h3>
                <form method="POST" action="{{ route('admin.events.editors.add', $event) }}" class="flex gap-4">
                    @csrf
                    <div class="flex-1">
                        <select name="user_id" id="user_id" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <option value="">Select a user...</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-s-plus class="w-4 h-4 mr-1"/> Add Editor
                    </button>
                </form>
            </div>

            <!-- Current Editors List -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Current Editors</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        These users can edit event details and manage shifts
                    </p>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($editors as $editor)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <x-heroicon-o-user-circle class="w-8 h-8 text-gray-400 mr-3"/>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $editor->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $editor->email }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        Added {{ $editor->pivot->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.events.editors.remove', [$event, $editor]) }}" 
                                  onsubmit="return confirm('Remove {{ $editor->name }} as an editor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                    <x-heroicon-s-trash class="w-4 h-4 mr-1"/> Remove
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400"/>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No editors assigned</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Add users above to grant them edit permissions for this event.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
