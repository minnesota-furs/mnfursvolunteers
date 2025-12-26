<x-app-layout>
    @auth
        @section('title', 'Communications - ' . $user->name)
        <x-slot name="header">
            {{ __('Communications for ') }}{{ $user->name }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('users.show', $user->id) }}"
                class="block rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-center text-sm font-semibold text-brand-green dark:text-gray-200 shadow-md hover:bg-gray-100 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Back to User
            </a>
        </x-slot>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                    {{ session('success')['message'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Error Message -->
                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Send Test Email Section -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Send Test Email</h3>
                        <div class="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                            <p>Send a test email to {{ $user->name }} at <strong
                                    class="text-gray-700 dark:text-gray-300">{{ $user->email }}</strong> to verify email
                                delivery.</p>
                        </div>
                        <div class="mt-5">
                            <form action="{{ route('users.send-test-email', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    Send Test Email
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Communications History Section -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">Communications History</h3>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <p>View all emails and communications sent to this user.</p>
                        </div>
                        <div class="mt-6">
                            @if($communications->isEmpty())
                                <!-- Placeholder for no communications -->
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No communications yet</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Communications sent to this user will appear here.
                                    </p>
                                </div>
                            @else
                                <!-- Communications List -->
                                <div class="overflow-hidden">
                                    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($communications as $communication)
                                            <li class="py-4">
                                                <div class="flex space-x-3">
                                                    <div class="flex-shrink-0">
                                                        @if($communication->type === 'email')
                                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                            </svg>
                                                        @else
                                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 space-y-1">
                                                        <div class="flex items-center justify-between">
                                                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                                                {{ $communication->subject }}
                                                            </h3>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                {{ $communication->created_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $communication->message }}
                                                        </p>
                                                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                            <span class="inline-flex items-center">
                                                                <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                                </svg>
                                                                {{ $communication->recipient_email }}
                                                            </span>
                                                            <span class="inline-flex items-center">
                                                                @if($communication->status === 'sent')
                                                                    <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-900/20 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20">
                                                                        ✓ Sent
                                                                    </span>
                                                                @elseif($communication->status === 'failed')
                                                                    <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900/20 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-600/20">
                                                                        ✗ Failed
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center rounded-md bg-yellow-50 dark:bg-yellow-900/20 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-400 ring-1 ring-inset ring-yellow-600/20">
                                                                        ⏱ {{ ucfirst($communication->status) }}
                                                                    </span>
                                                                @endif
                                                            </span>
                                                            @if($communication->sender)
                                                                <span class="inline-flex items-center">
                                                                    <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                    </svg>
                                                                    Sent by {{ $communication->sender->name }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($communication->metadata && isset($communication->metadata['election_title']))
                                                            <div class="mt-2">
                                                                <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-400 ring-1 ring-inset ring-blue-600/20">
                                                                    Election: {{ $communication->metadata['election_title'] }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="mt-6">
                                    {{ $communications->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
