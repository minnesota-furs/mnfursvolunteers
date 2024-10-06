<x-app-layout>
    <x-slot name="header">
        {{ __('Edit User: ') }} {{$user->name}}
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

    <form action="{{ route('users.update', $user->id) }}" id="form" method="POST">
        @csrf
        @method('PUT')
        <div class="py-4">
            <div class="max-w-7xl mx-auto">
                <div class="">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                {{-- Start Left Column --}}
                                <div>
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-base font-semibold leading-7 text-gray-900">Volunteer / User Information</h3>
                                    </div>
                                    <div class="mt-6 border-t border-gray-100">
                                        <dl class="divide-y divide-gray-100">
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Full name</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="name" name="name" type="text" class="block w-64 text-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                                    <x-form-validation for="name" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Email address</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="email" name="email" type="email" class="block w-64 text-sm" :value="old('email', $user->email)" required autocomplete="email" />
                                                    <x-form-validation for="email" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Status</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input id="active" name="active" class="block text-sm" required>
                                                        <option value="1" {{ old('active', $user->active) == 1 ? 'selected' : '' }}>Active</option>
                                                        <option value="0" {{ old('active', $user->active) == 0 ? 'selected' : '' }}>Inactive</option>
                                                    </x-select-input>
                                                    <x-form-validation for="active" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Notes</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-textarea-input id="notes" rows="8" name="notes" class="block w-full text-sm">{{ old('notes', $user->notes ?? '') }}</x-textarea-input>
                                                    <x-form-validation for="notes" />
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div>
                                {{-- Start Right Column --}}
                                <div>
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-base font-semibold leading-7 text-gray-900">Role Information</h3>
                                        {{-- <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Information involving their staff involvement with the group</p> --}}
                                    </div>
                                    <div class="mt-6 border-t border-gray-100">
                                        <dl class="divide-y divide-gray-100">
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Primary Sector</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input name="primary_sector_id" id="primary_sector_id" type="sector" class="block text-sm" :value="old('primary_sector_id', $user->primary_sector_id)">
                                                        <option class="text-gray-400" value=null {{ old('primary_sector_id', $user->primary_sector_id) == null ? 'selected' : '' }}>-None-</option>
                                                        @foreach($sectors as $sector)
                                                            <option value="{{ $sector->id }}" {{ old('primary_sector_id', $user->primary_sector_id) == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                                        @endforeach
                                                    </x-select-input>
                                                    <x-form-validation for="primary_sector_id" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Primary Dept</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input name="primary_dept_id" id="primary_dept_id" class="block w-64 text-sm" required>
                                                        <option value="">Select Department</option>
                                                        @foreach($departments as $department)
                                                            <option value="{{ $department->id }}" {{ $user->department->id ?? '' == $department->id ? 'selected' : '' }}>
                                                                {{ $department->name }}
                                                            </option>
                                                        @endforeach
                                                        <!-- Options will be populated by JavaScript based on the selected sector -->
                                                    </x-select-input>
                                                </dd>
                                                <x-form-validation for="primary_dept_id" />
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Total Hours</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    {{$user->totalVolunteerHours()}} hours
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="py-6 flex justify-end space-x-2">
                            <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                            <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // When the sector dropdown changes
            $('#primary_sector_id').on('change', function() {
                var sectorId = $(this).val();  // Get the selected sector ID
                populateDepartments(sectorId);  // Populate the departments based on the sector
            });
        });

        function populateDepartments(sectorId) {
            // Clear and disable the department dropdown while loading
            $('#primary_dept_id').empty().append('<option value="">Loading...</option>').prop('disabled', true);

            // If a sector is selected, fetch the departments
            if (sectorId) {
                $.ajax({
                    url: '{{ route("get-departments-by-sector") }}',  // The route to fetch departments
                    type: 'GET',
                    data: { sector_id: sectorId },  // Send the selected sector ID
                    success: function(data) {
                        // Clear and enable the department dropdown
                        $('#primary_dept_id').empty().append('<option value="">Select Department</option>');

                        // Populate the dropdown with the returned departments
                        $.each(data, function(index, department) {
                            var isSelected = department.id == '{{ $user->department_id }}' ? 'selected' : ''; // Preselect the department
                            $('#primary_dept_id').append('<option value="' + department.id + '" ' + isSelected + '>' + department.name + '</option>');
                        });

                        $('#primary_dept_id').prop('disabled', false);  // Enable the dropdown
                    },
                    error: function() {
                        alert('Error fetching departments.');
                        $('#primary_dept_id').prop('disabled', false);  // Re-enable the dropdown on error
                    }
                });
            } else {
                // If no sector is selected, reset the department dropdown
                $('#primary_dept_id').empty().append('<option value="">Select Department</option>').prop('disabled', true);
            }
        }
    </script>


</x-app-layout>
