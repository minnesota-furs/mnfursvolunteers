<header>
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ __('Accessibility needs') }}
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Optionally share any accommodations that may help us assign volunteer work that is comfortable and accessible for you.') }}
    </p>
</header>

<form method="post" action="{{ route('onboarding.accessibility-needs') }}" class="mt-6 space-y-6">
    @csrf

    <x-accessibility-needs-fields :user="$user" />

    <div class="flex justify-end">
        <x-primary-button>{{ __('Save & Continue') }}</x-primary-button>
    </div>
</form>
