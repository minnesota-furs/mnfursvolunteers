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
            @if(!isset($event))
                <div class="mb-6 rounded-md bg-blue-50 p-4 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Creating a New Event</h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <p>After creating the event, you'll be able to add collaborators who can help manage the event. You can add editors from the event's management page.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <form method="POST" id="main" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}">
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
                            :value="old('location', $event->location ?? '')" />
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
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900">Signups Open Date</dt>
                        <p class="text-gray-500 text-sm mt-1">When users start picking up shifts.</p>
                    </div>
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
                        <x-form-validation for="visibility" />
                        <p class="text-gray-500 text-sm mt-1">Public will show up on to everyone, including guests.</p>
                        <p class="text-gray-500 text-sm mt-1">Unlisted lets you link it to people, but isn't organically discoverable.</p>
                        <p class="text-gray-500 text-sm mt-1">Draft is just when you are working on it and don't want to publish it yet.</p>
                    </dd>
                </div>

                @if(isset($event) && auth()->user()->isAdmin())
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <div>
                            <dt class="text-sm font-medium leading-6 text-gray-900">Event Creator</dt>
                            <p class="text-gray-500 text-sm mt-1">Only admins can change this.</p>
                        </div>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <x-select-input name="created_by" id="created_by" class="block w-64 text-sm">
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}" {{ old('created_by', $event->created_by) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-form-validation for="created_by" />
                            <p class="text-gray-500 text-sm mt-1">The creator has full control over the event and can manage collaborators.</p>
                        </dd>
                    </div>
                @endif

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900">Hide Past Shifts</dt>
                        <p class="text-gray-500 text-sm mt-1">Default hide shifts that have past. This is useful for multiday events.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-checkbox-input class="block w-64 text-sm" name="hide_past_shifts" id="hide_past_shifts"
                            checked="{{ old('hide_past_shifts', isset($event) ? $event->hide_past_shifts : false) }}" />
                        <x-form-validation for="hide_past_shifts" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900">Automatically Credit Hours</dt>
                        <p class="text-gray-500 text-sm mt-1">Credit volunteer hours the day after the event completes. If disabled, you will need to manually approve each volunteers hours for crediting.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-checkbox-input class="block w-64 text-sm" name="auto_credit_hours" id="auto_credit_hours"
                            checked="{{ old('auto_credit_hours', isset($event) ? $event->auto_credit_hours : false) }}" />
                        <x-form-validation for="auto_credit_hours" />
                    </dd>
                </div>

                <div class="py-6 flex justify-end space-x-2">
                    <a type="submit" id="submit" href="{{ url()->previous() }}"
                        class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                    <button type="submit" form="main"
                        class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        {{ isset($event) ? 'Update Event' : 'Create Event' }}
                    </button>
                </div>
                
            </form>

            @if(isset($event))
                <h2 class="text-2xl font-semibold text-gray-900 mt-10 mb-4">Mass Shift Creation (Advanced)</h2>
                <x-shifts.csv-example />
                
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">CSV Import</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <form id="import" action="{{ route('admin.events.shifts.import', $event) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input class="text-sm p-1.5 text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                            type="file" name="csv_file" accept=".csv" required>
                            <button type="submit" class="inline rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green" form="import">Import Shifts CSV</button>
                        </form>
                        <x-form-validation for="location" />
                    </dd>
                </div>
                @endif
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
