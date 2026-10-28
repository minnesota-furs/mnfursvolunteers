<x-app-layout>
    @section('title', 'Log Hours - Department Required')
    <x-slot name="header">
        {{ __('Log New Hours') }}
    </x-slot>

    <div class="max-w-2xl mx-auto py-8 flex flex-col items-center gap-4 text-center">
        <div class="flex items-center gap-3">
            <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-amber-500" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">You can't log hours yourself yet</h3>
        </div>

        <p class="text-sm text-gray-700 dark:text-gray-300">
            Your account doesn't have a department assigned, so it isn't considered staff. Hours can only be
            self-reported by accounts with a department.
        </p>

        <p class="text-sm text-gray-700 dark:text-gray-300">
            To have your hours logged, please contact the organization directly and include:
        </p>

        <ul class="list-disc pl-5 text-left text-sm text-gray-700 dark:text-gray-300 flex flex-col gap-1">
            <li>How many hours you volunteered</li>
            <li>What you were doing</li>
            <li>Roughly when the volunteer activity took place, if you know</li>
        </ul>

        <div>
            <a href="{{ route('dashboard') }}"
                class="inline-block rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                Back to Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
