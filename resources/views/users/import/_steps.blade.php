{{-- Shared wizard step indicator --}}
{{-- Usage: @include('users.import._steps', ['current' => 1]) --}}

@php
$steps = [
    1 => 'Upload CSV',
    2 => 'Map Columns',
    3 => 'Confirm & Import',
];
@endphp

<nav aria-label="Import wizard steps" class="flex items-center justify-center py-4">
    @foreach ($steps as $num => $label)
        @php
            $isDone   = $num < $current;
            $isActive = $num === $current;
        @endphp

        {{-- Connector line --}}
        @if ($num > 1)
            <div class="flex-1 h-0.5 mx-2 {{ $num <= $current ? 'bg-brand-green' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
        @endif

        <div class="flex flex-col items-center">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                {{ $isDone   ? 'bg-brand-green text-white' : '' }}
                {{ $isActive ? 'bg-brand-green text-white ring-4 ring-brand-green/20' : '' }}
                {{ !$isDone && !$isActive ? 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : '' }}">
                @if ($isDone)
                    <x-heroicon-s-check class="w-4 h-4" />
                @else
                    {{ $num }}
                @endif
            </div>
            <span class="mt-1 text-xs font-medium
                {{ $isActive ? 'text-brand-green' : 'text-gray-400 dark:text-gray-500' }}">
                {{ $label }}
            </span>
        </div>
    @endforeach
</nav>
