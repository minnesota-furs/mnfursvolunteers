<x-app-layout>
    @section('title', 'Staff & Concat')
    <x-slot name="header">Staff &amp; Concat</x-slot>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-3">
            <a href="{{ route('report.staffConcat.unlinked') }}" class="group rounded-xl bg-white p-8 shadow transition hover:-translate-y-1 hover:shadow-lg dark:bg-gray-800">
                <x-heroicon-o-user-minus class="h-12 w-12 text-brand-green" />
                <h2 class="mt-5 text-xl font-bold text-gray-900 dark:text-white">Unlinked Users</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Active staff with no ConCat account associated.</p>
            </a>
            <a href="{{ route('report.staffConcat.withRegistration') }}" class="group rounded-xl bg-white p-8 shadow transition hover:-translate-y-1 hover:shadow-lg dark:bg-gray-800">
                <x-heroicon-o-check-circle class="h-12 w-12 text-brand-green" />
                <h2 class="mt-5 text-xl font-bold text-gray-900 dark:text-white">Staff With Registrations</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Linked staff who have an event registration on ConCat.</p>
            </a>
            <a href="{{ route('report.staffConcat.withoutRegistration') }}" class="group rounded-xl bg-white p-8 shadow transition hover:-translate-y-1 hover:shadow-lg dark:bg-gray-800">
                <x-heroicon-o-x-circle class="h-12 w-12 text-brand-green" />
                <h2 class="mt-5 text-xl font-bold text-gray-900 dark:text-white">Staff Without Registration</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Linked staff whose ConCat account has no event registration yet.</p>
            </a>
        </div>
    </div>
</x-app-layout>
