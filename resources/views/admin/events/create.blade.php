<x-app-layout>
    <x-slot name="header">
        {{ isset($event) ? 'Edit Event: '. $event->name : 'Create Event' }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ url()->previous() }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
        @if(isset($event))
            <button
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                onclick="copyToClipboard('{{ route('vol-listings-public.show', $event->id) }}')">
                <x-heroicon-s-link class="w-4 inline"/> Copy Public URL
            </button>
            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        onclick="return confirm('Are you sure you want to delete this event? This cannot be undone.');">
                        <x-heroicon-s-trash class="w-4 inline"/> Delete
                </button>
            </form>
        @endif
    </x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}">
                @csrf
                @if(isset($event))
                    @method('PUT')
                @endif
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Event Name</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="text" name="name" id="name"
                            :value="old('name', $event->name ?? '')" required />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Volunteers Neeed</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="number" name="max_volunteers"
                            id="max_volunteers" min="1"
                            value="{{ old('max_volunteers', $shift->max_volunteers ?? 1) }}" required />
                        <x-form-validation for="max_volunteers" />
                    </dd>
                </div> --}}

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Description</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-textarea-input id="notes" rows="6" name="description"
                            class="block w-full text-sm">{{ old('description', $event->description ?? '') }}</x-textarea-input>
                        <x-form-validation for="description" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Location</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="text" name="location" id="location"
                            :value="old('location', $event->location ?? '')" required />
                        <x-form-validation for="location" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Start Date/Time</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="datetime-local" name="start_date" id="start_date"
                            value="{{ old('start_date', isset($event) ? $event->start_date->format('Y-m-d\TH:i') : now()->setTime(10, 0)->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="start_date" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">End Date/Time</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="datetime-local" name="end_date" id="end_date"
                            value="{{ old('end_date', isset($event) ? $event->end_date->format('Y-m-d\TH:i') : now()->setTime(20, 0)->format('Y-m-d\TH:i')) }}" required />
                        <x-form-validation for="end_date" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Signups Open Date</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="datetime-local" name="signup_open_date" id="signup_open_date"
                        value="{{ old('signup_open_date', isset($event) && $event->signup_open_date ? $event->signup_open_date->format('Y-m-d\TH:i') : '') }}" />
                        <x-form-validation for="signup_open_date" />
                        <p class="text-gray-500 text-sm mt-1">Leave blank to allow signups immediately.</p>
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Visibility</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-select-input name="visibility" id="visibility" class="block w-64 text-sm">
                            <option value="draft" {{ old('visibility', $event->visibility ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="unlisted" {{ old('visibility', $event->visibility ?? '') == 'unlisted' ? 'selected' : '' }}>Unlisted</option>
                            <option value="public" {{ old('visibility', $event->visibility ?? '') == 'public' ? 'selected' : '' }}>Public</option>
                        </x-select-input>
                        <x-form-validation for="sector_id" />
                    </dd>
                </div>

                <div class="py-6 flex justify-end space-x-2">
                    <a type="submit" id="submit" href="{{ url()->previous() }}"
                        class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                    <button type="submit"
                        class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        {{ isset($event) ? 'Update Event' : 'Create Event' }}
                    </button>
                </div>
            </form>
            
        </div>
    </div>
    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('Public URL copied to clipboard!');
            }, function(err) {
                console.error('Failed to copy URL: ', err);
            });
        }
    </script>
</x-app-layout>
