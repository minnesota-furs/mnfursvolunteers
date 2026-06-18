<x-app-layout>
    @section('title', 'Profile - ' . $user->displayName())
    <x-slot name="header">
        {{ $user->displayName() }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ url()->previous() }}"
            class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors">
            <x-heroicon-m-arrow-left class="w-4 h-4"/>
            Back
        </a>
    </x-slot>

    <div class="space-y-6">

        {{-- Profile card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex flex-col sm:flex-row items-start gap-5">

                {{-- Avatar --}}
                <div class="w-16 h-16 rounded-full bg-brand-green/20 dark:bg-brand-green/30 flex items-center justify-center text-2xl font-bold text-brand-green flex-shrink-0">
                    {{ strtoupper(substr($user->displayName(), 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $user->displayName() }}</h2>

                    @if($user->pronouns)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $user->pronouns }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @if($user->departments->isNotEmpty())
                            <span class="flex items-center gap-1.5">
                                <x-heroicon-m-building-office class="w-4 h-4 text-gray-400"/>
                                {{ $user->departments->pluck('name')->join(', ') }}
                            </span>
                        @endif
                        @if($user->sector)
                            <span class="flex items-center gap-1.5">
                                <x-heroicon-m-squares-2x2 class="w-4 h-4 text-gray-400"/>
                                {{ $user->sector->name }}
                            </span>
                        @endif
                    </div>

                    {{-- Tags --}}
                    @if($user->tags->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach($user->tags as $tag)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                    style="background-color:{{ $tag->color }}22; color:{{ $tag->color }}; border-color:{{ $tag->color }}44;">
                                    @if($tag->color)
                                        <span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color:{{ $tag->color }}"></span>
                                    @endif
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Favorite / Avoid buttons --}}
                @feature('volunteer_relationships')
                @if(auth()->id() !== $user->id)
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form action="{{ route('users.relationship.toggle', $user) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="favorite">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium transition-colors
                                    {{ $relationship === 'favorite'
                                        ? 'bg-yellow-100 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700 text-yellow-700 dark:text-yellow-400'
                                        : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-yellow-300 dark:hover:border-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/10' }}"
                                title="{{ $relationship === 'favorite' ? 'Remove favorite' : 'Mark as favorite' }}">
                                @if($relationship === 'favorite')
                                    <x-heroicon-s-star class="w-4 h-4 text-yellow-500"/>
                                @else
                                    <x-heroicon-o-star class="w-4 h-4"/>
                                @endif
                                Favorite
                            </button>
                        </form>
                        <form action="{{ route('users.relationship.toggle', $user) }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="avoid">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium transition-colors
                                    {{ $relationship === 'avoid'
                                        ? 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700 text-red-700 dark:text-red-400'
                                        : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-red-300 dark:hover:border-red-600 hover:bg-red-50 dark:hover:bg-red-900/10' }}"
                                title="{{ $relationship === 'avoid' ? 'Remove avoid' : 'Mark as avoid' }}">
                                @if($relationship === 'avoid')
                                    <x-heroicon-s-hand-raised class="w-4 h-4 text-red-500"/>
                                @else
                                    <x-heroicon-o-hand-raised class="w-4 h-4"/>
                                @endif
                                Avoid
                            </button>
                        </form>
                    </div>
                @endif
                @endfeature
            </div>
        </div>

        {{-- Avoid disclaimer --}}
        @if($relationship === 'avoid')
            <div class="rounded-xl border border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/20 p-4">
                <div class="flex gap-3">
                    <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5"/>
                    <p class="text-sm text-amber-800 dark:text-amber-300">
                        If your reason for avoiding this volunteer involves abuse, bullying, harassment, or any other behavior in violation of the
                        Code of Conduct, please also report the issue to leadership so it can be addressed.
                    </p>
                </div>
            </div>
        @endif

        {{-- Recognition --}}
        {{-- @if($user->recognitions->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <x-heroicon-m-trophy class="w-4 h-4 text-yellow-500"/>
                    Recognition
                </h2>
                <div class="space-y-2">
                    @foreach($user->recognitions as $recognition)
                        <div class="flex items-center gap-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 px-3 py-2">
                            <x-heroicon-m-star class="w-4 h-4 text-yellow-500 flex-shrink-0"/>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $recognition->title }}</p>
                                @if($recognition->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $recognition->description }}</p>
                                @endif
                            </div>
                            <span class="ml-auto text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">{{ $recognition->created_at->format('M j, Y') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif --}}

        {{-- Upcoming shifts --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                <x-heroicon-m-calendar-days class="w-4 h-4 text-gray-400"/>
                Upcoming Assignments
            </h2>

            @if($upcomingShifts->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">No upcoming assignments.</p>
            @else
                <div class="space-y-2">
                    @foreach($upcomingShifts as $shift)
                        <div class="flex items-center gap-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 px-3 py-2">
                            <div class="flex-shrink-0 w-10 text-center">
                                <span class="text-xs font-semibold text-brand-green uppercase">{{ $shift->start_time->format('M') }}</span>
                                <span class="block text-lg font-bold text-gray-800 dark:text-gray-200 leading-tight">{{ $shift->start_time->format('j') }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $shift->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $shift->event->name ?? 'Unknown Event' }} · {{ $shift->start_time->format('g:i A') }} – {{ $shift->end_time->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    <x-slot name="right">
        <h1 class="text-base font-semibold leading-6 text-gray-900">Volunteers!</h1>
        <p class="py-2">Strong relationships with your volunteers are key to a successful organization! Here you can tag other volunteers as favorite or avoid.</p>
        <p class="py-2">Marking someone as a <span class="font-semibold">favorite</span> will allow you to easily see their profile and assignments, making it easier to connect and collaborate on shifts. </p>
        <p class="py-2">Marking someone as a <span class="font-semibold">avoid</span> will help you remember to steer clear of them when scheduling. This is only for your use. A user marked avoid will never know or be notified.</p>
    </x-slot>
</x-app-layout>
