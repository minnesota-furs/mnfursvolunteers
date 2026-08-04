<x-app-layout>
    @section('title', 'Staff Check-in')
    <x-slot name="header">Staff Check-in</x-slot>

    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2">
            <a href="{{ route('report.staffCheckIn.paper') }}" class="group rounded-xl bg-white p-8 shadow transition hover:-translate-y-1 hover:shadow-lg dark:bg-gray-800">
                <x-heroicon-o-printer class="h-12 w-12 text-brand-green" />
                <h2 class="mt-5 text-2xl font-bold text-gray-900 dark:text-white">Paper Printout experience</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Build and print a staff roster with custom fields, checklist boxes, and signature spaces.</p>
            </a>
            <a href="{{ route('report.staffCheckIn.digital.index') }}" class="group rounded-xl bg-white p-8 shadow transition hover:-translate-y-1 hover:shadow-lg dark:bg-gray-800">
                <x-heroicon-o-device-tablet class="h-12 w-12 text-brand-green" />
                <h2 class="mt-5 text-2xl font-bold text-gray-900 dark:text-white">Digital experience</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Create an iPad-friendly session, track handouts, and collect staff signatures.</p>
            </a>
        </div>
    </div>
</x-app-layout>
