@php
    $concatBaseUrl = app_setting('concat_api_base_url');

    $watchedSectorIds = \App\Models\ConcatSectorRoleMapping::pluck('sector_id');
    $userSectorIds = $user->departments()->pluck('departments.sector_id');
    $hasQualifyingDepartment = $watchedSectorIds->intersect($userSectorIds)->isNotEmpty();
@endphp

<section x-data="{ showEmailOverride: {{ old('concat_search_email') ? 'true' : 'false' }} }">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Concat') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Link your ConCat account so staffing a department can automatically grant you roles there. Roles are required for you to get a staff badge (If applicable).
        </p>
    </header>

    @unless($hasQualifyingDepartment)
        <div class="mt-4 rounded-md bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
            <p>
                You don't currently have any departments that qualify you for a ConCat staff role.
            </p>
            <p class="mt-1">
                If you believe this is in error, please contact your convention/event's Staff Admin.
            </p>
        </div>
    @endunless

    @if($user->concat_user_id)
        <div class="mt-4 rounded-md border border-gray-200 dark:border-gray-700 px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 shrink-0" />
                    <span>{{ __('Linked') }}</span>
                </div>
                <form method="POST" action="{{ route('profile.unlink-concat') }}" onsubmit="return confirm('Unlink your ConCat account? Any roles it granted will be revoked.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        {{ __('Unlink') }}
                    </button>
                </form>
            </div>

            @if($user->concatRoleGrants->isNotEmpty())
                <ul class="mt-3 space-y-1">
                    @foreach($user->concatRoleGrants as $grant)
                        <li class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $grant->sector?->concatRoleMapping?->concat_role_name ?? $grant->concat_role_id }}
                            via {{ $grant->sector->name ?? 'Unknown Sector' }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @else
        <div class="mt-4 space-y-4">
            @if($user->concat_checked_at)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <x-heroicon-s-x-circle class="w-4 h-4 inline -mt-0.5" />
                    No ConCat account was found for {{ $user->email }}.
                </p>
            @endif

            @if($concatBaseUrl)
                <div class="rounded-md bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3 text-sm text-blue-800 dark:text-blue-200">
                    <p class="flex items-start gap-2">
                        <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                        <span>
                            Don't have a ConCat account yet? You can 
                            <a href="{{ $concatBaseUrl }}" target="_blank" rel="noopener" class="font-medium underline hover:no-underline">create one here</a>.
                        </span>
                    </p>
                    <p class="mt-2">
                        <strong>Before creating a new account</strong>, please double-check you don't already have
                        one on ConCat, and make sure you're linking the ConCat account that actually belongs to
                        you. Linking the wrong account, or creating a duplicate, can cause registration and
                        staffing issues.
                    </p>
                </div>
            @endif

            @if(session('error') && ! old('concat_search_email'))
                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('profile.link-concat') }}">
                @csrf
                <button type="submit" class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                    {{ __('Link My ConCat Account') }}
                </button>
            </form>

            <div>
                <button type="button" x-on:click="showEmailOverride = !showEmailOverride"
                    class="text-sm text-gray-500 underline decoration-gray-300 underline-offset-2 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    {{ __('My ConCat account uses a different email') }}
                </button>

                <div x-show="showEmailOverride" x-cloak class="mt-3 space-y-2">
                    @if(session('error') && old('concat_search_email'))
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ session('error') }}</p>
                    @endif

                    <form method="POST" action="{{ route('profile.link-concat') }}" class="flex flex-wrap gap-2">
                        @csrf
                        <input type="email" name="concat_search_email" value="{{ old('concat_search_email') }}" placeholder="your-other-email@example.com"
                            class="block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        <button type="submit"
                            class="shrink-0 rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-green shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-green-400 dark:ring-gray-600 dark:hover:bg-gray-600">
                            {{ __('Search & Link') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</section>
