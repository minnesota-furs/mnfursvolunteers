<section>

    <form action="{{ route( 'users.update', $user->id ) }}" id="form" method="post">
        @csrf
        @method('patch')
        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- User Information Section --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">Volunteer / User Information</h3>
                                    </div>
                                    <div class="mt-6 border-t border-gray-100 dark:border-gray-700">
                                        <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Full name</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="name" name="name" type="text" class="block w-64 text-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                                    <x-form-validation for="name" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Legal First Name</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="first_name" name="first_name" type="text" class="block w-64 text-sm" :value="old('first_name', $user->first_name)" autofocus autocomplete="first_name" />
                                                    <x-form-validation for="first_name" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Legal Last Name</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="last_name" name="last_name" type="text" class="block w-64 text-sm" :value="old('name', $user->last_name)" autofocus autocomplete="last_name" />
                                                    <x-form-validation for="last_name" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Email address</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-text-input id="email" name="email" type="email" class="block w-64 text-sm" :value="old('email', $user->email)" required autocomplete="email" />
                                                    <x-form-validation for="email" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Status</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input id="active" name="active" class="block text-sm" required>
                                                        <option value="1" {{ old('active', $user->active) == 1 ? 'selected' : '' }}>Active</option>
                                                        <option value="0" {{ old('active', $user->active) == 0 ? 'selected' : '' }}>Inactive</option>
                                                    </x-select-input>
                                                    <x-form-validation for="active" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('active')" />
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">User Type</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input id="admin" name="admin" class="block text-sm" required>
                                                        <option value="0" {{ old('admin', $user->admin) == 0 ? 'selected' : '' }}>User</option>
                                                        <option value="1" {{ old('admin', $user->admin) == 1 ? 'selected' : '' }}>Admin</option>
                                                    </x-select-input>
                                                    <x-form-validation for="admin" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('admin')" />
                                                    <p class="mt-2 text-xs text-gray-500">
                                                        An admin can set granular permissions for users, including themselves. Its recommends only to give this to users you trust.
                                                    </p>
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Notes</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-textarea-input id="notes" rows="8" name="notes" class="block w-full text-sm">{{ old('notes', $user->notes ?? '') }}</x-textarea-input>
                                                    <x-form-validation for="notes" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                                                    <p class="mt-2 text-xs text-gray-500">
                                                        Careful what you write. Notes are public and can be seen by other users.
                                                    </p>
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                {{-- Role Information Section --}}
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100">Role Information</h3>
                                    </div>
                                    <div class="mt-6 border-t border-gray-100 dark:border-gray-700">
                                        <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">App Permissions</dt>
                                                <dd class="mt-1 text-xs leading-6 text-gray-500 sm:col-span-2 sm:mt-0">
                                                    Permissions are granted at the "details" page by a user with admin rights.
                                                </dd>
                                            </div>
                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Primary Sector</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input name="primary_sector_id" id="primary_sector_id" class="block text-sm" :value="old('primary_sector_id', $user->primary_sector_id)">
                                                        <option class="text-gray-400" value="" {{ old('primary_sector_id', $user->primary_sector_id) == null ? 'selected' : '' }}>-None-</option>
                                                        @foreach($sectors as $sector)
                                                            <option value="{{ $sector->id }}" {{ old('primary_sector_id', $user->primary_sector_id) == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                                        @endforeach
                                                    </x-select-input>
                                                    <x-input-error class="mt-2" :messages="$errors->get('primary_sector_id')" />
                                                </dd>
                                            </div>
                                            {{-- <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="text-sm font-medium leading-6 text-gray-900">Primary Dept</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <x-select-input name="primary_dept_id" id="primary_dept_id" class="block w-64 text-sm"> <!-- required> -->
                                                        <option class="text-gray-400" value="" {{ old('primary_department_id', $user->primary_department_id) == null ? 'selected' : '' }}>-None-</option>
                                                        @foreach($departments as $department)
                                                            <option value="{{ $department->id }}" {{ $user->department->id ?? '' == $department->id ? 'selected' : '' }}>
                                                                {{ $department->name }}
                                                            </option>
                                                        @endforeach
                                                        <!-- Options will be populated by JavaScript based on the selected sector -->
                                                    </x-select-input>
                                                </dd>
                                                <x-form-validation for="primary_dept_id" />
                                                <x-input-error class="mt-2" :messages="$errors->get('primary_dept_id')" />
                                            </div> --}}

                                            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                                <dt class="form-label">Departments</dt>
                                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                                    <select 
                                                        name="departments[]" 
                                                        id="departments" 
                                                        size="16"
                                                        multiple 
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm">
                                                        @foreach ($sectors as $sector)
                                                            <optgroup label="{{ $sector->name }}" class="font-bold text-gray-700">
                                                                @foreach ($sector->departments as $department)
                                                                    <option value="{{ $department->id }}"
                                                                        {{ isset($user) && $user->departments->contains($department->id) ? 'selected' : '' }}>
                                                                        {{ $department->name }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                    <p class="mt-2 text-xs text-gray-500">
                                                        Hold down the Ctrl (Windows) or Command (Mac) key to select multiple departments.
                                                    </p>
                                                </dd>
                                                <x-form-validation for="primary_dept_id" />
                                                <x-input-error class="mt-2" :messages="$errors->get('primary_dept_id')" />
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
                                <div class="flex justify-end space-x-2">
                                    <a type="submit" href="{{ url()->previous() }}" class="block rounded-md bg-gray-400 dark:bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 dark:hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400">Cancel</a>
                                    <button type="submit" class="block rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">Save</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
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
</section>
