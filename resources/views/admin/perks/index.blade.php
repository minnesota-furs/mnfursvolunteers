<x-app-layout>
    @section('title', 'Volunteer Perks')
    <x-slot name="header">
        {{ __('Volunteer Perks') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.perks.create') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Create Perk
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                    <p class="text-sm text-green-800 dark:text-green-200">{!! is_array(session('success')) ? session('success')['message'] : session('success') !!}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Perks reward volunteers for reaching hour milestones. Link a perk to specific events to only count hours from those events, or leave events unlinked to count all volunteer hours (optionally filtered by fiscal year).
                    </p>

                    @if($perks->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No perks have been created yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Perk
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Min. Hours
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tracks
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($perks as $perk)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $perk->name }}</div>
                                                @if($perk->description)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 max-w-xs">{{ Str::limit($perk->description, 80) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float)$perk->min_hours, 2) }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">hrs</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($perk->events->isNotEmpty())
                                                    <div class="text-xs text-gray-700 dark:text-gray-300">
                                                        <span class="font-medium">{{ $perk->events->count() }} event(s):</span>
                                                        <ul class="mt-0.5 space-y-0.5">
                                                            @foreach($perk->events->take(3) as $event)
                                                                <li>• {{ $event->name }}</li>
                                                            @endforeach
                                                            @if($perk->events->count() > 3)
                                                                <li class="text-gray-400">+ {{ $perk->events->count() - 3 }} more</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        All hours
                                                        @if($perk->fiscalLedger)
                                                            <br><span class="font-medium">{{ $perk->fiscalLedger->name }}</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($perk->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                                <a href="{{ route('admin.perks.edit', $perk) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit</a>
                                                <form action="{{ route('admin.perks.destroy', $perk) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                        onclick="return confirm('Delete perk \'{{ addslashes($perk->name) }}\'? This cannot be undone.')">
                                                        Delete
                                                    </button>
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
    </div>
</x-app-layout>
