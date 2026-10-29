<x-app-layout>
    @section('title', 'Board Elections')
    <x-slot name="header">
        {{ __('Board Elections') }}
    </x-slot>

    @can('manage-elections')
        <x-slot name="actions">
            <a href="{{ route('admin.elections.index') }}"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-cog-6-tooth class="w-4 inline"/> Manage Elections
            </a>
        </x-slot>
    @endcan

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($elections->count() > 0)
                <div class="space-y-6">
                    @foreach($elections as $election)
                        @php
                            $stage = $election->currentStage();
                        @endphp
                        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 break-words sm:text-xl">
                                            {{ $election->title }}
                                        </h3>

                                        @if($election->description)
                                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 prose prose-sm max-w-none break-words">
                                                {!! $election->parsedDescription !!}
                                            </div>
                                        @endif

                                        <p class="mt-4 text-sm text-gray-900 dark:text-gray-100">
                                            <span class="{{ $stage['color'] }} font-medium">{{ $stage['label'] }}</span>
                                            @if($stage['detail'])
                                                <span class="text-gray-500 dark:text-gray-400">· {{ $stage['detail'] }}</span>
                                            @endif
                                        </p>
                                    </div>

                                    <div class="sm:ml-6 sm:flex-shrink-0">
                                        <a href="{{ route('elections.show', $election) }}"
                                            class="flex w-full items-center justify-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 sm:inline-flex sm:w-auto">
                                            <x-heroicon-s-eye class="w-4 h-4 mr-1"/> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No active elections</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        There are currently no board elections accepting votes. Check back later or contact an administrator for more information.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
