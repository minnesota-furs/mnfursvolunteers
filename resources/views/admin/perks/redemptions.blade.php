<x-app-layout>
    @section('title', 'Redemptions: ' . $perk->name)
    <x-slot name="header">
        Redemptions: {{ $perk->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.perks.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
            ← Back to Perks
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                    <p class="text-sm text-green-800 dark:text-green-200">{!! is_array(session('success')) ? session('success')['message'] : session('success') !!}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    {{-- Perk summary --}}
                    <div class="mb-6 pb-5 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $perk->name }}</h2>
                        @if($perk->description)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $perk->description }}</p>
                        @endif
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span>Threshold: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ number_format((float)$perk->min_hours, 2) }} hrs</span></span>
                            @if($perk->reward_label)
                                <span>Reward: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $perk->reward_label }}</span></span>
                            @endif
                            <span class="ml-auto font-semibold text-gray-700 dark:text-gray-300">{{ $redemptions->count() }} redemption(s)</span>
                        </div>
                    </div>

                    @if($redemptions->isEmpty())
                        <div class="py-12 text-center">
                            <x-heroicon-o-gift class="mx-auto w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">No one has redeemed this perk yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Volunteer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Redeemed At</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($redemptions as $redemption)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $redemption->user?->name ?? '(deleted user)' }}
                                                </div>
                                                @if($redemption->user)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        {{ $redemption->user->email }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $redemption->redeemed_at->format('M j, Y g:i A') }}
                                                </span>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    {{ $redemption->redeemed_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <form action="{{ route('admin.perks.redemptions.reset', [$perk, $redemption]) }}"
                                                      method="POST"
                                                      class="inline"
                                                      onsubmit="return confirm('Reset redemption for {{ addslashes($redemption->user?->name ?? 'this user') }}? They will be able to redeem again.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                                                        Reset
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
