@props(['time', 'format' => 'M j, Y g:i A'])

@php
    $userTz = user_timezone();
    $appTz = app_timezone();
    $displayTime = $time->copy()->setTimezone($userTz);
    $isConverted = $userTz !== $appTz;
@endphp

@if($isConverted)
    <span class="relative inline-block" x-data="{ show: false, timer: null }"
          @mouseenter="timer = setTimeout(() => show = true, 150)"
          @mouseleave="clearTimeout(timer); show = false">
        <span tabindex="0"
              @focus="show = true" @blur="show = false"
              class="inline-flex items-center gap-0.5 cursor-help align-baseline text-brand-green dark:text-emerald-400 border-b border-dotted border-brand-green/60 dark:border-emerald-400/60">
            <x-heroicon-m-globe-alt class="w-3.5 h-3.5 flex-shrink-0" />{{ $displayTime->format($format) }}
        </span>

        <div class="absolute left-0 top-full z-50 mt-1.5 w-max max-w-[16rem] rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg px-3 py-2 text-xs"
             x-show="show"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-end="opacity-0"
             x-cloak
             @click.stop>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">App's Native Time</p>
            <p class="mt-0.5 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $time->copy()->setTimezone($appTz)->format('M j, Y g:i A T') }}</p>
        </div>
    </span>
@else
    {{ $displayTime->format($format) }}
@endif
