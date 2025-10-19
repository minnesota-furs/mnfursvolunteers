<x-app-layout>
    @auth
        <x-slot name="header">
            {{ __('Check-ins for ') . $oneOffEvent->name }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('one-off-events.edit', $oneOffEvent) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                <x-heroicon-m-pencil class="w-4 inline"/> Edit Event
            </a>
            <a href="{{ route('one-off-events.show', $oneOffEvent) }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                <x-heroicon-o-arrow-left class="w-4 inline"/> Back to Event
            </a>
        </x-slot>

        <div class="">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Event Summary --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $oneOffEvent->name }}</h1>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p><strong>Date:</strong> {{ $oneOffEvent->start_time->format('F j, Y') }}</p>
                        <p><strong>Time:</strong> {{ $oneOffEvent->start_time->format('g:i A') }} - {{ $oneOffEvent->end_time->format('g:i A') }}</p>
                        <p><strong>Duration:</strong> {{ $oneOffEvent->start_time->floatDiffInHours($oneOffEvent->end_time) }} hours</p>
                        <p><strong>Auto-credit hours:</strong> 
                            <span class="{{ $oneOffEvent->auto_credit_hours ? 'text-green-600' : 'text-gray-500' }}">
                                {{ $oneOffEvent->auto_credit_hours ? '✓ Enabled' : '✗ Disabled' }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Statistics --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Check-ins</div>
                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $checkIns->count() }}</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="text-sm font-medium text-green-700 dark:text-green-300">Hours Credited</div>
                        <div class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $checkIns->where('hours_credited', true)->count() }}</div>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                        <div class="text-sm font-medium text-yellow-700 dark:text-yellow-300">Pending Credit</div>
                        <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $checkIns->where('hours_credited', false)->count() }}</div>
                    </div>
                </div>

                {{-- Check-ins Table --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Check-in List</h2>
                    </div>

                    @if($checkIns->isEmpty())
                        <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <x-heroicon-o-user-group class="w-12 h-12 mx-auto mb-4 opacity-50"/>
                            <p class="text-lg font-medium">No check-ins yet</p>
                            <p class="text-sm">Users can check in starting 1 hour before the event.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Volunteer
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Check-in Time
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($checkIns as $checkIn)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            <a href="{{ route('users.show', $checkIn->user) }}" class="hover:underline text-blue-600 dark:text-blue-400">
                                                                {{ $checkIn->user->name }}
                                                            </a>
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $checkIn->user->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $checkIn->checked_in_at->format('M j, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $checkIn->checked_in_at->format('g:i A') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($checkIn->hours_credited)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                        <x-heroicon-s-check-circle class="w-4 h-4 mr-1"/> Hours Credited
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                                        <x-heroicon-s-clock class="w-4 h-4 mr-1"/> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if(!$checkIn->hours_credited)
                                                    <form method="POST" action="{{ route('one-off-events.check-ins.credit', [$oneOffEvent, $checkIn]) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-brand-green hover:text-indigo-900 dark:hover:text-indigo-400"
                                                                onclick="return confirm('Credit {{ $oneOffEvent->start_time->floatDiffInHours($oneOffEvent->end_time) }} hours to {{ $checkIn->user->name }}?')">
                                                            <x-heroicon-m-plus-circle class="w-5 h-5 inline"/> Credit Hours
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-600">
                                                        <x-heroicon-s-check class="w-5 h-5 inline"/> Completed
                                                    </span>
                                                @endif
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
    @endauth
</x-app-layout>
