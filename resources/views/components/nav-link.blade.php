@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 font-black border-white dark:border-brand-green text-sm font-medium leading-5 text-gray-100 dark:text-gray-100 focus:outline-none focus:border-white transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent hover:underline text-sm font-medium leading-5 text-gray-100 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-300 hover:border-white/25 dark:hover:border-gray-700 focus:outline-none focus:text-gray-100 dark:focus:text-gray-300 focus:border-gray-100 dark:focus:border-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
