@php
    $user = Auth::user();
    $canViewUsers = !app_setting('require_department_for_user_index', false)
        || $user->isAdmin()
        || $user->hasPermission('manage-users')
        || $user->departments->isNotEmpty();

    $features = collect([
        [
            'title' => 'Dashboard',
            'description' => 'Return to your dashboard',
            'keywords' => ['home', 'overview'],
            'url' => route('dashboard'),
            'visible' => true,
        ],
        [
            'title' => 'Event Assignments',
            'description' => 'Browse events and volunteer shifts',
            'keywords' => ['events', 'shifts', 'volunteer'],
            'url' => route('volunteer.events.index'),
            'visible' => feature_enabled('volunteer_events'),
        ],
        [
            'title' => 'Simple Volunteer Events',
            'description' => 'Browse standalone volunteer activities',
            'keywords' => ['simple events', 'one off events', 'check in'],
            'url' => route('simple-volunteer-events.index'),
            'visible' => feature_enabled('one_off_events'),
        ],
        [
            'title' => 'Users',
            'description' => 'Browse the volunteer directory',
            'keywords' => ['people', 'volunteers', 'members'],
            'url' => route('users.index'),
            'visible' => $canViewUsers,
        ],
        [
            'title' => 'Departments',
            'description' => 'Browse organization departments',
            'keywords' => ['teams', 'organization'],
            'url' => route('departments.index'),
            'visible' => true,
        ],
        [
            'title' => 'Sectors',
            'description' => 'Manage organization sectors',
            'keywords' => ['organization', 'departments'],
            'url' => route('sectors.index'),
            'visible' => $user->isAdmin(),
        ],
        [
            'title' => 'Fiscal Ledger',
            'description' => 'Manage fiscal periods and volunteer hours',
            'keywords' => ['ledger', 'fiscal', 'hours', 'accounting'],
            'url' => route('ledger.index'),
            'visible' => $user->isAdmin(),
        ],
        [
            'title' => 'Perks',
            'description' => 'View volunteer rewards and redemptions',
            'keywords' => ['volunteer perks', 'rewards', 'benefits'],
            'url' => route('volunteer.perks.index'),
            'visible' => feature_enabled('perk_tracking'),
        ],
        [
            'title' => 'Volunteer Event Settings',
            'description' => 'Manage events and shift assignments',
            'keywords' => ['admin events', 'event settings', 'shifts'],
            'url' => route('admin.events.index'),
            'visible' => feature_enabled('volunteer_events') && $user->can('manage-volunteer-events'),
        ],
        [
            'title' => 'Profile & Account Settings',
            'description' => 'Update your profile, password, and preferences',
            'keywords' => ['profile', 'account settings', 'settings', 'password', 'email'],
            'url' => route('profile.edit'),
            'visible' => true,
        ],
        [
            'title' => 'Application Settings',
            'description' => 'Configure application-wide settings',
            'keywords' => ['settings', 'configuration', 'admin'],
            'url' => route('settings.index'),
            'visible' => $user->isAdmin(),
        ],
    ])->where('visible')->map(function (array $feature): array {
        unset($feature['visible']);

        return $feature;
    })->values();
@endphp

<div
    x-data="commandPalette(@js($features), @js(route('command-palette.search')))"
    x-on:open-command-palette.window="open()"
    x-on:keydown.escape.window="isOpen && close()"
>
    <div
        x-cloak
        x-show="isOpen"
        x-transition.opacity
        class="fixed inset-0 z-[100] overflow-y-auto bg-gray-900/70 px-4 py-[10vh] backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="command-palette-title"
        x-on:click.self="close()"
    >
        <div class="mx-auto max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 dark:bg-gray-800 dark:ring-white/10">
            <h2 id="command-palette-title" class="sr-only">Command Palette</h2>

            <div class="flex items-center gap-3 border-b border-gray-200 px-4 dark:border-gray-700">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 shrink-0 text-gray-400" />
                <input
                    x-ref="searchInput"
                    x-model="query"
                    x-on:input="search()"
                    x-on:keydown.arrow-down.prevent="moveActive(1)"
                    x-on:keydown.arrow-up.prevent="moveActive(-1)"
                    x-on:keydown.enter.prevent="visitActive()"
                    type="search"
                    class="h-14 w-full border-0 bg-transparent p-0 text-base text-gray-900 placeholder:text-gray-400 focus:ring-0 dark:text-white"
                    placeholder="Search features or users..."
                    autocomplete="off"
                    aria-controls="command-palette-results"
                >
                <kbd class="hidden rounded border border-gray-300 px-1.5 py-0.5 text-xs text-gray-500 sm:block dark:border-gray-600 dark:text-gray-400">Esc</kbd>
            </div>

            <div x-ref="results" id="command-palette-results" class="max-h-[60vh] overflow-y-auto p-2" role="listbox">
                <template x-for="(result, index) in results" :key="`${result.type}-${result.url}`">
                    <a
                        :href="result.url"
                        :data-result-index="index"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 transition"
                        :class="activeIndex === index ? 'bg-emerald-50 dark:bg-emerald-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60'"
                        role="option"
                        :aria-selected="activeIndex === index"
                        x-on:mousemove="activeIndex = index"
                    >
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                            :class="result.type === 'user' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300'"
                        >
                            <svg x-show="result.type === 'feature'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 12h16.5m-7.5-7.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <svg x-show="result.type === 'user'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="result.title"></span>
                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="result.description"></span>
                        </span>
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400" x-text="result.type"></span>
                    </a>
                </template>

                <div x-show="isSearching" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    Searching users...
                </div>
                <div x-show="!isSearching && results.length === 0" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    No matching features or users found.
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 px-4 py-2 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <span>Features are shown before user results</span>
                <span class="hidden sm:block">↑↓ Navigate · Enter Open</span>
            </div>
        </div>
    </div>
</div>
