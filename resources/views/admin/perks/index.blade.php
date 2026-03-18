<x-app-layout>
    @section('title', 'Volunteer Perks')
    <x-slot name="header">
        {{ __('Volunteer Perks') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.perk-sets.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            Perk Sets
        </a>
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
                        Perks are grouped by perk set. Link perks to specific events to count only those event hours, or leave events unlinked to count all volunteer hours for the set's fiscal year.
                        Manage sets (names, visibility dates, fiscal year) under <a href="{{ route('admin.perk-sets.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Perk Sets</a>.
                    </p>

                    @if($perkSets->isEmpty() && $unassignedPerks->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No perks have been created yet.</p>
                    @else

                        @foreach($perkSets as $set)
                            <div id="set-{{ $set->id }}" class="mb-8">
                                <div class="flex items-center gap-3 mb-3">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ $set->name }}</h3>
                                    @if($set->fiscalLedger)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $set->fiscalLedger->name }}</span>
                                    @endif
                                    @if($set->visible_until)
                                        @php $expired = $set->visible_until->lt(\Carbon\Carbon::today()); @endphp
                                        <span class="text-xs {{ $expired ? 'text-gray-400' : 'text-gray-400' }} dark:text-gray-500">
                                            · Until {{ $set->visible_until->format('M j, Y') }}
                                            @if($expired) <span class="text-orange-500">(archived)</span> @endif
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.perk-sets.edit', $set) }}" class="ml-auto text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Edit Set</a>
                                </div>

                                @if($set->perks->isEmpty())
                                    <p class="text-sm text-gray-400 dark:text-gray-500 italic pl-2">No perks in this set yet.
                                        <a href="{{ route('admin.perks.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Add one</a>
                                    </p>
                                @else
                                    @include('admin.perks._table', ['perks' => $set->perks])
                                @endif
                            </div>
                        @endforeach

                        @if($unassignedPerks->isNotEmpty())
                            <div class="mb-8">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Unassigned</h3>
                                @include('admin.perks._table', ['perks' => $unassignedPerks])
                            </div>
                        @endif

                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
