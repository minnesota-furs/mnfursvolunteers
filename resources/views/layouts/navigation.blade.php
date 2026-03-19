<nav x-data="{ open: false }" class="border-brand-green print:hidden">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ app_logo() }}" alt="{{ app_name() }}" class="block h-12 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Users') }}
                    </x-nav-link>
                    @feature('job_listings')
                    <x-nav-link :href="route('job-listings.index')" :active="request()->routeIs('job-listings.*')">
                        {{ __('Open Positons') }}
                    </x-nav-link>
                    @endfeature
                    @feature('one_off_events')
                    <x-nav-link :href="route('one-off-events.index')" :active="request()->routeIs('one-off-events.*')">
                        {{ __('One-Off Event') }}
                    </x-nav-link>
                    @endfeature
                    @feature('volunteer_events')
                    <x-nav-link :href="route('volunteer.events.index')" :active="request()->routeIs('volunteer.events.*')">
                        {{ __('Events') }}
                    </x-nav-link>
                    @endfeature
                    @feature('perk_tracking')
                    <x-nav-link :href="route('volunteer.perks.index')" :active="request()->routeIs('volunteer.perks.*')">
                        {{ __('Perks') }}
                    </x-nav-link>
                    @endfeature

                    @php
                        $activeElections = \App\Models\Election::where('active', true)
                            ->where(function($query) {
                                // Show if voting is active
                                $query->where(function($q) {
                                    $q->where('start_date', '<=', now())
                                      ->where('end_date', '>=', now());
                                })
                                // OR if nominations are active
                                ->orWhere(function($q) {
                                    $q->where('nomination_start_date', '<=', now())
                                      ->where('nomination_end_date', '>=', now());
                                });
                            })
                            ->exists();
                    @endphp

                    @feature('elections')
                    @if($activeElections)
                        <x-nav-link :href="route('elections.index')" :active="request()->routeIs('elections.*')">
                            {{ __('Elections') }}
                        </x-nav-link>
                    @endif
                    @endfeature
                    
                    {{-- <x-nav-link :href="route('orgchart')" :active="request()->routeIs('orgchart')">
                        {{ __('Org Chart') }} (Test)
                    </x-nav-link> --}}

                    @can('view-reports')
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex mt-5 pb-5 items-center px-1 pt-1 border-b-2 border-transparent hover:underline text-sm font-medium leading-5 text-gray-100 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-300 hover:border-white/25 dark:hover:border-gray-700 focus:outline-none focus:text-gray-100 dark:focus:text-gray-300 focus:border-gray-100 dark:focus:border-gray-700 transition duration-150 ease-in-out">
                                <div>Reports</div>
                            </button>
                        </x-slot>

                        <x-slot name="content" class="-mt-32">
                            {{-- <x-dropdown-link :href="route('departments.index')">
                                {{ __('Recently Logged Hours') }}
                            </x-dropdown-link> --}}
                            <x-dropdown-link :href="route('report.usersWithoutDepartments')">
                                {{ __('Users without Dept') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('report.usersWithoutHoursThisPeriod')">
                                {{ __('Users With Zero Hours') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('report.eventShiftHours')">
                                {{ __('Event Shift Hours') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    @endcan

                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex mt-5 pb-5 items-center px-1 pt-1 border-b-2 border-transparent hover:underline text-sm font-medium leading-5 text-gray-100 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-300 hover:border-white/25 dark:hover:border-gray-700 focus:outline-none focus:text-gray-100 dark:focus:text-gray-300 focus:border-gray-100 dark:focus:border-gray-700 transition duration-150 ease-in-out">
                                <div>Settings</div>
                            </button>
                        </x-slot>

                        <x-slot name="content" class="-mt-32">
                            @if( Auth::check() && Auth::user()->isAdmin() )
                            <x-dropdown-link :href="route('settings.index')">
                                {{ __('Application Settings') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.invite-codes.index')">
                                {{ __('Invite Codes') }}
                            </x-dropdown-link>
                            <div class="border-t border-gray-200 dark:border-gray-600"></div>
                            @endif
                            
                            @feature('volunteer_events')
                            @can('manage-volunteer-events')
                            {{-- <x-dropdown-link :href="route('admin.manager-dashboard')">
                                <x-heroicon-s-signal class="w-3.5 h-3.5 inline text-green-500 mr-1"/>{{ __('Manager Dashboard') }}
                            </x-dropdown-link> --}}
                            <x-dropdown-link :href="route('admin.events.index')">
                                {{ __('Volunteer Events') }}
                            </x-dropdown-link>
                            @endcan
                            @endfeature
                            @feature('perk_tracking')
                            @can('manage-volunteer-events')
                            <x-dropdown-link :href="route('admin.perk-sets.index')">
                                {{ __('Perk Sets') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.perks.index')">
                                {{ __('Volunteer Perks') }}
                            </x-dropdown-link>
                            @endcan
                            @endfeature

                            @feature('elections')
                            @can('manage-elections')
                            <x-dropdown-link :href="route('admin.elections.index')">
                                {{ __('Elections') }}
                            </x-dropdown-link>
                            @endcan
                            @endfeature

                            @feature('one_off_events')
                            <x-dropdown-link :href="route('one-off-events.index')">
                                {{ __('One Off Events') }}
                            </x-dropdown-link>
                            @endfeature

                            @if( Auth::check() && Auth::user()->isAdmin() )
                            <x-dropdown-link :href="route('ledger.index')">
                                {{ __('Ledgers') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('sectors.index')">
                                {{ __('Sectors') }}
                            </x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('departments.index')">
                                {{ __('Departments') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="flex flex-1 justify-center items-center px-2 lg:ml-6 lg:justify-end">
                <div class="max-w-lg lg:max-w-xs">
                  <label for="search" class="sr-only">Search</label>
                  <form class="relative text-white focus-within:text-white" action="{{ route('users.index') }}" method="GET">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <input id="search" class="block w-full rounded-md border-0  bg-white/5 py-1.5 pl-10 pr-3 text-white focus:ring-2 focus:ring-white focus:ring-offset-white sm:text-sm sm:leading-6 placeholder-white/25" placeholder="Search Users" type="search" name="search" value="{{ request('search') }}">
                  </form>
                </div>
            </div>

            <!-- Settings Dropdown -->
            @auth
            <div class="hidden sm:flex sm:items-center gap-2">
                <!-- Notifications Bell -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                        class="relative inline-flex items-center p-2 rounded-full text-white hover:bg-white/10 focus:outline-none transition ease-in-out duration-150"
                        aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if($unreadNotificationsCount > 0)
                        <span class="absolute top-0.5 right-0.5 inline-flex items-center justify-center min-w-[1rem] h-4 px-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                        </span>
                        @endif
                    </button>

                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute end-0 z-50 mt-2 w-80 rounded-md shadow-lg"
                        style="display: none;">
                        <div class="rounded-md ring-1 ring-black ring-opacity-5 bg-white dark:bg-gray-700">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                                @if($unreadNotificationsCount > 0)
                                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Mark all as read</button>
                                </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse($recentNotifications as $notification)
                                <div class="flex items-start gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-600 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-600 {{ is_null($notification->read_at) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                        @if(!empty($notification->data['message']))
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $notification->data['message'] }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if(is_null($notification->read_at))
                                    <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="shrink-0">
                                        @csrf
                                        <button type="submit" class="text-blue-500 hover:text-blue-700 dark:hover:text-blue-400" title="Mark as read">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                @empty
                                <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No notifications
                                </div>
                                @endforelse
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-600 px-4 py-2 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View all notifications</a>
                            </div>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white dark:text-gray-400 bg-white/5 dark:bg-gray-800 hover:text-blue-200 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('users.show', Auth::user()->id)">
                            {{ __('My Volunteer Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Account Settings') }}
                        </x-dropdown-link>

                        <x-dropdown-link href="/">
                            {{ __('Public Site') }}
                        </x-dropdown-link>

                        <x-dropdown-link href="#" 
                                onclick="window.themeController.toggleTheme();">
                            {{ __('Light/Dark Mode') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-200 dark:border-gray-600"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @endauth

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white dark:text-gray-500 hover:text-brand-green dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-brand-green dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <!-- Mobile Search -->
        <div class="pt-2 pb-3 px-4">
            <form class="relative text-white focus-within:text-white" action="{{ route('users.index') }}" method="GET">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input id="mobile-search" class="block w-full rounded-md border-0 bg-white/5 py-1.5 pl-10 pr-3 text-white focus:ring-2 focus:ring-white focus:ring-offset-white sm:text-sm sm:leading-6 placeholder-white/25" placeholder="Search Users" type="search" name="search" value="{{ request('search') }}">
            </form>
        </div>

        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Users') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('job-listings.index')" :active="request()->routeIs('job-listings.*')">
                {{ __('Open Positons') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('volunteer.events.index')" :active="request()->routeIs('volunteer.events.*')">
                {{ __('Events') }}
            </x-responsive-nav-link>
            
            @php
                $activeElections = \App\Models\Election::where('active', true)
                    ->where(function($query) {
                        // Show if voting is active
                        $query->where(function($q) {
                            $q->where('start_date', '<=', now())
                              ->where('end_date', '>=', now());
                        })
                        // OR if nominations are active
                        ->orWhere(function($q) {
                            $q->where('nomination_start_date', '<=', now())
                              ->where('nomination_end_date', '>=', now());
                        });
                    })
                    ->exists();
            @endphp
            @if($activeElections)
                <x-responsive-nav-link :href="route('elections.index')" :active="request()->routeIs('elections.*')">
                    {{ __('Elections') }}
                </x-responsive-nav-link>
            @endif

            @can('view-reports')
            <!-- Reports Section -->
            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2">
                    <div class="font-medium text-base text-white dark:text-gray-200">Reports</div>
                </div>
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('report.usersWithoutDepartments')">
                        {{ __('Users without Dept') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.usersWithoutHoursThisPeriod')">
                        {{ __('Users With Zero Hours') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.eventShiftHours')">
                        {{ __('Event Shift Hours') }}
                    </x-responsive-nav-link>
                </div>
            </div>
            @endcan

            <!-- Settings Section -->
            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2">
                    <div class="font-medium text-base text-white dark:text-gray-200">Settings</div>
                </div>
                <div class="space-y-1">
                    @if( Auth::check() && Auth::user()->isAdmin() )
                    <x-responsive-nav-link :href="route('settings.index')">
                        {{ __('Application Settings') }}
                    </x-responsive-nav-link>
                    @endif

                    @can('manage-volunteer-events')
                    <x-responsive-nav-link :href="route('admin.events.index')">
                        {{ __('Volunteer Events') }}
                    </x-responsive-nav-link>
                    @endcan

                    @can('manage-elections')
                    <x-responsive-nav-link :href="route('admin.elections.index')">
                        {{ __('Elections') }}
                    </x-responsive-nav-link>
                    @endcan

                    <x-responsive-nav-link :href="route('one-off-events.index')">
                        {{ __('One Off Events') }}
                    </x-responsive-nav-link>
                    
                    @if( Auth::user()->isAdmin() )
                    <x-responsive-nav-link :href="route('ledger.index')">
                        {{ __('Ledgers') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('sectors.index')">
                        {{ __('Sectors') }}
                    </x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link :href="route('departments.index')">
                        {{ __('Departments') }}
                    </x-responsive-nav-link>
                    @if( Auth::user()->isAdmin() )
                    <x-responsive-nav-link :href="route('admin.custom-fields.index')">
                        {{ __('User Custom Fields') }}
                    </x-responsive-nav-link>
                    @endif
                </div>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        @auth
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-bold text-base text-white dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-light text-sm text-gray-300">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    {{ __('Notifications') }}
                    @if($unreadNotificationsCount > 0)
                    <span class="ml-1 inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.show', Auth::user()->id)">
                    {{ __('My Volunteer Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Account Settings') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="#" 
                        onclick="window.themeController.toggleTheme();">
                    {{ __('Light/Dark Mode') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
