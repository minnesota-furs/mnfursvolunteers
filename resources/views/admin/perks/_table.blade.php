<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Perk</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Min. Hours</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tracks</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
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
                            <span class="text-xs text-gray-500 dark:text-gray-400">All hours</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-wrap gap-1">
                            @if($perk->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    Inactive
                                </span>
                            @endif
                            @if($perk->is_mystery)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    <x-heroicon-s-question-mark-circle class="w-3 h-3" /> Mystery
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                        <a href="{{ route('admin.perks.edit', $perk) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit</a>
                        @if($perk->has_physical_reward)
                            <a href="{{ route('admin.perks.redemptions', $perk) }}" class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300">Redemptions</a>
                        @endif
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
