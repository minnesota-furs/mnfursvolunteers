<x-app-layout>
    @section('title', 'Perk Sets')
    <x-slot name="header">
        {{ __('Perk Sets') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.perks.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            Perks
        </a>
        <a href="{{ route('admin.perk-sets.create') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Create Perk Set
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
                        Perk sets group related perks for a specific convention year or event. Once the <em>Visible Until</em> date passes, the set moves to the volunteer perk history page automatically.
                    </p>

                    @if($sets->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No perk sets have been created yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Perks</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fiscal Year</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Visibility Window</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($sets as $set)
                                        @php
                                            $today      = \Carbon\Carbon::today();
                                            $isExpired  = $set->visible_until && $set->visible_until->lt($today);
                                            $isUpcoming = $set->visible_from && $set->visible_from->gt($today);
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $set->name }}</div>
                                                @if($set->description)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 max-w-xs">{{ Str::limit($set->description, 80) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $set->perks_count }}</span>
                                                <a href="{{ route('admin.perks.index') }}#set-{{ $set->id }}" class="ml-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Manage</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $set->fiscalLedger?->name ?? '—' }}
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                                @if($set->visible_from || $set->visible_until)
                                                    @if($set->visible_from)
                                                        <div>From: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $set->visible_from->format('M j, Y') }}</span></div>
                                                    @endif
                                                    @if($set->visible_until)
                                                        <div>Until: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $set->visible_until->format('M j, Y') }}</span></div>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">Always visible</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($isExpired)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                        Archived
                                                    </span>
                                                @elseif(!$set->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                        Inactive
                                                    </span>
                                                @elseif($isUpcoming)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                                        Upcoming
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                        Active
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                                <a href="{{ route('admin.perk-sets.edit', $set) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit</a>
                                                <a href="{{ route('admin.perk-sets.awards', $set) }}" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">Perk Awards</a>
                                                <form action="{{ route('admin.perk-sets.destroy', $set) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                        onclick="return confirm('Delete perk set \'{{ addslashes($set->name) }}\'?\n\nPerks in this set will NOT be deleted but will become unassigned.')">
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
