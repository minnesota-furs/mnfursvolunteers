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
                    @if(!app_setting('require_department_for_user_index', false) || Auth::user()->isAdmin() || Auth::user()->hasPermission('manage-users') || Auth::user()->departments->isNotEmpty())
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Users') }}
                    </x-nav-link>
                    @endif
                    @feature('job_listings')
                    <x-nav-link :href="route('job-listings.index')" :active="request()->routeIs('job-listings.*')">
                        {{ __('Staff Openings') }}
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
                        $volunteerActive = request()->routeIs('volunteer.events.*')
                            || request()->routeIs('simple-volunteer-events.*')
                            || request()->routeIs('volunteer.perks.*')
                            || request()->routeIs('elections.*');
                    @endphp

                    @php
                        $primaryColor = app_setting('primary_color', '#10b981');
                        [$pr, $pg, $pb] = sscanf(ltrim($primaryColor, '#'), "%02x%02x%02x");
                        $primaryBg10  = "rgba({$pr},{$pg},{$pb},0.1)";
                        $primaryBg20  = "rgba({$pr},{$pg},{$pb},0.2)";
                    @endphp

                    <!-- Volunteer flyout menu -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                        <button id="tour-volunteer-menu-trigger" @click="open = !open"
                            class="inline-flex mt-5 pb-5 items-center gap-1 px-1 pt-1 border-b-2 {{ $volunteerActive ? 'border-white/50' : 'border-transparent' }} text-sm font-medium leading-5 text-gray-100 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-300 hover:border-white/25 dark:hover:border-gray-700 focus:outline-none transition duration-150 ease-in-out">
                            Volunteer
                            <svg class="h-4 w-4 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            @click="open = false"
                            class="absolute start-0 z-50 mt-2 w-72 rounded-xl shadow-lg ring-1 ring-black/10 bg-white dark:bg-gray-800"
                            style="display: none;">
                            <div class="p-2 space-y-0.5">
                                @feature('volunteer_events')
                                <a href="{{ route('volunteer.events.index') }}" data-tour="tour-events-link"
                                    class="group flex items-start gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 {{ request()->routeIs('volunteer.events.*') ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md transition duration-150"
                                        style="background-color: {{ $primaryBg10 }}; color: {{ $primaryColor }};"
                                        x-on:mouseenter="$el.style.backgroundColor='{{ $primaryBg20 }}'"
                                        x-on:mouseleave="$el.style.backgroundColor='{{ $primaryBg10 }}'">
                                        <x-heroicon-o-calendar class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Event Assignments</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Sign up for volunteer assignments at upcoming conventions or events</p>
                                    </div>
                                </a>
                                @endfeature
                                @feature('one_off_events')
                                <a href="{{ route('simple-volunteer-events.index') }}"
                                    class="group flex items-start gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 {{ request()->routeIs('simple-volunteer-events.*') ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md transition duration-150"
                                        style="background-color: {{ $primaryBg10 }}; color: {{ $primaryColor }};"
                                        x-on:mouseenter="$el.style.backgroundColor='{{ $primaryBg20 }}'"
                                        x-on:mouseleave="$el.style.backgroundColor='{{ $primaryBg10 }}'">
                                        <x-heroicon-o-check class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Simple Volunteer Events</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Check in to standalone activities like meetings or training</p>
                                    </div>
                                </a>
                                @endfeature
                                @feature('perk_tracking')
                                <a href="{{ route('volunteer.perks.index') }}"
                                    class="group flex items-start gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 {{ request()->routeIs('volunteer.perks.*') ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md transition duration-150"
                                        style="background-color: {{ $primaryBg10 }}; color: {{ $primaryColor }};"
                                        x-on:mouseenter="$el.style.backgroundColor='{{ $primaryBg20 }}'"
                                        x-on:mouseleave="$el.style.backgroundColor='{{ $primaryBg10 }}'">
                                        <x-heroicon-o-gift class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Perks</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">View and redeem your earned volunteer rewards</p>
                                    </div>
                                </a>
                                @endfeature
                                @feature('elections')
                                @if($activeElections)
                                <a href="{{ route('elections.index') }}"
                                    class="group flex items-start gap-3 rounded-lg px-3 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 {{ request()->routeIs('elections.*') ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md transition duration-150"
                                        style="background-color: {{ $primaryBg10 }}; color: {{ $primaryColor }};"
                                        x-on:mouseenter="$el.style.backgroundColor='{{ $primaryBg20 }}'"
                                        x-on:mouseleave="$el.style.backgroundColor='{{ $primaryBg10 }}'">
                                        <x-heroicon-o-bookmark class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Elections</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Nominate candidates and cast your vote</p>
                                    </div>
                                </a>
                                @endif
                                @endfeature
                            </div>
                        </div>
                    </div>

                    @can('view-reports')
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                        <div @click="open = !open">
                            <button class="inline-flex mt-5 pb-5 items-center px-1 pt-1 border-b-2 border-transparent hover:underline text-sm font-medium leading-5 text-gray-100 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-300 hover:border-white/25 dark:hover:border-gray-700 focus:outline-none focus:text-gray-100 dark:focus:text-gray-300 focus:border-gray-100 dark:focus:border-gray-700 transition duration-150 ease-in-out">
                                Reports
                                <svg class="ml-1 w-3.5 h-3.5 transition-transform duration-150" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute z-50 mt-2 w-[680px] rounded-xl shadow-xl ltr:origin-top-left start-0"
                             style="display: none;"
                             @click="open = false">
                            <div class="rounded-xl ring-1 ring-black ring-opacity-5 bg-white dark:bg-gray-700 overflow-hidden">
                                <div class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-gray-600">
                                    {{-- Users column --}}
                                    <div class="p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-400 mb-2 px-2">Users</p>
                                        <a href="{{ route('report.usersWithoutDepartments') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-user-minus class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Without Department
                                        </a>
                                        <a href="{{ route('report.usersWithoutHoursThisPeriod') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-clock class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Zero Hours This Period
                                        </a>
                                        <a href="{{ route('report.volunteerRelationships') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-heart class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Volunteer Relationships
                                        </a>
                                        <a href="{{ route('report.newSignupsWithNoShifts') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-user-plus class="w-4 h-4 shrink-0 text-gray-400"/>
                                            New Signups Without Shifts
                                        </a>
                                    </div>
                                    {{-- Shifts column --}}
                                    <div class="p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-400 mb-2 px-2">Shifts</p>
                                        <a href="{{ route('report.eventShiftHours') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-calendar class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Event Shift Hours
                                        </a>
                                        <a href="{{ route('report.noShows') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-x-circle class="w-4 h-4 shrink-0 text-gray-400"/>
                                            No-Shows
                                        </a>
                                    </div>
                                    {{-- Other column --}}
                                    <div class="p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-400 mb-2 px-2">Other</p>
                                        <a href="{{ route('report.departmentsWithoutHead') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-building-office class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Departments Without Head
                                        </a>
                                        <a href="{{ route('report.customFields') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-adjustments-horizontal class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Custom Fields
                                        </a>
                                        <a href="{{ route('report.volunteersWithMultipleDepartments') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-user-group class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Multiple Departments
                                        </a>
                                        <a href="{{ route('report.departmentMembership') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                            <x-heroicon-o-chart-bar class="w-4 h-4 shrink-0 text-gray-400"/>
                                            Department Membership
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endcan

                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button id="tour-settings-trigger" class="inline-flex mt-5 pb-5 items-center px-1 pt-1 border-b-2 border-transparent hover:underline text-sm font-medium leading-5 text-gray-100 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-300 hover:border-white/25 dark:hover:border-gray-700 focus:outline-none focus:text-gray-100 dark:focus:text-gray-300 focus:border-gray-100 dark:focus:border-gray-700 transition duration-150 ease-in-out">
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
                            <x-dropdown-link :href="route('admin.events.index')" data-tour="tour-volunteer-events-link">
                                {{ __('Volunteer Events') }}
                            </x-dropdown-link>
                            @endcan
                            @endfeature
                            @feature('perk_tracking')
                            @can('manage-volunteer-events')
                            <x-dropdown-link :href="route('admin.perks.index')">
                                {{ __('Volunteer Perks') }}
                            </x-dropdown-link>
                            {{-- <x-dropdown-link :href="route('admin.perk-sets.index')">
                                {{ __('Volunteer Perk Sets') }}
                            </x-dropdown-link> --}}
                            @endcan
                            @endfeature

                            @feature('elections')
                            @can('manage-elections')
                            <x-dropdown-link :href="route('admin.elections.index')">
                                {{ __('Elections') }}
                            </x-dropdown-link>
                            @endcan
                            @endfeature

                            @can('manage-announcements')
                            <x-dropdown-link :href="route('announcements.index')">
                                {{ __('Announcements') }}
                            </x-dropdown-link>
                            @endcan

                            @if( Auth::check() && Auth::user()->isAdmin() )
                            <x-dropdown-link :href="route('ledger.index')" data-tour="tour-ledgers-link">
                                {{ __('Ledgers') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('sectors.index')">
                                {{ __('Sectors') }}
                            </x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('departments.index')">
                                {{ __('Departments') }}
                            </x-dropdown-link>
                            @if( Auth::check() && Auth::user()->isAdmin() )
                            <div class="border-t border-gray-200 dark:border-gray-600"></div>
                            <x-dropdown-link href="#" onclick="event.preventDefault(); window.MNFTour && window.MNFTour.start('admin-setup');">
                                <x-heroicon-o-map class="w-3.5 h-3.5 inline text-brand-green mr-1"/>{{ __('Start Guided Tour') }}
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            @if(!app_setting('require_department_for_user_index', false) || Auth::user()->isAdmin() || Auth::user()->hasPermission('manage-users') || Auth::user()->departments->isNotEmpty())
            <div class="sm:flex justify-center items-center px-2 lg:ml-6 lg:justify-end">
                <div class="max-w-lg lg:max-w-xs">
                  <label for="search" class="sr-only">Search</label>
                  <form class="relative text-white focus-within:text-white" action="{{ route('users.index') }}" method="GET">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <input id="search" class="block w-full rounded-md border-0 bg-white/5 py-1.5 pl-10 pr-3 text-white focus:ring-2 focus:ring-white focus:ring-offset-white sm:text-sm sm:leading-6 placeholder-white/25" placeholder="Search Users" type="search" name="search" value="{{ request('search') }}">
                  </form>
                </div>
            </div>
            @endif

            <!-- Settings Dropdown -->
            @auth
            <div class="hidden sm:flex sm:items-center gap-2">
                @if(Auth::user()->vol_code)
                <!-- Volunteer QR Code -->
                <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'volunteer-qr-code')"
                    class="relative inline-flex items-center p-2 rounded-full text-white hover:bg-white/10 focus:outline-none transition ease-in-out duration-150"
                    aria-label="Show my volunteer QR code">
                    <x-heroicon-o-qr-code class="h-5 w-5" />
                    {{-- <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                    </svg> --}}
                </button>
                @endif

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

                        @if($managedDepartments->isNotEmpty())
                            <div class="relative" x-data="{ departmentMenuOpen: false }"
                                x-on:mouseenter="departmentMenuOpen = true"
                                x-on:mouseleave="departmentMenuOpen = false">
                                <button type="button"
                                    x-on:click.stop="departmentMenuOpen = ! departmentMenuOpen"
                                    x-on:keydown.right.prevent="departmentMenuOpen = true"
                                    x-on:keydown.left.prevent="departmentMenuOpen = false"
                                    class="flex w-full items-center justify-between px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                                    aria-haspopup="true"
                                    x-bind:aria-expanded="departmentMenuOpen">
                                    <span>{{ __('Manage Department') }}</span>
                                    <x-heroicon-s-chevron-right class="h-4 w-4" />
                                </button>

                                <div x-show="departmentMenuOpen"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 translate-x-1"
                                    x-transition:enter-end="opacity-100 translate-x-0"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 translate-x-0"
                                    x-transition:leave-end="opacity-0 translate-x-1"
                                    class="absolute left-full top-0 z-50 w-56 rounded-r-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-700"
                                    style="display: none;"
                                    x-on:click.stop>
                                    @foreach($managedDepartments as $managedDepartment)
                                        <x-dropdown-link :href="route('departments.manage', $managedDepartment)">
                                            {{ $managedDepartment->name }}
                                        </x-dropdown-link>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @feature('volunteer_relationships')
                        <x-dropdown-link :href="route('relationships.index')">
                            {{ __('Favorite & Avoid List') }}
                        </x-dropdown-link>
                        @endfeature

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Account Settings') }}
                        </x-dropdown-link>

                        <x-dropdown-link href="/">
                            {{ __('Public Site') }}
                        </x-dropdown-link>

                        <x-dropdown-link href="#" 
                                onclick="window.themeController.toggleTheme();">
                            {{ __('Light/Dark Mode') }} 
                            <span class="text-xs p-1 font-medium rounded-md bg-purple-100 text-purple-800 dark:bg-yellow-900/30 dark:text-yellow-400">BETA</span>
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
                <button id="tour-mobile-menu-trigger" @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white dark:text-gray-500 hover:text-brand-green dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-brand-green dark:focus:text-gray-400 transition duration-150 ease-in-out">
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
        @if(!app_setting('require_department_for_user_index', false) || Auth::user()->isAdmin() || Auth::user()->hasPermission('manage-users') || Auth::user()->departments->isNotEmpty())
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
        @endif

        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(!app_setting('require_department_for_user_index', false) || Auth::user()->isAdmin() || Auth::user()->hasPermission('manage-users') || Auth::user()->departments->isNotEmpty())
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Users') }}
            </x-responsive-nav-link>
            @endif
            @feature('job_listings')
            <x-responsive-nav-link :href="route('job-listings.index')" :active="request()->routeIs('job-listings.*')">
                {{ __('Open Positions') }}
            </x-responsive-nav-link>
            @endfeature

            <!-- Volunteer Section -->
            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2">
                    <div class="font-medium text-base text-white dark:text-gray-200">Volunteer</div>
                </div>
                <div class="space-y-1">
                    @feature('volunteer_events')
                    <x-responsive-nav-link :href="route('volunteer.events.index')" :active="request()->routeIs('volunteer.events.*')" data-tour="tour-events-link">
                        {{ __('Events') }}
                    </x-responsive-nav-link>
                    @endfeature
                    @feature('one_off_events')
                    <x-responsive-nav-link :href="route('simple-volunteer-events.index')" :active="request()->routeIs('simple-volunteer-events.*')">
                        {{ __('Simple Volunteer Events') }}
                    </x-responsive-nav-link>
                    @endfeature
                    @feature('perk_tracking')
                    <x-responsive-nav-link :href="route('volunteer.perks.index')" :active="request()->routeIs('volunteer.perks.*')">
                        {{ __('Perks') }}
                    </x-responsive-nav-link>
                    @endfeature
                    @feature('elections')
                    @if($activeElections)
                    <x-responsive-nav-link :href="route('elections.index')" :active="request()->routeIs('elections.*')">
                        {{ __('Elections') }}
                    </x-responsive-nav-link>
                    @endif
                    @endfeature
                </div>
            </div>

            @can('view-reports')
            <!-- Reports Section -->
            <div class="pt-2 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4 py-2">
                    <div class="font-medium text-base text-white dark:text-gray-200">Reports</div>
                </div>
                <div class="space-y-1">
                    <div class="px-4 pt-2 pb-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Users</p>
                    </div>
                    <x-responsive-nav-link :href="route('report.usersWithoutDepartments')">
                        {{ __('Without Department') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.usersWithoutHoursThisPeriod')">
                        {{ __('Zero Hours This Period') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.volunteerRelationships')">
                        {{ __('Volunteer Relationships') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.newSignupsWithNoShifts')">
                        {{ __('New Signups Without Shifts') }}
                    </x-responsive-nav-link>
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Shifts</p>
                    </div>
                    <x-responsive-nav-link :href="route('report.eventShiftHours')">
                        {{ __('Event Shift Hours') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.noShows')">
                        {{ __('No-Shows') }}
                    </x-responsive-nav-link>
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Other</p>
                    </div>
                    <x-responsive-nav-link :href="route('report.departmentsWithoutHead')">
                        {{ __('Departments Without Head') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.customFields')">
                        {{ __('Custom Fields') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.volunteersWithMultipleDepartments')">
                        {{ __('Multiple Departments') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('report.departmentMembership')">
                        {{ __('Department Membership') }}
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
                    <x-responsive-nav-link :href="route('admin.events.index')" data-tour="tour-volunteer-events-link">
                        {{ __('Volunteer Events') }}
                    </x-responsive-nav-link>
                    @endcan

                    @can('manage-elections')
                    <x-responsive-nav-link :href="route('admin.elections.index')">
                        {{ __('Elections') }}
                    </x-responsive-nav-link>
                    @endcan

                    @can('manage-announcements')
                    <x-responsive-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">
                        {{ __('Announcements') }}
                    </x-responsive-nav-link>
                    @endcan

                    @if( Auth::user()->isAdmin() )
                    <x-responsive-nav-link :href="route('ledger.index')" data-tour="tour-ledgers-link">
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
                    <x-responsive-nav-link href="#" onclick="event.preventDefault(); window.MNFTour && window.MNFTour.start('admin-setup');">
                        {{ __('Start Guided Tour') }}
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
                @if(Auth::user()->vol_code)
                <x-responsive-nav-link href="#" x-data="" x-on:click.prevent="$dispatch('open-modal', 'volunteer-qr-code')">
                    <x-heroicon-o-qr-code class="h-5 w-5 inline" />
                    {{ __('My Volunteer QR Code') }}
                </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    {{ __('Notifications') }}
                    @if($unreadNotificationsCount > 0)
                    <span class="ml-1 inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.show', Auth::user()->id)">
                    {{ __('My Volunteer Profile') }}
                </x-responsive-nav-link>

                @if($managedDepartments->isNotEmpty())
                    <div x-data="{ departmentMenuOpen: false }">
                        <button type="button"
                            x-on:click="departmentMenuOpen = ! departmentMenuOpen"
                            class="flex w-full items-center justify-between border-l-4 border-transparent py-2 pl-3 pr-4 text-left text-base font-medium text-gray-100 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-100">
                            <span>{{ __('Manage Department') }}</span>
                            <x-heroicon-s-chevron-right class="h-4 w-4 transition-transform" x-bind:class="{ 'rotate-90': departmentMenuOpen }" />
                        </button>
                        <div x-show="departmentMenuOpen" class="space-y-1 bg-white/5 py-1" style="display: none;">
                            @foreach($managedDepartments as $managedDepartment)
                                <x-responsive-nav-link :href="route('departments.manage', $managedDepartment)" class="pl-8">
                                    {{ $managedDepartment->name }}
                                </x-responsive-nav-link>
                            @endforeach
                        </div>
                    </div>
                @endif

                @feature('volunteer_relationships')
                <x-responsive-nav-link :href="route('relationships.index')">
                    {{ __('Favorite & Avoid List') }}
                </x-responsive-nav-link>
                @endfeature

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Account Settings') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="#" 
                        onclick="window.themeController.toggleTheme();">
                    {{ __('Light/Dark Mode') }}
                    <span class="text-xs p-1 font-medium rounded-md bg-purple-100 text-purple-800 dark:bg-yellow-900/30 dark:text-yellow-400">BETA</span>
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

@auth
@if(Auth::user()->vol_code)
<x-modal name="volunteer-qr-code" maxWidth="sm" focusable>
    <div class="p-6 text-center"
        x-data="{
            render() {
                QRCode.toCanvas(this.$refs.qrCanvas, '{{ Auth::user()->vol_code }}', { width: 240, margin: 2 });
            }
        }"
        x-init="render()">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('My Volunteer QR Code') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Show this to staff to quickly look up your volunteer profile.') }}
        </p>

        <div class="mt-4 flex justify-center">
            <canvas x-ref="qrCanvas"></canvas>
        </div>

        <div class="mt-2 font-mono text-xl tracking-widest text-gray-900 dark:text-gray-100">
            {{ Auth::user()->vol_code }}
        </div>

        <div class="mt-6 flex justify-center">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Close') }}
            </x-secondary-button>
        </div>
    </div>
</x-modal>
@endif
@endauth
