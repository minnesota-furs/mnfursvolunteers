@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'border-t border-gray-200 dark:border-gray-700 mt-6 pt-4']) }}>
    <h3 class="text-xl font-semibold leading-7 text-gray-900 dark:text-gray-100">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $description }}</p>
    @endif
</div>
