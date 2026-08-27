{{-- Name --}}
<div class="mb-6">
    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Category Name <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}" required
        placeholder="e.g. Badge Checker, Setup, Teardown"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('name') border-red-500 @enderror">
    <x-form-validation for="name" />
</div>

{{-- Color --}}
<div class="mb-6">
    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Color <span class="text-gray-500 text-xs">(hex code, e.g., #3B82F6)</span>
    </label>
    <div class="flex items-center space-x-2">
        <input type="color" name="color" id="color" value="{{ old('color', $category->color ?? '#3B82F6') }}"
            class="h-10 w-20 rounded-md border border-gray-300 dark:border-gray-600 cursor-pointer">
        <input type="text" id="color_text" value="{{ old('color', $category->color ?? '#3B82F6') }}"
            class="mt-0 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            readonly>
    </div>
    <x-form-validation for="color" />
</div>

{{-- Description --}}
<div class="mb-6">
    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Description
    </label>
    <textarea name="description" id="description" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('description') border-red-500 @enderror">{{ old('description', $category->description ?? '') }}</textarea>
    <x-form-validation for="description" />
</div>

{{-- Actions --}}
<div class="flex items-center justify-end space-x-4">
    <a href="{{ route('admin.events.categories.index', $event) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
        Cancel
    </a>
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
        {{ isset($category) ? 'Update Category' : 'Create Category' }}
    </button>
</div>

<script>
    (function () {
        const colorPicker = document.getElementById('color');
        const colorText = document.getElementById('color_text');

        colorPicker.addEventListener('input', function () {
            colorText.value = this.value;
        });
    })();
</script>
