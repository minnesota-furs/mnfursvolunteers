@props(['icon' => null])

@php
$classes = 'block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        @svg('heroicon-s-' . $icon, 'w-4 inline')
    @endif
    {{ $slot }}
</a>


