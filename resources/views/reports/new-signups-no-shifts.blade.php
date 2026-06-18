<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Report: New Signups Without Shifts') }}
        </x-slot>

        <x-slot name="actions"></x-slot>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Last 30 Days</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $totalLast30 }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">signed up, no shifts yet</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Last 60 Days</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $totalLast60 }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">signed up, no shifts yet</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">All Time</p>
                    <p class="text-3xl font-bold text-gray-600 dark:text-gray-300">{{ $totalAllTime }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">active volunteers, no shifts</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
                <div class="flex flex-col sm:flex-row gap-4">

                    {{-- Days filter --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Signed up within:</span>
                        @foreach ([30 => 'Last 30 Days', 60 => 'Last 60 Days', 90 => 'Last 90 Days', 0 => 'All Time'] as $value => $label)
                            <a href="{{ request()->fullUrlWithQuery(['days' => $value, 'page' => null]) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border transition
                                      {{ $days == $value
                                          ? 'bg-brand-green text-white border-brand-green'
                                          : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('report.newSignupsWithNoShifts') }}" class="ml-auto flex gap-2">
                        <input type="hidden" name="days" value="{{ $days }}">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <input type="hidden" name="direction" value="{{ $direction }}">
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Search name or email…"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-3 py-1.5 focus:ring-brand-green focus:border-brand-green w-56">
                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg bg-brand-green text-white text-sm font-medium hover:bg-green-700 transition">
                            Search
                        </button>
                        @if($search)
                            <a href="{{ route('report.newSignupsWithNoShifts', ['days' => $days]) }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Volunteers With No Shifts
                            @if($days > 0)
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(signed up in the last {{ $days }} days)</span>
                            @else
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(all time)</span>
                            @endif
                        </h2>
                        @if($search)
                            <p class="mt-0.5 text-sm text-orange-600 dark:text-orange-400">
                                <x-heroicon-s-magnifying-glass class="w-4 h-4 inline"/> Showing {{ $users->total() }} result(s) for "<span class="font-medium">{{ $search }}</span>"
                            </p>
                        @endif
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $users->total() }} total</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="py-3 pl-5 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    @php
                                        $nameDir = ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $nameDir, 'page' => null]) }}"
                                       class="flex items-center gap-1 hover:text-brand-green transition">
                                        Name
                                        @if($sort === 'name')
                                            <x-heroicon-s-chevron-{{ $direction === 'asc' ? 'up' : 'down' }} class="w-3.5 h-3.5"/>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 hidden sm:table-cell">
                                    @php
                                        $emailDir = ($sort === 'email' && $direction === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => $emailDir, 'page' => null]) }}"
                                       class="flex items-center gap-1 hover:text-brand-green transition">
                                        Email
                                        @if($sort === 'email')
                                            <x-heroicon-s-chevron-{{ $direction === 'asc' ? 'up' : 'down' }} class="w-3.5 h-3.5"/>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 hidden md:table-cell">Department</th>
                                <th class="px-3 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    @php
                                        $dateDir = ($sort === 'created_at' && $direction === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $dateDir, 'page' => null]) }}"
                                       class="flex items-center gap-1 hover:text-brand-green transition">
                                        Signed Up
                                        @if($sort === 'created_at')
                                            <x-heroicon-s-chevron-{{ $direction === 'asc' ? 'up' : 'down' }} class="w-3.5 h-3.5"/>
                                        @endif
                                    </a>
                                </th>
                                <th class="relative py-3 pl-3 pr-5 w-16"><span class="sr-only">View</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <td class="py-4 pl-5 pr-3 text-sm">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                        @if(Auth::user()->isAdmin())
                                            <div class="text-xs text-gray-400">{{ $user->first_name }} {{ $user->last_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                        @if($user->departments->count())
                                            {{ $user->departments->pluck('name')->join(', ') }}
                                        @else
                                            <span class="italic text-gray-300 dark:text-gray-500">None</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span title="{{ $user->created_at->format('M j, Y g:ia') }}">
                                            {{ $user->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="py-4 pl-3 pr-5 text-right text-sm">
                                        <a href="{{ route('users.show', $user->id) }}"
                                           class="text-brand-green hover:underline font-medium">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                        <x-heroicon-o-check-circle class="w-8 h-8 mx-auto mb-2 text-green-400"/>
                                        No volunteers found matching this criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $users->appends(request()->except('page'))->links('vendor.pagination.custom') }}
                    </div>
                @endif
            </div>

        </div>
    @endauth
</x-app-layout>
