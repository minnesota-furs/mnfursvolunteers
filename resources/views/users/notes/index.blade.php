<x-app-layout>
    @auth
        @section('title', 'Notes - ' . $user->name)
        <x-slot name="header">
            {{ __('Notes: ') }}{{ $user->name }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('users.show', $user) }}" 
                class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Back to Profile
            </a>
        </x-slot>

        <div class="py-12" x-data="{ 
            typeFilter: 'all',
            privacyFilter: 'all',
            get filteredNotes() {
                let notes = Array.from(document.querySelectorAll('.note-item'));
                return notes.filter(note => {
                    let typeMatch = this.typeFilter === 'all' || note.dataset.type === this.typeFilter;
                    let privacyMatch = this.privacyFilter === 'all' || 
                        (this.privacyFilter === 'private' && note.dataset.private === 'true') ||
                        (this.privacyFilter === 'public' && note.dataset.private === 'false');
                    
                    if (typeMatch && privacyMatch) {
                        note.style.display = '';
                    } else {
                        note.style.display = 'none';
                    }
                    return typeMatch && privacyMatch;
                }).length;
            }
        }" x-init="$watch('typeFilter', () => filteredNotes); $watch('privacyFilter', () => filteredNotes)">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Notes List --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    All Notes ({{ $notes->count() }})
                                </h3>
                            </div>
                            @if(Auth::user()->hasPermission('manage-user-notes'))
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="{{ route('users.notes.create', $user) }}"
                                    class="inline-flex items-center justify-center rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    New Note
                                </a>
                            </div>
                            @endif
                        </div>
                        
                        {{-- Filters --}}
                        <div class="mt-4 flex flex-col sm:flex-row gap-3">
                            <div class="flex-1">
                                <label for="type-filter" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Type</label>
                                <select id="type-filter" x-model="typeFilter"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                    <option value="all">All Types</option>
                                    <option value="Standard">Standard</option>
                                    <option value="Writeup">Writeup</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label for="privacy-filter" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Privacy</label>
                                <select id="privacy-filter" x-model="privacyFilter"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green text-sm">
                                    <option value="all">All Notes</option>
                                    <option value="public">Public</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        @if($notes->count() > 0)
                            <div class="space-y-6">
                                @foreach($notes as $note)
                                    <div class="note-item border border-gray-200 dark:border-gray-700 rounded-lg p-4 
                                        @if($note->type === 'Writeup') border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/10 @endif"
                                        data-type="{{ $note->type }}"
                                        data-private="{{ $note->private ? 'true' : 'false' }}">
                                        @if($note->title)
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $note->title }}</h4>
                                        @endif
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                                    @if($note->type === 'Standard')
                                                        bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 ring-blue-600/20 dark:ring-blue-400/30
                                                    @else
                                                        bg-red-50 dark:bg-red-900/50 text-red-700 dark:text-red-300 ring-red-600/20 dark:ring-red-400/30
                                                    @endif">
                                                    {{ $note->type }}
                                                </span>
                                                @if($note->private)
                                                    <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-900/50 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 ring-1 ring-inset ring-gray-500/10 dark:ring-gray-400/20">
                                                        <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                        Private
                                                    </span>
                                                @endif
                                            </div>
                                            @if($note->created_by === auth()->id() || auth()->user()->hasPermission('manage-user-notes'))
                                                <div class="flex space-x-2" x-data="{ showEdit: false, showDelete: false }">
                                                    <button @click="showEdit = !showEdit" type="button"
                                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                                        Edit
                                                    </button>
                                                    <button @click="showDelete = !showDelete" type="button"
                                                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                        Delete
                                                    </button>

                                                    {{-- Edit Form --}}
                                                    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                                        <div class="flex items-center justify-center min-h-screen px-4">
                                                            <div @click.away="showEdit = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-2xl w-full">
                                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Note</h3>
                                                                <form action="{{ route('users.notes.update', [$user, $note]) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="space-y-4">
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title (Optional)</label>
                                                                            <input type="text" name="title" value="{{ $note->title }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note Type</label>
                                                                            <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm">
                                                                                <option value="Standard" @if($note->type === 'Standard') selected @endif>Standard</option>
                                                                                <option value="Writeup" @if($note->type === 'Writeup') selected @endif>Writeup</option>
                                                                            </select>
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content</label>
                                                                            <textarea name="content" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm">{{ $note->content }}</textarea>
                                                                        </div>
                                                                        <div class="flex items-center">
                                                                            <input name="private" type="checkbox" value="1" @if($note->private) checked @endif class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-brand-green focus:ring-brand-green">
                                                                            <label class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Private</label>
                                                                        </div>
                                                                        <div class="flex justify-end space-x-2">
                                                                            <button @click="showEdit = false" type="button" class="rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">Cancel</button>
                                                                            <button type="submit" class="rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark">Update</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Delete Confirmation --}}
                                                    <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                                        <div class="flex items-center justify-center min-h-screen px-4">
                                                            <div @click.away="showDelete = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full">
                                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Note</h3>
                                                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Are you sure you want to delete this note? This action cannot be undone.</p>
                                                                <form action="{{ route('users.notes.destroy', [$user, $note]) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <div class="flex justify-end space-x-2">
                                                                        <button @click="showDelete = false" type="button" class="rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">Cancel</button>
                                                                        <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">Delete</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                            Created by <span class="font-medium text-gray-900 dark:text-white">{{ $note->creator->name }}</span>
                                            on {{ $note->created_at->format('M j, Y g:i A') }}
                                        </div>

                                        <div class="text-gray-900 dark:text-white whitespace-pre-line">
                                            {{ $note->content }}
                                        </div>

                                        {{-- Comments Section --}}
                                        @if($note->comments->count() > 0)
                                            <div class="mt-4 space-y-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Comments</h4>
                                                @foreach($note->comments as $comment)
                                                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-md p-3">
                                                        <div class="flex justify-between items-start mb-1">
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                <span class="font-medium text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                                                - {{ $comment->created_at->format('M j, Y g:i A') }}
                                                            </div>
                                                            @if($comment->user_id === auth()->id() || auth()->user()->hasPermission('manage-user-notes'))
                                                                <form action="{{ route('users.notes.comments.destroy', [$user, $note, $comment]) }}" method="POST" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" onclick="return confirm('Delete this comment?')"
                                                                        class="text-xs text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                            {{ $comment->content }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Add Comment Form --}}
                                        <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4" x-data="{ showCommentForm: false }">
                                            <button @click="showCommentForm = !showCommentForm" type="button"
                                                class="text-sm text-brand-green hover:text-brand-green-dark">
                                                Add Comment
                                            </button>
                                            <div x-show="showCommentForm" x-cloak class="mt-2" style="display: none;">
                                                <form action="{{ route('users.notes.comments.store', [$user, $note]) }}" method="POST">
                                                    @csrf
                                                    <textarea name="content" rows="2" required
                                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-green focus:ring-brand-green sm:text-sm"
                                                        placeholder="Add a comment..."></textarea>
                                                    <div class="mt-2 flex justify-end space-x-2">
                                                        <button @click="showCommentForm = false" type="button"
                                                            class="rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-xs font-semibold text-gray-900 dark:text-white shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                            class="rounded-md bg-brand-green px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-green-dark">
                                                            Post Comment
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                                No notes found for this user.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
