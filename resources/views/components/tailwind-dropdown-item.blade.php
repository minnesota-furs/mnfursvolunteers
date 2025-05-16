@props(['href', 'title'])
<a href="{{$href}}" {!! $attributes->merge(['class' => 'block px-4 py-2 text-sm hover:bg-gray-50 text-gray-700']) !!} role="menuitem" tabindex="-1">{{$slot}}</a>
