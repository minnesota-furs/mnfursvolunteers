@props(['disabled' => false, 'label' => '', 'name' => '', 'value' => '', 'min' => '0', 'max' => '', 'step' => '1'])

<input type="number" name="{{ $name }}" value="{{ $value }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
