<x-app-layout>
    <x-slot name="header">
        {{ __('Create New Event') }}
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}">
                @csrf
                @if(isset($event))
                    @method('PUT')
                @endif
        
                <div class="mb-4">
                    <label for="name" class="block font-medium">Event Name</label>
                    <input type="text" name="name" id="name" class="w-full border px-3 py-2 rounded" value="{{ old('name', $event->name ?? '') }}" required>
                </div>
        
                <div class="mb-4">
                    <label for="description" class="block font-medium">Description</label>
                    <textarea name="description" id="description" class="w-full border px-3 py-2 rounded" rows="4">{{ old('description', $event->description ?? '') }}</textarea>
                </div>
        
                <div class="mb-4">
                    <label for="location" class="block font-medium">Location</label>
                    <input type="text" name="location" id="location" class="w-full border px-3 py-2 rounded" value="{{ old('location', $event->location ?? '') }}">
                </div>
        
                <div class="mb-4">
                    <label for="start_date" class="block font-medium">Start Date</label>
                    <input type="datetime-local" name="start_date" id="start_date" class="w-full border px-3 py-2 rounded" value="{{ old('start_date', isset($event) ? $event->start_date->format('Y-m-d\TH:i') : '') }}" required>
                </div>
        
                <div class="mb-4">
                    <label for="end_date" class="block font-medium">End Date</label>
                    <input type="datetime-local" name="end_date" id="end_date" class="w-full border px-3 py-2 rounded" value="{{ old('end_date', isset($event) ? $event->end_date->format('Y-m-d\TH:i') : '') }}" required>
                </div>
        
                <button type="submit" class="">
                    {{ isset($event) ? 'Update Event' : 'Create Event' }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
