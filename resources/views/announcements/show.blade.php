<x-app-layout>
    @section('title', $announcement->title)
    <x-slot name="header">{{ __('Announcement') }}</x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <article class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800 sm:p-8">
            <header class="border-b border-gray-200 pb-5 dark:border-gray-700">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-megaphone class="mt-1 h-7 w-7 shrink-0 text-brand-green" />
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ $announcement->title }}</h1>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Posted {{ $announcement->created_at->timezone(user_timezone())->format('M j, Y g:i A') }}
                            @if($announcement->expires_at)
                                · Available until {{ $announcement->expires_at->timezone(user_timezone())->format('M j, Y g:i A') }}
                            @endif
                        </p>
                    </div>
                </div>
            </header>

            <div class="prose mt-6 max-w-none dark:prose-invert">{!! $announcement->html_body !!}</div>

            <div class="mt-8 border-t border-gray-200 pt-5 dark:border-gray-700">
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-brand-green hover:underline">← Back to dashboard</a>
            </div>
        </article>
    </div>
</x-app-layout>
