<x-app-layout>
    <x-slot name="header">
        Edit Log Entry
    </x-slot>

    <x-slot name="actions">
        <a href="{{ url()->previous() }}"
            class="block rounded-md bg-gray-500 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-gray-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Cancel
        </a>
        <button onClick="document.getElementById('form').submit();"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Save
        </button>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="col-span-2">
                {{-- Start Left Column --}}
                <form action="{{ route('hours.update', $hour->id) }}" id="form" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="px-4 sm:px-0">
                        <h3 class="text-base font-semibold leading-7 text-gray-900">Volunteer Contribution Information</h3>
                    </div>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-100">
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">User</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                    @if($selectedUser)
                                        <div>
                                            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                                            <x-text-input class="block w-64 bg-gray-200 text-sm" type="text" name="" id="" value="{{ $selectedUser->name }}" disabled />
                                            <x-form-validation for="user_id" />
                                        </div>
                                    @else
                                        <x-select-input name="user_id" id="user_id" class="block text-sm" required>
                                            <option value="">Select a user</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </x-select-input>
                                        <x-form-validation for="user_id" />
                                    @endif
                                    {{-- <x-text-input id="name" name="name" type="text" class="block w-64 text-sm" :value="old('name')" required autofocus autocomplete="name" /> --}}
                                </dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Short Description</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-64 text-sm" type="text" name="description" id="description" placeholder="Picnic Volunteer" :value="old('description', $hour->description)"/>
                                    <p class="text-xs text-gray-400">This helps us understand what you were doing. You can be descriptive like "Badge Checking at FM2024" or vauge like "Time volunteered during FM2024"</p>
                                    <x-form-validation for="description" />
                                </dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Volunteer Date</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-64 text-sm" type="date" name="volunteer_date" id="volunteer_date" :value="old('volunteer_date', $hour->volunteer_date ? $hour->volunteer_date->format('Y-m-d') : '')"/>
                                    <x-form-validation for="volunteer_date" />
                                </dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Department</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                    <x-select-input name="primary_dept_id" id="primary_dept_id" class="block w-64 text-sm" required>
                                        <option value="">Select Department</option>
                                        <!-- Recent Departments Section -->
                                        @if($recentDepartments->isNotEmpty())
                                            <optgroup label="Recent Departments">
                                                @foreach($recentDepartments as $department)
                                                    <option value="{{ $department->id }}" {{ $selectedUser->primary_department_id == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @foreach($sectors as $sector)
                                            <optgroup label="{{ $sector->name }}">
                                                @foreach($sector->departments as $department)
                                                    <option value="{{ $department->id }}" {{ $selectedUser->primary_dept_id == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </x-select-input>
                                    <p class="text-xs text-gray-400">The volunteers primary department is the default department selected for hour submissions. You may override it, if desired</p>
                                    
                                    <x-form-validation for="primary_dept_id" />
                                </dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Hours</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                    <x-text-input class="block w-64 text-sm" type="number" name="hours" id="hours" step="0.1" placeholder="2.5" :value="old('hours', $hour->hours)" required />
                                    <x-form-validation for="hours" />
                                    <p class="text-xs text-gray-400">Example: An hour and a half would be 1.5</p>
                                </dd>
                            </div>
                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Notes (Optional)</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                    <x-textarea-input id="notes" rows="8" name="notes" class="block w-full text-sm">{{ old('notes', $hour->notes) }}</x-textarea-input>
                                    <p class="text-xs text-gray-400">This is entirely optional but provided if your volunteer shift requires some contextual information.</p>
                                    <x-form-validation for="notes" />
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div class="py-6 flex justify-end space-x-2">
                        <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                        <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

