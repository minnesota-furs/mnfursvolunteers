<x-app-layout>
    @section('title', $user->name . "'s Recognition & Awards")
    <x-slot name="header">
        {{ __($user->name . "'s Recognition & Awards") }}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- User Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</h2>
                        @if($user->sector)
                            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $user->sector->name }}</p>
                        @endif
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            <strong>{{ $recognitions->count() }}</strong> {{ $recognitions->count() === 1 ? 'Recognition' : 'Recognitions' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recognitions Grid -->
            @if($recognitions->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recognitions as $recognition)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="p-6">
                                <!-- Header with Type Badge -->
                                <div class="flex items-start justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex-1">
                                        {{ $recognition->name }}
                                    </h3>
                                </div>

                                <!-- Type and Date -->
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                            @switch($recognition->type)
                                                @case('General')
                                                    class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                                                    @break
                                                @case('Physical Award')
                                                    class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200"
                                                    @break
                                                @case('Shoutout')
                                                    class="bg-pink-100 dark:bg-pink-900 text-pink-800 dark:text-pink-200"
                                                    @break
                                                @case('Other')
                                                    class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
                                                    @break
                                            @endswitch
                                        >
                                            {{ $recognition->type }}
                                        </span>
                                    </div>

                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <x-heroicon-s-calendar class="w-4 inline mr-1"/>
                                        {{ $recognition->date->format('F d, Y') }}
                                    </div>

                                    @if($recognition->sector)
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            <x-heroicon-s-building-office-2 class="w-4 inline mr-1"/>
                                            {{ $recognition->sector->name }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Description -->
                                @if($recognition->description)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                                        {{ $recognition->description }}
                                    </p>
                                @endif

                                <!-- Granted By -->
                                @if($recognition->grantedByUser)
                                    <div class="border-t dark:border-gray-700 pt-3 mt-3">
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            Granted by {{ $recognition->grantedByUser->name }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-s-star class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No public recognition</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user hasn't received any public recognition or awards.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
