<x-app-layout>
    @section('title', 'Event Categories - ' . $event->name)
    <x-slot name="header">
        {{ __('Event Categories') }} — {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.categories.create', $event) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Add Category
        </a>
        <a href="{{ route('admin.events.edit', $event) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Event
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Categories exist only for this event and can be assigned to its shifts. Once assigned, they'll be
                    shown to volunteers as badges on the shift sign-up pages.
                </p>

                @if($categories->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No categories have been created for this event yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Shifts
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($categories as $category)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="inline-block w-4 h-4 rounded mr-2" style="background-color: {{ $category->color ?: '#9CA3AF' }}"></span>
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</span>
                                                    @if($category->description)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $category->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $category->shifts_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.events.categories.edit', [$event, $category]) }}" class="text-brand-green hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Edit</a>
                                            <form action="{{ route('admin.events.categories.destroy', [$event, $category]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category? It will be removed from any shifts using it.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
