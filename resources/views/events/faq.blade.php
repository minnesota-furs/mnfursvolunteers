<x-app-layout>
    @section('title', 'FAQ - ' . $event->name)
    <x-slot name="header">
        {{ $event->name }} &mdash; FAQ
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('volunteer.events.show', $event) }}"
            class="inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors">
            <x-heroicon-m-arrow-left class="w-4 h-4"/>
            Back to Event
        </a>
    </x-slot>

    <div class="mx-auto">
        <div class="px-6 py-8 prose dark:prose-invert max-w-none">
            {!! \Parsedown::instance()->text($event->faq) !!}
        </div>
    </div>
</x-app-layout>
