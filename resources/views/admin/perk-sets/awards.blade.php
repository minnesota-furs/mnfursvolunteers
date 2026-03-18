<x-app-layout>
    @section('title', 'Perk Awards: ' . $perkSet->name)
    <x-slot name="header">
        Perk Awards: {{ $perkSet->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.perk-sets.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            Back to Perk Sets
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Set summary --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg px-6 py-4 mb-6 flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $perkSet->name }}</p>
                    @if($perkSet->description)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $perkSet->description }}</p>
                    @endif
                </div>
                @if($perkSet->fiscalLedger)
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Fiscal Year:</span>
                        {{ $perkSet->fiscalLedger->name }}
                    </div>
                @endif
                <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                    <span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $report->count() }}</span>
                        perk{{ $report->count() !== 1 ? 's' : '' }}
                    </span>
                    <span>•</span>
                    <span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ $report->flatMap(fn ($row) => $row['earners']->pluck('id'))->unique()->count() }}
                        </span>
                        unique earner{{ $report->flatMap(fn ($row) => $row['earners']->pluck('id'))->unique()->count() !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            @if($report->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
                    This perk set has no perks yet.
                </div>
            @else
                <div class="space-y-6">
                    @foreach($report as $row)
                        @php
                            /** @var \App\Models\VolunteerPerk $perk */
                            $perk    = $row['perk'];
                            $earners = $row['earners'];
                        @endphp

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            {{-- Perk header --}}
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between gap-4 flex-wrap">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            @if($perk->is_mystery)
                                                <x-heroicon-s-question-mark-circle class="w-4 h-4 inline text-purple-500 dark:text-purple-400 -mt-0.5" />
                                            @endif
                                            {{ $perk->name }}
                                        </h2>
                                        @if($perk->is_mystery)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                Mystery
                                            </span>
                                        @endif
                                        @if(!$perk->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                    @if($perk->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $perk->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 shrink-0 text-sm">
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Min. Hours</p>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float)$perk->min_hours, 2) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Earners</p>
                                        <p class="font-semibold {{ $earners->isEmpty() ? 'text-gray-400 dark:text-gray-500' : 'text-green-600 dark:text-green-400' }}">
                                            {{ $earners->count() }}
                                        </p>
                                    </div>
                                    @if($perk->events->isNotEmpty())
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Tracks</p>
                                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $perk->events->count() }} event(s)</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Earner list --}}
                            @if($earners->isEmpty())
                                <div class="px-6 py-5 text-sm text-gray-400 dark:text-gray-500 text-center">
                                    No volunteers have earned this perk yet.
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vol Code</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                                @if($perk->has_physical_reward)
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Redemption</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($earners as $user)
                                                @php
                                                    $redemption = $perk->redemptions->firstWhere('user_id', $user->id);
                                                @endphp
                                                <tr>
                                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $user->name }}
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap">
                                                        @if($user->vol_code)
                                                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded">{{ $user->vol_code }}</span>
                                                        @else
                                                            <span class="text-xs text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $user->email }}
                                                    </td>
                                                    @if($perk->has_physical_reward)
                                                        <td class="px-6 py-3 whitespace-nowrap text-sm">
                                                            @if($redemption)
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                                    <x-heroicon-s-check-circle class="w-3 h-3" />
                                                                    Redeemed {{ $redemption->redeemed_at?->format('M j, Y g:i A') }}
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                                    Not redeemed
                                                                </span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
