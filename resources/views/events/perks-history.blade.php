<x-app-layout>
    @section('title', 'Perk History')
    <x-slot name="header">
        {{ __('Perk History') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('volunteer.perks.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            Current Perks
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 px-1">
                Your progress from past perk sets. These records are final — they show your hours at the time the set ended.
            </p>

            @if($sets->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                    <x-heroicon-o-clock class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400">No past perk sets yet. Check back after a convention year ends!</p>
                </div>
            @else
                <div class="space-y-8">
                    @foreach($sets as $item)
                        @php
                            /** @var \App\Models\VolunteerPerkSet $set */
                            $set = $item['set'];
                        @endphp

                        <div>
                            {{-- Set header --}}
                            <div class="flex items-center gap-3 mb-3 flex-wrap">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $set->name }}</h2>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    Archived
                                </span>
                                @if($set->fiscalLedger)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $set->fiscalLedger->name }}</span>
                                @endif
                                @if($set->visible_until)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">· Ended {{ $set->visible_until->format('M j, Y') }}</span>
                                @endif
                            </div>
                            @if($set->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $set->description }}</p>
                            @endif

                            {{-- Perks --}}
                            <div class="space-y-3">
                                @foreach($item['perks'] as $perkItem)
                                    @php
                                        /** @var \App\Models\VolunteerPerk $perk */
                                        $perk         = $perkItem['perk'];
                                        $progress     = $perkItem['progress'];
                                        $percentage   = $perkItem['percentage'];
                                        $earned       = $perkItem['earned'];
                                        $breakdown    = $perkItem['breakdown'];
                                        $minHours     = (float) $perk->min_hours;
                                        $completedPct = $minHours > 0 ? min(100, $breakdown['completed'] / $minHours * 100) : 100;
                                        $upcomingPct  = $minHours > 0 ? min(100 - $completedPct, $breakdown['upcoming'] / $minHours * 100) : 0;
                                    @endphp

                                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4
                                        {{ $earned ? 'ring-2 ring-green-400 dark:ring-green-600' : 'opacity-75' }}">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ $perk->name }}
                                                    </h3>
                                                    @if($earned)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Earned
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($perk->description)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $perk->description }}</p>
                                                @endif
                                            </div>

                                            <div class="text-right shrink-0">
                                                <p class="text-lg font-bold {{ $earned ? 'text-green-600 dark:text-green-400' : 'text-gray-700 dark:text-gray-300' }}">
                                                    {{ number_format($progress, 1) }}
                                                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">/ {{ number_format((float)$perk->min_hours, 1) }} hrs</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden flex">
                                                <div class="h-2 transition-all duration-500 {{ $earned ? 'bg-green-500' : 'bg-gray-400 dark:bg-gray-500' }}"
                                                    style="width: {{ $completedPct }}%">
                                                </div>
                                                <div class="h-2 bg-yellow-400 dark:bg-yellow-500 transition-all duration-500"
                                                    style="width: {{ $upcomingPct }}%">
                                                </div>
                                            </div>
                                            @if($breakdown['completed'] > 0 || $breakdown['upcoming'] > 0)
                                                <div class="flex items-center gap-3 mt-1">
                                                    @if($breakdown['completed'] > 0)
                                                        <span class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                                            <span class="inline-block w-2 h-2 rounded-full {{ $earned ? 'bg-green-500' : 'bg-gray-400 dark:bg-gray-500' }} shrink-0"></span>
                                                            {{ number_format($breakdown['completed'], 1) }} completed
                                                        </span>
                                                    @endif
                                                    @if($breakdown['upcoming'] > 0)
                                                        <span class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                                            <span class="inline-block w-2 h-2 rounded-full bg-yellow-400 dark:bg-yellow-500 shrink-0"></span>
                                                            {{ number_format($breakdown['upcoming'], 1) }} upcoming
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
