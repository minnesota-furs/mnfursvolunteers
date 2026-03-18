<x-app-layout>
    @section('title', 'Volunteer Perks')
    <x-slot name="header">
        {{ __('Volunteer Perks') }}
    </x-slot>

    <div class="">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if($perks->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                    <x-heroicon-o-gift class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400">No perks have been set up yet. Check back later!</p>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 px-1">
                    Track your progress toward earning volunteer perks. Hours are counted once shifts have been marked complete.
                </p>

                <div class="space-y-4">
                    @foreach($perks as $item)
                        @php
                            /** @var \App\Models\VolunteerPerk $perk */
                            $perk       = $item['perk'];
                            $progress   = $item['progress'];
                            $percentage = $item['percentage'];
                            $earned     = $item['earned'];
                        @endphp

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5
                            {{ $earned ? 'ring-2 ring-green-400 dark:ring-green-600' : '' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $perk->name }}
                                        </h3>
                                        @if($earned)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Earned
                                            </span>
                                        @endif
                                    </div>

                                    @if($perk->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $perk->description }}</p>
                                    @endif

                                    @if($perk->events->isNotEmpty())
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            Tracks hours from:
                                            @foreach($perk->events as $i => $event)
                                                <span class="font-medium text-gray-600 dark:text-gray-300">{{ $event->name }}</span>{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </p>
                                    @elseif($perk->fiscalLedger)
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            Tracks all hours in <span class="font-medium text-gray-600 dark:text-gray-300">{{ $perk->fiscalLedger->name }}</span>
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tracks all volunteer hours</p>
                                    @endif
                                </div>

                                <div class="text-right shrink-0">
                                    <p class="text-2xl font-bold {{ $earned ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-gray-100' }}">
                                        {{ number_format($progress, 1) }}
                                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">/ {{ number_format((float)$perk->min_hours, 1) }} hrs</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Progress bar --}}
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Progress</span>
                                    <span>{{ number_format($percentage, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                    <div class="h-3 rounded-full transition-all duration-500
                                        {{ $earned ? 'bg-green-500' : 'bg-brand-green' }}"
                                        style="width: {{ $percentage }}%">
                                    </div>
                                </div>
                                @if(!$earned)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ number_format(max(0, (float)$perk->min_hours - $progress), 1) }} more hour(s) needed
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
