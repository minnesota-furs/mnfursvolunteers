<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Authorized Applications') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('These third-party applications can sign you in and access your account. Revoke any you no longer use or recognize.') }}
        </p>
    </header>

    @if ($authorizedClients->isEmpty())
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            {{ __("You haven't authorized any applications.") }}
        </p>
    @else
        <ul class="mt-6 space-y-4" aria-label="{{ __('Authorized applications') }}">
            @foreach ($authorizedClients as $authorization)
                <li class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $authorization->client->name }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Authorized on :date', ['date' => $authorization->authorized_at->format('M j, Y')]) }}
                            </p>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('profile.revoke-oauth-client', $authorization->client) }}"
                            onsubmit="return confirm('Revoke access for {{ $authorization->client->name }}? You will need to re-authorize it to use it again.')"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                {{ __('Revoke') }}
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
