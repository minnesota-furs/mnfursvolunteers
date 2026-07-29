@props(['name', 'description' => null])

<div class="absolute left-0 top-full z-50 mt-2 w-72 max-w-[85vw] rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl p-4 text-left"
     x-show="show"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-end="opacity-0"
     x-cloak
     @click.stop>
    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $name }}</p>
    @if($description)
        <p class="mt-1.5 text-xs text-gray-600 dark:text-gray-400 line-clamp-4">{{ Str::limit($description, 220) }}</p>
    @endif
    <div class="mt-2.5 space-y-1.5 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-2.5">
        {{ $slot }}
    </div>
</div>
