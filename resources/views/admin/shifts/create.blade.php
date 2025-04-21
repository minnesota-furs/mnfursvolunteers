<x-app-layout>
    <x-slot name="header">
        {{ isset($shift) ? 'Edit Shift' : 'Create Shift' }} for Event: {{ $event->name }}
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($shift) ? route('admin.events.shifts.update', [$event, $shift]) : route('admin.events.shifts.store', $event) }}">
                @csrf
                @if(isset($shift))
                    @method('PUT')
                @endif
        
                <div class="mb-4">
                    <label for="name" class="block font-medium">Shift Name</label>
                    <input type="text" name="name" id="name" class="w-full border px-3 py-2 rounded" value="{{ old('name', $shift->name ?? '') }}" required>
                </div>
        
                <div class="mb-4">
                    <label for="description" class="block font-medium">Description</label>
                    <textarea name="description" id="description" class="w-full border px-3 py-2 rounded" rows="3">{{ old('description', $shift->description ?? '') }}</textarea>
                </div>
        
                @php
                    $defaultStart = isset($shift) ? $shift->start_time : $event->start_date;
                    $defaultEnd = isset($shift) ? $shift->end_time : $event->start_date->copy()->addHour();
                @endphp
        
                <div class="mb-4">
                    <label for="start_time" class="block font-medium">Start Time</label>
                    <input type="datetime-local" name="start_time" id="start_time" class="w-full border px-3 py-2 rounded" value="{{ old('start_time', $defaultStart->format('Y-m-d\TH:i')) }}" required>
                </div>
        
                <div class="mb-4">
                    <label for="end_time" class="block font-medium">End Time</label>
                    <input type="datetime-local" name="end_time" id="end_time" class="w-full border px-3 py-2 rounded" value="{{ old('end_time', $defaultEnd->format('Y-m-d\TH:i')) }}" required>
                </div>
        
                <div class="mb-6">
                    <label for="max_volunteers" class="block font-medium">Max Volunteers</label>
                    <input type="number" name="max_volunteers" id="max_volunteers" min="1" class="w-full border px-3 py-2 rounded" value="{{ old('max_volunteers', $shift->max_volunteers ?? 1) }}" required>
                </div>
        
                <button type="submit" class="">
                    {{ isset($shift) ? 'Update Shift' : 'Create Shift' }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
