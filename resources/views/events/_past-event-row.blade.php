@php
    $hasLimitations = $event->requiredTags->isNotEmpty() || $event->requiredDepartments->isNotEmpty();
    $dimmed = $dimmed ?? false;
@endphp

<a href="{{ route('volunteer.events.show', $event) }}"
   class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors first:rounded-t-xl last:rounded-b-xl
          {{ $dimmed ? 'opacity-75' : '' }}">
    <div class="min-w-0 flex-1">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate block">
            {{ $event->name }}
        </span>
        @if($hasLimitations)
            <div class="mt-1 flex flex-wrap gap-1">
                @foreach($event->requiredDepartments as $dept)
                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-600 dark:text-gray-400">
                        {{ $dept->name }}
                    </span>
                @endforeach
                @foreach($event->requiredTags as $tag)
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                          @if($tag->color)
                              style="background-color: {{ $tag->color }}22; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}55;"
                          @else
                              style="background-color: #e5e7eb; color: #374151;"
                          @endif>
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        @if($dimmed)
            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-900/20 px-2 py-0.5 text-xs font-medium text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800">
                <x-heroicon-m-no-symbol class="w-3 h-3"/>
                Not eligible
            </span>
        @endif
        <span class="text-xs text-gray-400 dark:text-gray-500">
            {{ $event->start_date->format('M j') }}
            @if($event->isMultiDay()) – {{ $event->end_date->format('M j') }}@endif,
            {{ $event->start_date->format('Y') }}
        </span>
    </div>
</a>
