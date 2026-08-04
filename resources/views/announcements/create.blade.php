<x-app-layout>
    @section('title', 'Create Announcement')
    <x-slot name="header">{{ __('Create Announcement') }}</x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
            <form method="POST" action="{{ route('announcements.store') }}">
                @include('announcements._form')
            </form>
        </div>
    </div>
</x-app-layout>
