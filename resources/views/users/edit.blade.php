<x-app-layout>
    @section('title', 'Users - Edit ' . $user->name)
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-white">Edit User: {{ $user->name }}</h1>
                <div class="mt-2 flex flex-wrap gap-2">
                    @if($user->active)
                        <span class="inline-flex items-center rounded-full bg-white/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-white ring-1 ring-inset ring-white/30">
                            <svg class="mr-1.5 h-1.5 w-1.5 fill-current" viewBox="0 0 6 6" aria-hidden="true">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-yellow-500/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-yellow-100 ring-1 ring-inset ring-yellow-400/30">
                            <svg class="mr-1.5 h-1.5 w-1.5 fill-current" viewBox="0 0 6 6" aria-hidden="true">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Inactive
                        </span>
                    @endif
                    @if($user->isAdmin())
                        <span class="inline-flex items-center rounded-full bg-red-500/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-red-100 ring-1 ring-inset ring-red-400/30">
                            <x-heroicon-s-shield-check class="mr-1.5 h-3 w-3" />
                            Administrator
                        </span>
                    @endif
                    @if($user->vol_code)
                        <span class="inline-flex items-center rounded-full bg-blue-500/20 backdrop-blur-sm px-3 py-1 text-xs font-medium text-blue-100 ring-1 ring-inset ring-blue-400/30">
                            <x-heroicon-o-identification class="mr-1.5 h-3 w-3" />
                            {{ $user->vol_code }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="flex items-center gap-3">
            @if( Auth::user()->isAdmin() && Auth::user()->id != $user->id )
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                        <x-heroicon-s-trash class="mr-2 h-4 w-4"/>
                        Delete User
                    </button>
                </form>
            @endif
            
            <a href="{{ route('users.show', $user->id) }}"
                class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                <x-heroicon-m-x-mark class="mr-2 h-4 w-4" />
                Cancel
            </a>
            
            <button type="submit" form="user-form"
                class="inline-flex items-center rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                <x-heroicon-m-check class="mr-2 h-4 w-4" />
                Save Changes
            </button>
        </div>
    </x-slot>

    @include('users.partials.edit-user-form-redesigned')

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
