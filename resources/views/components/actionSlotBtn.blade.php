@props(['href'])
<a href="{{ $href }}"
    class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100
    focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-green-900
    dark:text-gray-100">
    {{ $slot }}
</a>
