<x-app-layout>
    @section('title', 'Users - Edit ' . $user->name)
    <x-slot name="header">
        {{ __('Edit User: ') }} {{$user->name}}
    </x-slot>

    <x-slot name="actions">
        @if( Auth::user()->isAdmin() && Auth::user()->id != $user->id )
        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    onclick="return confirm('Are you sure you want to delete this user?');">
                    <x-heroicon-s-trash class="w-4 inline"/> Delete
            </button>
        </form>
        @endif
        <a href="{{ url()->previous() }}"
            class="block rounded-md bg-gray-500 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-gray-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Cancel
        </a>
        <button onClick="document.getElementById('form').submit();"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Save
        </button>
    </x-slot>

    @include('users.partials.edit-user-form')

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
