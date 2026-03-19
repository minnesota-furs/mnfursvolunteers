<x-app-layout>
    @section('title', 'Notifications')
    <x-slot name="header">
        Notifications
    </x-slot>

    @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp

    <x-slot name="actions">
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Mark All as Read
            </button>
        </form>
        @endif
    </x-slot>

    {{-- Summary bar --}}
    @if($notifications->total() > 0)
    <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-sm text-gray-500 dark:text-gray-400">
        <span>
            {{ $notifications->total() }} {{ Str::plural('notification', $notifications->total()) }}
            @if($unreadCount > 0)
                &mdash; <span class="font-medium text-blue-600 dark:text-blue-400">{{ $unreadCount }} unread</span>
            @endif
        </span>
        <span>Showing {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }}</span>
    </div>
    @endif

    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($notifications as $notification)
        @php $isUnread = is_null($notification->read_at); @endphp
        <div class="flex items-start gap-4 px-6 py-4 transition-colors {{ $isUnread ? 'bg-blue-50/60 dark:bg-blue-900/10' : 'hover:bg-gray-50 dark:hover:bg-gray-800/40' }}">

            {{-- Status dot --}}
            <div class="mt-1 shrink-0">
                @if($isUnread)
                    <span class="block h-2.5 w-2.5 rounded-full bg-blue-500 ring-2 ring-blue-100 dark:ring-blue-900" title="Unread"></span>
                @else
                    <span class="block h-2.5 w-2.5 rounded-full bg-gray-200 dark:bg-gray-600" title="Read"></span>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold {{ $isUnread ? 'text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }}">
                    {{ $notification->data['title'] ?? 'Notification' }}
                </p>
                @if(!empty($notification->data['message']))
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $notification->data['message'] }}
                </p>
                @endif
                <div class="mt-1.5 flex items-center gap-3">
                    <span class="text-xs text-gray-400 dark:text-gray-500" title="{{ $notification->created_at->format('M j, Y g:i A') }}">
                        {{ $notification->created_at->diffForHumans() }}
                    </span>
                    @if(!empty($notification->data['url']))
                    <a href="{{ $notification->data['url'] }}" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                        View details →
                    </a>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1 shrink-0 pt-0.5">
                @if($isUnread)
                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                        title="Mark as read">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Mark read
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('notifications.mark-unread', $notification->id) }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        title="Mark as unread">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Mark unread
                    </button>
                </form>
                @endif

                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center rounded p-1 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                        title="Dismiss">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-20 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <p class="mt-3 text-sm font-medium text-gray-400 dark:text-gray-500">All caught up!</p>
            <p class="mt-1 text-xs text-gray-300 dark:text-gray-600">No notifications to show.</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
        {{ $notifications->links() }}
    </div>
    @endif
</x-app-layout>
