<x-app-layout>
    @auth
        <x-slot name="header">
            {{ $oneOffEvent->name }}
        </x-slot>

        <x-slot name="actions">
            @can('manage-events')
                <a href="{{ route('one-off-events.check-ins', $oneOffEvent) }}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                    <x-heroicon-o-user-group class="w-4 inline"/> Check-ins ({{ $oneOffEvent->checkIns()->count() }})
                </a>
                <a href="{{ route('one-off-events.edit', $oneOffEvent) }}"
                    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
                    <x-heroicon-m-pencil class="w-4 inline"/> Edit
                </a>
            @endcan
            <a href="{{ route('one-off-events.index') }}"
                class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10">
                <x-heroicon-o-arrow-left class="w-4 inline"/> Back
            </a>
        </x-slot>

        <div class="">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Description Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-brand-green" />
                            About This Event
                        </h2>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ $oneOffEvent->description }}
                        </p>
                    </div>

                    <!-- Check-in Status Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <x-heroicon-o-check-circle class="w-5 h-5 mr-2 text-brand-green" />
                            Check-in Status
                        </h2>
                        
                        @if ($checkIn)
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                            You're checked in!
                                        </h3>
                                        <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                                            Checked in at {{ $checkIn->checked_in_at->format('F j, Y \a\t g:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            @php
                                $now = now();
                                $hoursBeforeStart = $oneOffEvent->checkin_hours_before ?? 1;
                                $hoursAfterEnd = $oneOffEvent->checkin_hours_after ?? 12;
                                $checkInStart = $oneOffEvent->start_time->copy()->subHours($hoursBeforeStart);
                                $checkInEnd = $oneOffEvent->end_time->copy()->addHours($hoursAfterEnd);
                                $canCheckIn = $now->isBetween($checkInStart, $checkInEnd);
                            @endphp
                            
                            @if ($canCheckIn)
                                <div class="space-y-4">
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                                        Check in to record your attendance and earn volunteer hours for this event.
                                    </p>
                                    <form method="POST" action="{{ route('one-off-events.check-in', $oneOffEvent) }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-brand-green hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-green transition-colors shadow-sm">
                                            <x-heroicon-s-check-circle class="w-5 h-5 mr-2" />
                                            Check In Now
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                                Check-in Not Available
                                            </h3>
                                            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                                Check-in is only available {{ $hoursBeforeStart }} hour(s) before the event starts until {{ $hoursAfterEnd }} hour(s) after it ends.
                                            </p>
                                            @if ($now->isBefore($checkInStart))
                                                <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                                    Check-in opens at {{ $checkInStart->format('F j, Y \a\t g:i A') }}
                                                </p>
                                            @else
                                                <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                                    Check-in closed at {{ $checkInEnd->format('F j, Y \a\t g:i A') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Event Details Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <x-heroicon-o-calendar class="w-5 h-5 mr-2 text-brand-green" />
                            Event Details
                        </h2>
                        
                        <dl class="space-y-4">
                            <!-- Start Time -->
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                    Start Time
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-medium flex items-center">
                                    <x-heroicon-s-arrow-right-circle class="w-4 h-4 mr-2 text-green-600" />
                                    {{ $oneOffEvent->start_time->format('M j, Y') }}
                                    <span class="mx-1">•</span>
                                    {{ $oneOffEvent->start_time->format('g:i A') }}
                                </dd>
                            </div>

                            <!-- End Time -->
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                    End Time
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-medium flex items-center">
                                    <x-heroicon-s-arrow-left-circle class="w-4 h-4 mr-2 text-red-600" />
                                    {{ $oneOffEvent->end_time->format('M j, Y') }}
                                    <span class="mx-1">•</span>
                                    {{ $oneOffEvent->end_time->format('g:i A') }}
                                </dd>
                            </div>

                            <!-- Duration -->
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                    Duration
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-medium flex items-center">
                                    <x-heroicon-s-clock class="w-4 h-4 mr-2 text-blue-600" />
                                    @php
                                        $duration = $oneOffEvent->start_time->diffInMinutes($oneOffEvent->end_time);
                                        $hours = floor($duration / 60);
                                        $minutes = $duration % 60;
                                        $durationText = '';
                                        if ($hours > 0) {
                                            $durationText .= $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
                                        }
                                        if ($minutes > 0) {
                                            $durationText .= ($hours > 0 ? ' ' : '') . $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes');
                                        }
                                    @endphp
                                    {{ $durationText ?: 'Less than a minute' }}
                                </dd>
                            </div>

                            @can('manage-events')
                                <!-- Total Check-ins -->
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                        Total Check-ins
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-white font-medium flex items-center">
                                        <x-heroicon-s-user-group class="w-4 h-4 mr-2 text-purple-600" />
                                        {{ $oneOffEvent->checkIns()->count() }} volunteer{{ $oneOffEvent->checkIns()->count() !== 1 ? 's' : '' }}
                                    </dd>
                                </div>
                            @endcan
                        </dl>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            Quick Actions
                        </h3>
                        <div class="space-y-2">
                            <a href="{{ route('one-off-events.index') }}"
                               class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <x-heroicon-o-arrow-left class="w-4 h-4 inline mr-1" />
                                All Events
                            </a>
                            @can('manage-events')
                                <a href="{{ route('one-off-events.check-ins', $oneOffEvent) }}"
                                   class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    <x-heroicon-o-user-group class="w-4 h-4 inline mr-1" />
                                    View All Check-ins
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Guest View -->
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center">
                <x-heroicon-o-lock-closed class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Authentication Required
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Please log in to view event details and check in.
                </p>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-green hover:bg-emerald-600">
                    Log In
                </a>
            </div>
        </div>
    @endauth
</x-app-layout>
