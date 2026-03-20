<x-app-layout>
    <x-slot name="header">
        Org Chart
    </x-slot>

    <x-slot name="actions">
      <a href="{{route('orgchart-visual')}}"
          class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
          <x-heroicon-s-chart-pie class="w-4 inline"/> Visual Chart
      </a>
    </x-slot>

    <div
        class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto"
        x-data="{ search: '' }"
    >
        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-6 items-start sm:items-center">
            <div class="relative flex-1 max-w-xs">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                <input
                    type="text"
                    x-model.debounce.150ms="search"
                    placeholder="Search departments or members&hellip;"
                    class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-green"
                >
            </div>
            <div class="flex gap-2 text-sm">
                <button
                    @click="$dispatch('org-toggle-all', { open: true })"
                    class="px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                >
                    Expand all
                </button>
                <button
                    @click="$dispatch('org-toggle-all', { open: false })"
                    class="px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                >
                    Collapse all
                </button>
            </div>
        </div>

        @forelse ($sectors as $sector)
            @php
                $sectorUserCount = $sector->departments->sum(fn ($d) => $d->users->count());
            @endphp

            {{-- Sector block --}}
            <div
                x-data="{ open: true }"
                @org-toggle-all.window="open = $event.detail.open"
                class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden"
                x-show="
                    search === '' ||
                    '{{ strtolower($sector->name) }}'.includes(search.toLowerCase()) ||
                    @js($sector->departments->flatMap(fn ($d) => [$d->name, ...$d->users->pluck('name')->toArray()])->values()->toArray()).some(v => v.toLowerCase().includes(search.toLowerCase()))
                "
            >
                {{-- Sector header --}}
                <button
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-4 text-left bg-brand-lightbeige dark:bg-gray-700 hover:brightness-95 transition-all"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-base font-semibold text-brand-green dark:text-green-400">{{ $sector->name }}</span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-white/60 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300">
                            {{ $sector->departments->count() }} {{ Str::plural('dept', $sector->departments->count()) }}
                            &middot;
                            {{ $sectorUserCount }} {{ Str::plural('member', $sectorUserCount) }}
                        </span>
                    </div>
                    <x-heroicon-s-chevron-down
                        class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                        ::class="open ? '' : '-rotate-90'"
                    />
                </button>

                {{-- Departments --}}
                <div x-show="open" x-transition>
                    @if ($sector->departments->isEmpty())
                        <p class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400 italic">No departments in this sector.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                            @foreach ($sector->departments as $department)
                                @php
                                    $memberCount = $department->users->count();
                                    $headIds = $department->heads->pluck('id')->toArray();
                                    $heads = $department->heads;
                                    $members = $department->users->sortByDesc(fn ($u) => in_array($u->id, $headIds));
                                @endphp

                                <div
                                    x-data="{ membersOpen: false }"
                                    class="rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 overflow-hidden"
                                    x-show="
                                        search === '' ||
                                        '{{ strtolower($department->name) }}'.includes(search.toLowerCase()) ||
                                        @js($department->users->pluck('name')->values()->toArray()).some(v => v.toLowerCase().includes(search.toLowerCase()))
                                    "
                                    x-effect="if (search !== '') membersOpen = true; else membersOpen = false"
                                >
                                    {{-- Department header --}}
                                    <div class="px-4 py-3 bg-brand-brown dark:bg-gray-700 flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-white truncate">{{ $department->name }}</p>
                                            @if ($heads->isNotEmpty())
                                                <p class="text-xs text-amber-300 mt-0.5 truncate">
                                                    <x-heroicon-s-star class="w-3 h-3 inline -mt-0.5" />
                                                    {{ $heads->map(fn ($h) => $h->name)->join(', ') }}
                                                </p>
                                            @else
                                                <p class="text-xs text-white/40 mt-0.5">
                                                    <x-heroicon-o-star class="w-3 h-3 inline -mt-0.5" />
                                                    No Dept Head
                                                </p>
                                            @endif
                                        </div>
                                        <button
                                            @click="membersOpen = !membersOpen"
                                            class="flex-shrink-0 flex items-center gap-1 text-xs text-white/80 hover:text-white transition"
                                            :aria-expanded="membersOpen"
                                        >
                                            <span class="tabular-nums">{{ $memberCount }}</span>
                                            <x-heroicon-s-users class="w-3.5 h-3.5" />
                                            <x-heroicon-s-chevron-down
                                                class="w-3 h-3 transition-transform duration-150"
                                                ::class="membersOpen ? '' : '-rotate-90'"
                                            />
                                        </button>
                                    </div>

                                    {{-- Member list --}}
                                    <div x-show="membersOpen" x-transition>
                                        @if ($department->users->isEmpty())
                                            <p class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 italic">No members assigned.</p>
                                        @else
                                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                                @foreach ($members as $user)
                                                    <li
                                                        class="flex items-center gap-2 px-4 py-2"
                                                        x-show="search === '' || '{{ strtolower($user->name) }}'.includes(search.toLowerCase())"
                                                    >
                                                        @if (in_array($user->id, $headIds))
                                                            <x-heroicon-s-star class="w-3 h-3 flex-shrink-0 text-amber-400" />
                                                        @else
                                                            <x-heroicon-o-user class="w-3 h-3 flex-shrink-0 text-gray-400" />
                                                        @endif
                                                        <a
                                                            href="{{ route('users.show', $user) }}"
                                                            class="text-xs text-gray-700 dark:text-gray-200 hover:text-brand-green dark:hover:text-green-400 hover:underline truncate"
                                                        >{{ $user->name }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-building-office-2 class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">No sectors or departments have been created yet.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
