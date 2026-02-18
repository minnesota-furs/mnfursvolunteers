<x-app-layout>
    @section('title', 'Manage Recognition & Awards')
    <x-slot name="header">
        {{ __('Manage Recognition & Awards') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('admin.recognitions.create')}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Add Recognition
        </a>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <form method="GET" action="{{ route('admin.recognitions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="sector_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sector</label>
                    <select name="sector_id" id="sector_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Sectors</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ request('sector_id') == $sector->id ? 'selected' : '' }}>
                                {{ $sector->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="is_private" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Privacy</label>
                    <select name="is_private" id="is_private" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All</option>
                        <option value="0" {{ request('is_private') === '0' ? 'selected' : '' }}>Public</option>
                        <option value="1" {{ request('is_private') === '1' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-green text-white rounded-md hover:bg-green-700">
                        <x-heroicon-s-funnel class="w-4 mr-2"/>
                        Filter
                    </button>
                    <a href="{{ route('admin.recognitions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Recognitions Table -->
        <div class="flow-root">
            <div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    @if($recognitions->count())
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">User</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Recognition Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Type</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Date</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Sector</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">Privacy</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($recognitions as $recognition)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                            <a href="{{ route('users.show', $recognition->user) }}" class="font-medium text-brand-green hover:underline">
                                                {{ $recognition->user->name }}
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $recognition->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-3 py-1 text-xs font-medium text-blue-800 dark:text-blue-200">
                                                {{ $recognition->type }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $recognition->date->format('M d, Y') }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if($recognition->sector)
                                                {{ $recognition->sector->name }}
                                            @else
                                                <span class="text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-center text-sm">
                                            @if($recognition->is_private)
                                                <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900 px-2 py-1 text-xs font-medium text-red-800 dark:text-red-200">
                                                    <x-heroicon-s-lock-closed class="w-3 mr-1"/> Private
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900 px-2 py-1 text-xs font-medium text-green-800 dark:text-green-200">
                                                    <x-heroicon-o-eye class="w-3 mr-1"/> Public
                                                </span>
                                            @endif
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                            <a href="{{ route('admin.recognitions.edit', $recognition) }}" class="text-brand-green hover:text-green-700 mr-3">Edit</a>
                                            <form method="POST" action="{{ route('admin.recognitions.destroy', $recognition) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-s-star class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No recognition yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new recognition.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.recognitions.create') }}" class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                                    <x-heroicon-s-plus class="w-4 mr-1"/>
                                    Create Recognition
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($recognitions->count())
            <div class="mt-6">
                {{ $recognitions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
