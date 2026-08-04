<x-app-layout>
    @section('title', 'Digital Staff Check-in')
    <x-slot name="header">Digital Staff Check-in</x-slot>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <p class="text-gray-600 dark:text-gray-300">Select an existing session or start a new check-in process.</p>
            <a href="{{ route('report.staffCheckIn.digital.create') }}" class="rounded-lg bg-brand-green px-5 py-3 font-semibold text-white">Create Check-in Session</a>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            @forelse($sessions as $session)
                <a href="{{ route('report.staffCheckIn.digital.show', $session) }}" class="rounded-xl bg-white p-6 shadow hover:ring-2 hover:ring-brand-green dark:bg-gray-800">
                    <div class="flex justify-between gap-4">
                        <div><h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $session->name }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $session->groupName() }}</p></div>
                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">{{ $session->check_ins_count }} checked in</span>
                    </div>
                </a>
            @empty
                <div class="rounded-xl bg-white p-8 text-center text-gray-500 shadow dark:bg-gray-800 dark:text-gray-400 md:col-span-2">No digital check-in sessions yet.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
