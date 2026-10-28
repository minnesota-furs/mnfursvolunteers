@props(['election'])

@php
    $timeline = $election->timeline();
    $segments = $timeline['segments'];
    $gap = 0.25;

    $progressSummary = $timeline['currentLabel']
        ? 'Election progress: currently in ' . $timeline['currentLabel'] . '.'
        : 'Election progress: not started yet.';
@endphp

<div {{ $attributes }}>
    <div class="relative flex gap-1 pt-4" role="img" aria-label="{{ $progressSummary }}">
        @foreach($segments as $segment)
            <div class="relative h-3 overflow-hidden {{ $loop->first ? 'rounded-l-full' : '' }} {{ $loop->last ? 'rounded-r-full' : '' }}"
                style="flex: {{ $segment['weight'] }} 1 0%"
                title="{{ $segment['label'] }} ({{ $segment['start']->format('M j, Y g:i A') }} to {{ $segment['end']->format('M j, Y g:i A') }})">
                <div class="absolute inset-0 opacity-25 {{ $segment['color'] }}"></div>
                <div class="relative h-full {{ $segment['color'] }}" style="width: {{ $segment['fill'] }}%"></div>
            </div>
        @endforeach
        @if($timeline['nowFraction'] !== null)
            <div class="absolute top-0 flex flex-col items-center -translate-x-1/2" style="left: calc((100% - {{ (count($segments) - 1) * $gap }}rem) * {{ round($timeline['nowFraction'], 5) }} + {{ $timeline['nowSegmentIndex'] * $gap }}rem)">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200 leading-none">Now</span>
                <span class="w-0.5 h-4 bg-gray-700 dark:bg-gray-200"></span>
            </div>
        @endif
    </div>

    <ul class="mt-3 flex flex-col gap-1.5 sm:hidden">
        @foreach($segments as $segment)
            <li class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $segment['color'] }}" aria-hidden="true"></span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $segment['label'] }}</span>
                <span>
                    @if($segment['isMilestone'])
                        {{ $segment['start']->format('M j') }}
                    @else
                        {{ $segment['start']->format('M j') }} – {{ $segment['end']->format('M j') }}
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
    <div class="hidden sm:flex gap-1 mt-2">
        @foreach($segments as $segment)
            <div class="min-w-0 text-xs text-gray-600 dark:text-gray-400" style="flex: {{ $segment['weight'] }} 1 0%">
                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $segment['label'] }}</div>
                <div>
                    @if($segment['isMilestone'])
                        {{ $segment['start']->format('M j') }}
                    @else
                        {{ $segment['start']->format('M j') }} – {{ $segment['end']->format('M j') }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
