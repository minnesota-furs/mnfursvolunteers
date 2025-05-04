<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Link MNFurs.org Account') }}
        </h2>

        {{-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p> --}}
    </header>

    @if(Auth::user()->wordpress_id)
        <div class="text-green-600 mb-2">✅ Linked to WordPress ID: {{ Auth::user()->wordpress_id }}</div>
        <form method="POST" action="{{ route('profile.unlink-wordpress') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Unlink WordPress Account</button>
        </form>
    @else
        <form method="POST" action="{{ route('profile.link-wordpress') }}">
            @csrf
            <div class="mb-2">
                <label for="wordpress_email" class="block font-medium">WordPress Email</label>
                <input type="text" name="wordpress_email" id="wordpress_email" class="input" required>
                <x-input-error :messages="$errors->userDeletion->get('wordpress_email')" class="mt-2" />
            </div>

            <div class="mb-2">
                <label for="wordpress_password" class="block font-medium">WordPress Password</label>
                <input type="password" name="wordpress_password" id="wordpress_password" class="input" required>
                <x-input-error :messages="$errors->userDeletion->get('wordpress_password')" class="mt-2" />
            </div>

            <button type="submit" class="btn btn-primary">Link WordPress Account</button>
        </form>
    @endif

</section>
