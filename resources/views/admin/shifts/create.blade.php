<x-app-layout>
    <x-slot name="header">
        {{ isset($shift) ? 'Edit Shift' : 'Create Shift' }} for Event: {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Cancel
        </a>
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(isset($shift) && $shift->users->isNotEmpty())
                <div class="mb-6 p-4 bg-yellow-100 border-l-4 border-yellow-400 text-yellow-700">
                    ⚠️ Warning: Volunteers have already signed up for this shift. 
                    Be cautious when changing shift times or deleting this shift.
                </div>
            @endif
            <form method="POST"
                action="{{ isset($shift) ? route('admin.events.shifts.update', [$event, $shift]) : route('admin.events.shifts.store', $event) }}">
                @csrf
                @if (isset($shift))
                    @method('PUT')
                @endif
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Shift Name</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="text" name="name" id="name"
                            :value="old('name', $shift->name ?? '')" required />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Volunteers Neeed</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="number" name="max_volunteers"
                            id="max_volunteers" min="1"
                            value="{{ old('max_volunteers', $shift->max_volunteers ?? 1) }}" required />
                        <x-form-validation for="max_volunteers" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Description</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-textarea-input id="notes" rows="6" name="description"
                            class="block w-full text-sm">{{ old('description', $shift->description ?? '') }}</x-textarea-input>
                        <x-form-validation for="description" />
                    </dd>
                </div>

                @php
                    $defaultStart = isset($shift) ? $shift->start_time : $event->start_date;
                    $defaultEnd = isset($shift) ? $shift->end_time : $event->start_date->copy()->addHour();
                @endphp

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Start Date/Time</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="datetime-local" name="start_time" id="start_time"
                            value="{{ old('start_time', $defaultStart->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="start_time" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">End Date/Time</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="datetime-local" name="end_time" id="end_time"
                            value="{{ old('end_time', $defaultEnd->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="end_time" />
                    </dd>
                </div>

                <div class="py-6 flex justify-end space-x-2">
                    <a type="submit" id="submit" href="{{ url()->previous() }}"
                        class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                    <button type="submit"
                        class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        {{ isset($shift) ? 'Update Shift' : 'Create Shift' }}
                    </button>
                </div>
            </form>
        </div>
        {{-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST"
                action="{{ isset($shift) ? route('admin.events.shifts.update', [$event, $shift]) : route('admin.events.shifts.store', $event) }}">
                @csrf
                @if (isset($shift))
                    @method('PUT')
                @endif

                <div class="mb-4">
                    <label for="name" class="block font-medium">Shift Name</label>
                    <input type="text" name="name" id="name" class="w-full border px-3 py-2 rounded"
                        value="{{ old('name', $shift->name ?? '') }}" required>
                </div>

                <div class="mb-4">
                    <label for="description" class="block font-medium">Description</label>
                    <textarea name="description" id="description" class="w-full border px-3 py-2 rounded" rows="3">{{ old('description', $shift->description ?? '') }}</textarea>
                </div>

                

                <div class="mb-4">
                    <label for="start_time" class="block font-medium">Start Time</label>
                    <input type="datetime-local" step="900" name="start_time" id="start_time"
                        class="w-full border px-3 py-2 rounded"
                        value="{{ old('start_time', $defaultStart->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="mb-4">
                    <label for="end_time" class="block font-medium">End Time</label>
                    <input type="datetime-local" step="900" name="end_time" id="end_time"
                        class="w-full border px-3 py-2 rounded"
                        value="{{ old('end_time', $defaultEnd->format('Y-m-d\TH:i')) }}" required>
                </div>

                <div class="mb-6">
                    <label for="max_volunteers" class="block font-medium">Max Volunteers</label>
                    <input type="number" name="max_volunteers" id="max_volunteers" min="1"
                        class="w-full border px-3 py-2 rounded"
                        value="{{ old('max_volunteers', $shift->max_volunteers ?? 1) }}" required>
                </div>

                <button type="submit" class="">
                    {{ isset($shift) ? 'Update Shift' : 'Create Shift' }}
                </button>
            </form> --}}
    </div>
    <x-slot name="right">
        @if (isset($shift))
        <h2 class="text-xl font-semibold mb-3">Volunteers Signed Up ({{ $shift->users->count() }})</h2>
            <ul class="list-disc pl-5 text-sm text-gray-800">
                @forelse ($shift->users as $user)
                    <li class="flex items-center justify-between hover:bg-gray-100 p-3">
                        <span>{{ $user->name }} ({{ $user->email }})</span>
                        <form action="{{ route('admin.events.shifts.remove-volunteer', [$event, $shift, $user]) }}"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-sm hover:underline ml-4">Remove</button>
                        </form>
                    </li>
                @empty
                    <li class="flex items-center justify-between hover:bg-gray-100 p-3">
                        <span class="text-gray-400">No volunteer signups...</span>
                    </li>
                @endforelse
            </ul>
        @else
            <h2 class="text-xl font-semibold mb-3">Volunteers Signed Up (0)</h2>
            <p class="text-gray-500">This is where your signed up volunteers will appear.</p>
        @endif
    </x-slot>
</x-app-layout>
