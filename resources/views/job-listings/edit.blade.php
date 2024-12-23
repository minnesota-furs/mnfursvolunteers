<x-app-layout>

    <x-slot name="header">
        {{ __('Create New Department') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{route('job-listings.show', $jobListing->id)}}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
    @if( Auth::user()->isAdmin() )
    <form action="{{ route('job-listings.destroy', $jobListing->id) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                onclick="return confirm('Are you sure you want to delete this job listing?');">
                <x-heroicon-s-trash class="w-4 inline"/> Delete
        </button>
    </form>
        {{-- <a href="{{route('job-listings.destroy', $jobListing->id)}}"
            class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-trash class="w-4 inline"/> Delete
        </a> --}}
    @endif
</x-slot>

    <div class="py-6d">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form class="pb-5" action="{{ route('job-listings.update', $jobListing->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Position Name</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="text" name="position_title" id="position_title" placeholder="Discord Moderator" :value="old('position_title', $jobListing->position_title)" required />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                <!-- Department -->
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Department</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-select-input name="department_id" id="department_id" class="block w-64 text-sm" required>
                            @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ $jobListing->department_id == $department->id ? 'selected' : '' }}>
                                {{ $department->name }} ({{$department->sector->name}})
                            </option>
                            @endforeach
                        </x-select-input>
                        <x-form-validation for="department_id" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Openings</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="number" name="number_of_openings" id="number_of_openings" placeholder="1" :value="old('number_of_openings', $jobListing->number_of_openings)" required />
                        <x-form-validation for="number_of_openings" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Description</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-textarea-input id="notes" rows="14" name="description" class="block w-full text-sm" placeholder="Our discord server is seeking new **moderators**!&#10;&#10;# Responsiblities&#10;- Monitoring for CoC Infractions&#10;- Removing Spammers & Bots&#10;&#10;# Benefits&#10;- Sweet discord role">{{ old('description', $jobListing->description) }}</x-textarea-input>
                        <x-form-validation for="description" />
                        <p class="text-xs text-gray-400">Description for the role. This supports <a class="text-blue-500" href="https://www.markdownguide.org/basic-syntax/"">markdown syntax</a>.</p>
                    </dd>
                </div>

                <!-- Visiblity -->
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Listing Visibility</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-select-input name="visibility" id="visibility" class="block w-64 text-sm">
                            <option value="draft" {{ $jobListing->visibility == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="public" {{ $jobListing->visibility == 'public' ? 'selected' : '' }}>Public</option>
                            <option value="internal" {{ $jobListing->visibility == 'internal' ? 'selected' : '' }}>Internal</option>
                        </x-select-input>
                        <x-form-validation for="visibility" />
                        <p class="text-xs text-gray-400">Who can see this listing: <span class="font-bold">Public</span> means anyone can see it (even guests). <span class="font-bold">Internal</span> is only existing volunteers/staff. <span class="font-bold">Draft</span> is only admins.</p>
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Closing Date</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-64 text-sm" type="date" name="closing_date" id="closing_date" placeholder="Communications" value="{{ old('closing_date', $jobListing->closing_date ? $jobListing->closing_date->format('Y-m-d') : '' ) }}" />
                        <x-form-validation for="closing_date" />
                        <p class="text-xs text-gray-400">The date of which the position will be closed for new applicants. Leave this unset for no expiration/close date.</p>
                    </dd>
                </div>

                {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Department Head</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                        <x-select-input name="department_head_id" id="department_head_id" class="block w-64 text-sm" :value="old('sector_id')">
                            <option value="">Select a Department Head</option>
                            @foreach ($users as $user)
                            <option value="{{ $user->id }}" >
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                        </x-select-input>
                        <x-form-validation for="department_head_id" />
                    </dd>
                </div> --}}

                <div class="py-6 flex justify-end space-x-2">
                    <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                    <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
