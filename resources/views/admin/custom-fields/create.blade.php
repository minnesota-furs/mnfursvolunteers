<x-app-layout>
    @section('title', 'Create Custom Field')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Custom Field') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.custom-fields.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Field Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('name') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The label that will appear on user forms</p>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Field Key -->
                        <div class="mb-6">
                            <label for="field_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Field Key <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="field_key" id="field_key" value="{{ old('field_key') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono @error('field_key') border-red-500 @enderror"
                                pattern="[a-z0-9_]+">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unique identifier (lowercase letters, numbers, and underscores only)</p>
                            @error('field_key')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Field Type -->
                        <div class="mb-6">
                            <label for="field_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Field Type <span class="text-red-500">*</span>
                            </label>
                            <select name="field_type" id="field_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('field_type') border-red-500 @enderror">
                                <option value="">Select a type...</option>
                                <option value="text" {{ old('field_type') == 'text' ? 'selected' : '' }}>Text (Single line)</option>
                                <option value="textarea" {{ old('field_type') == 'textarea' ? 'selected' : '' }}>Textarea (Multiple lines)</option>
                                <option value="select" {{ old('field_type') == 'select' ? 'selected' : '' }}>Select (Dropdown)</option>
                                <option value="checkbox" {{ old('field_type') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                <option value="date" {{ old('field_type') == 'date' ? 'selected' : '' }}>Date</option>
                                <option value="number" {{ old('field_type') == 'number' ? 'selected' : '' }}>Number</option>
                            </select>
                            @error('field_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Options (for select/checkbox) -->
                        <div class="mb-6" id="options-container" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Options <span class="text-red-500">*</span>
                            </label>
                            <div id="options-list" class="space-y-2">
                                <div class="flex items-center space-x-2 option-item">
                                    <input type="text" name="options[]" value="{{ old('options.0') }}" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Option value">
                                    <button type="button" class="remove-option text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" style="display: none;">
                                        <x-heroicon-o-trash class="w-5 h-5"/>
                                    </button>
                                </div>
                            </div>
                            <button type="button" id="add-option" class="mt-2 text-sm text-brand-green hover:text-green-700 dark:text-green-400 dark:hover:text-green-300">
                                + Add Option
                            </button>
                            @error('options')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Help text shown below the field</p>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Required -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-green shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Make this field required</span>
                            </label>
                        </div>

                        <!-- Is Active -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-green shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active (show on user forms)</span>
                            </label>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-6">
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('sort_order') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to auto-assign to the end</p>
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('admin.custom-fields.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Create Custom Field
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-generate field_key from name
        document.getElementById('name').addEventListener('input', function() {
            const fieldKey = document.getElementById('field_key');
            if (!fieldKey.value || fieldKey.dataset.autogenerated) {
                const key = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s]/g, '')
                    .replace(/\s+/g, '_');
                fieldKey.value = key;
                fieldKey.dataset.autogenerated = 'true';
            }
        });

        document.getElementById('field_key').addEventListener('input', function() {
            if (this.value) {
                delete this.dataset.autogenerated;
            }
        });

        // Show/hide options based on field type
        const fieldTypeSelect = document.getElementById('field_type');
        const optionsContainer = document.getElementById('options-container');
        
        fieldTypeSelect.addEventListener('change', function() {
            if (this.value === 'select' || this.value === 'checkbox') {
                optionsContainer.style.display = 'block';
            } else {
                optionsContainer.style.display = 'none';
            }
        });

        // Trigger on page load in case of validation errors
        if (fieldTypeSelect.value === 'select' || fieldTypeSelect.value === 'checkbox') {
            optionsContainer.style.display = 'block';
        }

        // Add/remove options
        const optionsList = document.getElementById('options-list');
        const addOptionBtn = document.getElementById('add-option');

        addOptionBtn.addEventListener('click', function() {
            const newOption = document.createElement('div');
            newOption.className = 'flex items-center space-x-2 option-item';
            newOption.innerHTML = `
                <input type="text" name="options[]" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Option value">
                <button type="button" class="remove-option text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            `;
            optionsList.appendChild(newOption);
            updateRemoveButtons();
        });

        optionsList.addEventListener('click', function(e) {
            if (e.target.closest('.remove-option')) {
                e.target.closest('.option-item').remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const items = optionsList.querySelectorAll('.option-item');
            items.forEach((item, index) => {
                const removeBtn = item.querySelector('.remove-option');
                removeBtn.style.display = items.length > 1 ? 'block' : 'none';
            });
        }

        updateRemoveButtons();
    </script>
</x-app-layout>
