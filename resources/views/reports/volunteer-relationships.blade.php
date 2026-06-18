<x-app-layout>
    @section('title', 'Report: Volunteer Relationships')
    <x-slot name="header">
        Report: Volunteer Relationships
    </x-slot>

    <x-slot name="actions">
        {{-- intentionally empty --}}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 flex items-center gap-4">
                <div class="rounded-full bg-yellow-100 dark:bg-yellow-900/30 p-3">
                    <x-heroicon-s-star class="w-6 h-6 text-yellow-500"/>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalFavorites }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Favorites</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 flex items-center gap-4">
                <div class="rounded-full bg-red-100 dark:bg-red-900/30 p-3">
                    <x-heroicon-s-hand-raised class="w-6 h-6 text-red-500"/>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalAvoids }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Avoids</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 flex items-center gap-4">
                <div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3">
                    <x-heroicon-s-users class="w-6 h-6 text-blue-500"/>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $uniqueUsers }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Users with Relationships</p>
                </div>
            </div>
        </div>

        {{-- Most avoided volunteers --}}
        @if($mostAvoided->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-500"/>
                    Most Avoided Volunteers
                </h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($mostAvoided as $entry)
                        @if($entry->targetUser)
                            <div class="inline-flex items-center gap-2 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-3 py-2">
                                <a href="{{ route('users.show', $entry->targetUser->id) }}"
                                   class="text-sm font-medium text-red-700 dark:text-red-400 hover:underline">
                                    {{ $entry->targetUser->name }}
                                </a>
                                <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/40 px-2 py-0.5 text-xs font-bold text-red-700 dark:text-red-300">
                                    {{ $entry->avoid_count }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('report.volunteerRelationships') }}"
              class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                {{-- Type filter --}}
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select name="type" id="type"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        <option value="all" {{ $typeFilter === 'all' ? 'selected' : '' }}>All</option>
                        <option value="favorite" {{ $typeFilter === 'favorite' ? 'selected' : '' }}>Favorites</option>
                        <option value="avoid" {{ $typeFilter === 'avoid' ? 'selected' : '' }}>Avoids</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="flex-1 w-full sm:w-auto">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}"
                           placeholder="Search by name or email..."
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-o-funnel class="w-4 inline mr-1"/> Filter
                    </button>
                    @if($typeFilter !== 'all' || $search)
                        <a href="{{ route('report.volunteerRelationships') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:underline">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Results table --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Relationships
                        <span class="ml-2 inline-flex items-center rounded-full bg-brand-green/10 px-2.5 py-0.5 text-xs font-medium text-brand-green">
                            {{ $relationships->total() }}
                        </span>
                    </h2>
                </div>
            </div>

            @if($relationships->isEmpty())
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-user-group class="w-10 h-10 mx-auto mb-3 text-gray-300"/>
                    <p class="font-semibold">No relationships found.</p>
                    <p class="text-sm mt-1">Try adjusting your filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @php
                                    $sortLink = fn($col, $label) => route('report.volunteerRelationships', array_merge(
                                        request()->only('type', 'search'),
                                        ['sort' => $col, 'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc']
                                    ));
                                    $sortIcon = fn($col) => $sort === $col
                                        ? ($direction === 'asc' ? '↑' : '↓')
                                        : '';
                                @endphp
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortLink('user_name', 'User') }}" class="hover:text-gray-700 dark:hover:text-gray-300">
                                        User {!! $sortIcon('user_name') !!}
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortLink('type', 'Type') }}" class="hover:text-gray-700 dark:hover:text-gray-300">
                                        Type {!! $sortIcon('type') !!}
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortLink('target_name', 'Target') }}" class="hover:text-gray-700 dark:hover:text-gray-300">
                                        Target {!! $sortIcon('target_name') !!}
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                    Departments
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ $sortLink('created_at', 'Date') }}" class="hover:text-gray-700 dark:hover:text-gray-300">
                                        Date {!! $sortIcon('created_at') !!}
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($relationships as $rel)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    {{-- User --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rel->user)
                                            <a href="{{ route('users.show', $rel->user->id) }}"
                                               class="text-sm font-medium text-brand-green hover:underline">
                                                {{ $rel->user->name }}
                                            </a>
                                            <div class="text-xs text-gray-400">{{ $rel->user->email }}</div>
                                        @else
                                            <span class="text-sm text-gray-400 italic">Deleted user</span>
                                        @endif
                                    </td>

                                    {{-- Type badge --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($rel->type === 'favorite')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-400">
                                                <x-heroicon-s-star class="w-3.5 h-3.5"/>
                                                Favorite
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-1 text-xs font-medium text-red-700 dark:text-red-400">
                                                <x-heroicon-s-hand-raised class="w-3.5 h-3.5"/>
                                                Avoid
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Target --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rel->targetUser)
                                            <a href="{{ route('users.show', $rel->targetUser->id) }}"
                                               class="text-sm font-medium text-brand-green hover:underline">
                                                {{ $rel->targetUser->name }}
                                            </a>
                                            <div class="text-xs text-gray-400">{{ $rel->targetUser->email }}</div>
                                        @else
                                            <span class="text-sm text-gray-400 italic">Deleted user</span>
                                        @endif
                                    </td>

                                    {{-- Departments (of target) --}}
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        @if($rel->targetUser && $rel->targetUser->departments->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($rel->targetUser->departments as $dept)
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-600 dark:text-gray-300">
                                                        {{ $dept->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $rel->created_at->format('M j, Y') }}
                                        <div class="text-xs text-gray-400">{{ $rel->created_at->format('g:i A') }}</div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <form action="{{ route('report.volunteerRelationships.destroy', $rel) }}" method="POST"
                                              onsubmit="return confirm('Remove this relationship?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-md border border-red-300 dark:border-red-700 px-2.5 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                    title="Remove this relationship">
                                                <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $relationships->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
