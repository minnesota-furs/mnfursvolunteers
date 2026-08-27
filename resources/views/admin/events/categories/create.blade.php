<x-app-layout>
    @section('title', 'Add Category - ' . $event->name)
    <x-slot name="header">
        {{ __('Add Category') }} — {{ $event->name }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.events.categories.store', $event) }}">
                        @csrf
                        @include('admin.events.categories._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
