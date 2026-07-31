<x-app-layout>
    <x-slot name="header">
        Create a Staffing Coverage Roster
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('departments.manage', $department) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
    </x-slot>

    <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('departments.staffing-rosters.store', $department) }}">
            @csrf

            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $department->name }} staffing roster</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        This event will be restricted to {{ $department->name }} and you will be its owner.
                    </p>
                </div>

                <div class="grid gap-6 p-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="roster_name" value="Roster name" />
                        <x-text-input id="roster_name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $department->name.' Coverage')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Recommended format: event shorthand and year, followed by the department. For example, “FM2026-MainStage Staff”.
                        </p>
                    </div>

                    <div>
                        <x-input-label for="roster_start_date" value="Coverage starts" />
                        <x-text-input id="roster_start_date" name="start_date" type="datetime-local" class="mt-1 block w-full"
                            :value="old('start_date', now()->addDay()->startOfHour()->format('Y-m-d\\TH:i'))" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="roster_end_date" value="Coverage ends" />
                        <x-text-input id="roster_end_date" name="end_date" type="datetime-local" class="mt-1 block w-full"
                            :value="old('end_date', now()->addDay()->startOfHour()->addHours(8)->format('Y-m-d\\TH:i'))" required />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="roster_location" value="Location" />
                        <x-text-input id="roster_location" name="location" type="text" class="mt-1 block w-full" :value="old('location')" />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="roster_visibility" value="Visibility" />
                        <select id="roster_visibility" name="visibility"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="internal" @selected(old('visibility', 'internal') === 'internal')>Internal</option>
                            <option value="draft" @selected(old('visibility') === 'draft')>Draft</option>
                        </select>
                        <x-input-error :messages="$errors->get('visibility')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Internal rosters are visible to signed-in volunteers. Draft rosters are visible only to event managers.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="roster_description" value="Description" />
                        <x-textarea-input id="roster_description" name="description" rows="5"
                            class="mt-1 block w-full">{{ old('description') }}</x-textarea-input>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900/50">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Automatically configured</h3>
                    <dl class="mt-3 grid gap-4 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Department restriction</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $department->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Owner</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ auth()->user()->displayName() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Collaborating department heads</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                {{ $department->heads->where('id', '!=', auth()->id())->map->displayName()->join(', ') ?: 'None' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('departments.manage', $department) }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <x-primary-button>Create roster and add shifts</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
