@php
    $user = Auth::user();

    $showConcatNotice = false;

    if (concat_configured() && ! $user->concat_user_id) {
        $watchedSectorIds = \App\Models\ConcatSectorRoleMapping::pluck('sector_id');
        $userSectorIds = $user->departments()->pluck('departments.sector_id');

        $showConcatNotice = $watchedSectorIds->intersect($userSectorIds)->isNotEmpty();
    }
@endphp

@if($showConcatNotice)
<div class="mt-5">
    <div class="overflow-hidden rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 border-2 border-blue-200 dark:border-blue-700 px-4 py-5 shadow-lg sm:p-6">
        <h3 class="text-xl font-bold mb-2 text-blue-800 dark:text-blue-200 flex items-center">
            <x-heroicon-o-link class="w-6 h-6 mr-2" />
            Connect Your ConCat Account
        </h3>
        <p class="text-blue-900/80 dark:text-blue-100/80 mb-4">
            A department you staff grants a role on ConCat, but you don't have a ConCat account linked yet.
            Connect it on your profile so the role can be granted automatically. These roles are important so you
            can get your staff badge (if applicable).
        </p>
        <a href="{{ route('profile.edit') }}#concat"
           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <x-heroicon-o-link class="w-4 h-4 mr-1" />
            Connect ConCat on Your Profile
        </a>
    </div>
</div>
@endif
