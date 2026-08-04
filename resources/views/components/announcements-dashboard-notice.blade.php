@props(['announcements'])

@if($announcements->isNotEmpty())
    <section class="mt-5 overflow-hidden rounded-lg border-l-4 border-brand-green bg-white shadow-lg dark:bg-gray-800" aria-labelledby="dashboard-announcements-heading">
        <div class="flex items-center gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
            <x-heroicon-o-megaphone class="h-6 w-6 shrink-0 text-brand-green" />
            <div>
                <h2 id="dashboard-announcements-heading" class="text-lg font-bold text-gray-900 dark:text-gray-100">Announcements</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Important updates for you</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-px bg-gray-200 dark:bg-gray-700 md:grid-cols-2">
            @foreach($announcements as $announcement)
                <a href="{{ route('announcements.show', $announcement) }}"
                    class="group flex min-w-0 flex-col justify-between gap-4 bg-white px-5 py-4 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-gray-900 group-hover:text-brand-green dark:text-gray-100">{{ $announcement->title }}</h3>
                            <x-heroicon-m-chevron-right class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 transition group-hover:text-brand-green" />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Click to read announcement</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <span>Posted {{ $announcement->created_at->diffForHumans() }}</span>
                        @if($announcement->expires_at)
                            <span aria-hidden="true">·</span>
                            <span>
                                Until {{ $announcement->expires_at->timezone(user_timezone())->format('M j, Y g:i A') }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
