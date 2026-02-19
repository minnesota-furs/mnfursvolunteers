<x-app-layout>
    @section('title', 'Event Log')
    <x-slot name="header">
        {{ __('Event Log') }} - {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ url()->previous() }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 sm:px-6 lg:px-8">
                {{-- <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold leading-6 text-gray-900">Events</h1>
                    </div>
                </div> --}}
                <div class="flow-root">
                    <div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            {{-- {{ $events->links() }} --}}
                            <table class="relative min-w-full divide-y divide-gray-300 dark:divide-white/15">
                                <thead>
                                    <tr>
                                    <th scope="col" class="whitespace-nowrap py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0 dark:text-white">Date</th>
                                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">User</th>
                                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Action</th>
                                    <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Changes</th>
                                    {{-- <th scope="col" class="whitespace-nowrap px-2 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Comments</th> --}}
                                    {{-- <th scope="col" class="whitespace-nowrap py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Edit</span>
                                    </th> --}}
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-white/10 dark:bg-gray-900">
                                    @forelse ($logs as $log)
                                    <tr>
                                        <td class="whitespace-nowrap flex flex-col py-2 pl-4 pr-3 text-sm text-gray-500 sm:pl-0 dark:text-gray-400">
                                            <span>{{ $log->created_at->format('M d - h:i A') }}</span>
                                            <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-2 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $log->user->name }}</td>
                                        <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-900 dark:text-white">{{ $log->action }}</td>
                                        <td class="px-2 py-2 text-sm text-gray-500 dark:text-gray-400 break-words">
                                            {{-- <pre>{{ json_encode($log->changes, JSON_PRETTY_PRINT) }}</pre> --}}
                                            @if($log->comment)
                                                <em>{{ $log->comment }}</em>
                                            @elseif(!empty($log->changes['new']))
                                                {{ implode(', ', array_keys($log->changes['new'])) }}
                                            @else
                                                –
                                            @endif
                                        </td>
                                        {{-- <td class="whitespace-nowrap px-2 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $log->comment ?? 'N/A'}}</td> --}}
                                        {{-- <td class="whitespace-nowrap py-2 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit<span class="sr-only">, AAPS0L</span></a>
                                        </td> --}}
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="whitespace-nowrap py-2 pl-4 pr-3 text-sm text-gray-500 sm:pl-0 dark:text-gray-400 text-center">No logs found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                </table>
                            {{ $logs->links('components.compact-pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
