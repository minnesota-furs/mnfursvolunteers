<x-guest-layout>
    <x-slot name="header">
        403 Forbidden
    </x-slot>

    <div class="py-6">
        <div class="text-center">
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">Access Denied</h1>
            <p class="mt-6 text-base leading-7 text-gray-600">{{ $exception->getMessage() ?: "You don't have permission to access this page." }}</p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
                <a href="{{ url()->previous() }}" class="rounded-md bg-brand-green px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Go back</a>
            </div>
        </div>
    </div>
</x-guest-layout>
