<x-app-layout>
    @section('title', 'Manage Announcements')
    <x-slot name="header">{{ __('Manage Announcements') }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('announcements.create') }}"
            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
            Create Announcement
        </a>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($announcements as $announcement)
                    <article class="p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $announcement->title }}</h2>
                                <div class="prose prose-sm mt-2 max-w-none text-gray-700 dark:prose-invert dark:text-gray-300">{!! $announcement->html_body !!}</div>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $announcement->expires_at ? ($announcement->expires_at->isPast() ? 'Expired ' : 'Expires ').$announcement->expires_at->timezone(user_timezone())->format('M j, Y g:i A') : 'No expiration' }}</span>
                                    <span aria-hidden="true">·</span>
                                    <span>
                                        @if($announcement->volunteers_only)
                                            Volunteers without departments
                                        @elseif($announcement->departments->isEmpty() && $announcement->sectors->isEmpty())
                                            Everyone
                                        @else
                                            {{ $announcement->departments->pluck('name')->merge($announcement->sectors->pluck('name')->map(fn ($name) => $name.' sector'))->join(', ') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <a href="{{ route('announcements.edit', $announcement) }}" class="text-sm text-blue-600 hover:underline dark:text-blue-400">Edit</a>
                                <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline dark:text-red-400">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="p-8 text-center text-gray-500 dark:text-gray-400">No announcements have been created.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-6">{{ $announcements->links() }}</div>
    </div>
</x-app-layout>
