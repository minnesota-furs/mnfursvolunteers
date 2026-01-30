<x-app-layout>
    @auth
        @section('title', 'Activity Timeline - ' . $user->name)
        <x-slot name="header">
            {{ __('Activity Timeline: ') }}{{ $user->name }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('users.show', $user) }}" 
                class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Back to Profile
            </a>
        </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Complete Activity Timeline
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Showing {{ $timelineEvents->total() }} total events
                            </p>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Page {{ $timelineEvents->currentPage() }} of {{ $timelineEvents->lastPage() }}
                        </div>
                    </div>
                </div>
                <div class="px-4 py-6 sm:p-6">
                    @if($timelineEvents->count() > 0)
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($timelineEvents as $event)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800 
                                                        @if($event['type'] === 'volunteer_hours')
                                                            bg-green-500
                                                        @elseif($event['type'] === 'shift_signup')
                                                            bg-blue-500
                                                        @elseif($event['type'] === 'note')
                                                            {{ $event['note_type'] === 'Writeup' ? 'bg-red-500' : 'bg-yellow-500' }}
                                                        @elseif($event['type'] === 'audit_log')
                                                            bg-gray-500
                                                        @else
                                                            bg-purple-500
                                                        @endif">
                                                        @if($event['type'] === 'volunteer_hours')
                                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        @elseif($event['type'] === 'shift_signup')
                                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                                            </svg>
                                                        @elseif($event['type'] === 'note')
                                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                            </svg>
                                                        @elseif($event['type'] === 'audit_log')
                                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                            </svg>
                                                        @else
                                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                                            </svg>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ $event['title'] }}
                                                        </p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $event['description'] }}
                                                        </p>
                                                        @if($event['type'] === 'volunteer_hours')
                                                            @if(isset($event['department']) || isset($event['sector']))
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                    @if(isset($event['sector']))
                                                                        <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-2 py-0.5 text-xs font-medium text-blue-800 dark:text-blue-200">
                                                                            {{ $event['sector'] }}
                                                                        </span>
                                                                    @endif
                                                                    @if(isset($event['department']))
                                                                        <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900 px-2 py-0.5 text-xs font-medium text-green-800 dark:text-green-200">
                                                                            {{ $event['department'] }}
                                                                        </span>
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        @elseif($event['type'] === 'note')
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset
                                                                    @if($event['note_type'] === 'Writeup')
                                                                        bg-red-50 dark:bg-red-900/50 text-red-700 dark:text-red-300 ring-red-600/20 dark:ring-red-400/30
                                                                    @else
                                                                        bg-yellow-50 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-300 ring-yellow-600/20 dark:ring-yellow-400/30
                                                                    @endif">
                                                                    {{ $event['note_type'] }}
                                                                </span>
                                                                @if($event['is_private'])
                                                                    <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-900/50 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-400 ring-1 ring-inset ring-gray-500/10 dark:ring-gray-400/20 ml-1">
                                                                        <svg class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                                        </svg>
                                                                        Private
                                                                    </span>
                                                                @endif
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                By: {{ $event['created_by'] }}
                                                            </p>
                                                        @elseif($event['type'] === 'shift_signup')
                                                            @if(isset($event['event_name']))
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                    Event: {{ $event['event_name'] }}
                                                                </p>
                                                            @endif
                                                            @if(isset($event['start_time']))
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ \Carbon\Carbon::parse($event['start_time'])->format('M j, Y g:i A') }}
                                                                    @if(isset($event['end_time']))
                                                                        - {{ \Carbon\Carbon::parse($event['end_time'])->format('g:i A') }}
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        @elseif($event['type'] === 'audit_log')
                                                            @if(isset($event['performed_by']))
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                    By: {{ $event['performed_by'] }}
                                                                </p>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                                        <time datetime="{{ $event['date'] }}">
                                                            {{ \Carbon\Carbon::parse($event['date'])->format('M j, Y') }}
                                                        </time>
                                                        <p class="text-xs">
                                                            {{ \Carbon\Carbon::parse($event['date'])->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Pagination --}}
                        @if($timelineEvents->hasPages())
                            <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                                {{ $timelineEvents->links() }}
                            </div>
                        @endif
                    @endauth
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No activity yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user has no recorded activity.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
