<x-app-layout>
    @section('title', 'Favorite & Avoid List')
    <x-slot name="header">
        {{ __('Favorite & Avoid List') }}
    </x-slot>

    <x-slot name="actions">
        <button
            type="button"
            x-data
            x-on:click="$dispatch('open-modal', 'relationship-help')"
            class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors"
        >
            <x-heroicon-o-question-mark-circle class="w-4 h-4"/>
            What is this?
        </button>
    </x-slot>

    <div class="space-y-6">

        {{-- ── Favorites ───────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <x-heroicon-s-star class="w-5 h-5 text-yellow-500"/>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Favorites</h2>
                <span class="ml-auto text-sm text-gray-500 dark:text-gray-400">{{ $favorites->count() }}</span>
            </div>

            @if($favorites->isEmpty())
                <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-star class="w-8 h-8 mx-auto mb-2 opacity-40"/>
                    <p>You haven't favorited anyone yet.</p>
                    <p class="text-sm mt-1">Visit a volunteer's profile to mark them as a favorite.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($favorites as $user)
                        <li class="px-6 py-3 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-brand-green/20 dark:bg-brand-green/30 flex items-center justify-center text-sm font-bold text-brand-green flex-shrink-0">
                                    {{ strtoupper(substr($user->displayName(), 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('users.profile.show', $user) }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-brand-green truncate block">
                                        {{ $user->displayName() }}
                                    </a>
                                    @if($user->departments->isNotEmpty())
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->departments->pluck('name')->join(', ') }}</p>
                                    @endif
                                </div>
                            </div>
                            <form action="{{ route('users.relationship.toggle', $user) }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="favorite">
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors" title="Remove favorite">
                                    <x-heroicon-o-x-mark class="w-4 h-4"/>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- ── Avoided ─────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <x-heroicon-s-hand-raised class="w-5 h-5 text-red-500"/>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Avoided</h2>
                <span class="ml-auto text-sm text-gray-500 dark:text-gray-400">{{ $avoided->count() }}</span>
            </div>

            @if($avoided->isEmpty())
                <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-hand-raised class="w-8 h-8 mx-auto mb-2 opacity-40"/>
                    <p>You haven't marked anyone to avoid.</p>
                    <p class="text-sm mt-1">Visit a volunteer's profile to mark someone as avoid.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($avoided as $user)
                        <li class="px-6 py-3 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-brand-green/20 dark:bg-brand-green/30 flex items-center justify-center text-sm font-bold text-brand-green flex-shrink-0">
                                    {{ strtoupper(substr($user->displayName(), 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('users.profile.show', $user) }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-brand-green truncate block">
                                        {{ $user->displayName() }}
                                    </a>
                                    @if($user->departments->isNotEmpty())
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->departments->pluck('name')->join(', ') }}</p>
                                    @endif
                                </div>
                            </div>
                            <form action="{{ route('users.relationship.toggle', $user) }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="avoid">
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors" title="Remove avoid">
                                    <x-heroicon-o-x-mark class="w-4 h-4"/>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</x-app-layout>

<x-modal name="relationship-help" maxWidth="lg" focusable>
    <div class="p-6">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-brand-green/10 dark:bg-brand-green/20">
                <x-heroicon-o-user-group class="h-5 w-5 text-brand-green"/>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    How Favorite & Avoid works
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    These private relationship markers help you make more informed choices when picking up shifts.
                </p>
            </div>
        </div>

        <div class="mt-6 space-y-4 text-sm text-gray-600 dark:text-gray-300">
            <div class="flex items-start gap-3">
                <x-heroicon-s-star class="mt-0.5 h-5 w-5 flex-shrink-0 text-yellow-500"/>
                <p>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">Favorite</span>
                    people you get along with. When one of them is signed up for a shift, you will see a visual indicator before you pick it up.
                </p>
            </div>

            <div class="flex items-start gap-3">
                <x-heroicon-s-hand-raised class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500"/>
                <p>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">Avoid</span>
                    people you do not work well with. You will see a private warning when one of them is on a shift you are considering.
                </p>
            </div>

            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Avoid markers are anonymous</h3>
                <p class="mt-1">
                    A person will never be notified that you marked them as avoid, and your identity will not be shared with them. Leadership can see when someone has received a high number of avoid markers so they can look into possible conduct concerns. This review is always handled anonymously.
                </p>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Close') }}
            </x-secondary-button>
        </div>
    </div>
</x-modal>
