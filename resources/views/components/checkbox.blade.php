@props(['disabled' => false, 'label' => '', 'name' => ''])

<!--
NOTE:
HTML <input>s of type "checkbox" will only be included on the form if they are CHECKED (true)
There are ways to specify and force a non-checked value, but this is not supported by all browsers

The workaround to this is to include a hidden input of the same name with value `0`, that way the checkbox will overwrite the value to `1` iff it is checked
This works because when an HTML form has multiple inputs with the same name, only the last one is submitted to the server.
-->
<input type="hidden" name="{{ $name }}" value="0">
<input type="checkbox" name="{{ $name }}" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">

<!-- 
OLD IMPLIMENTATION:
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600', 'type' => 'checkbox',  'value' => 'true']) !!}>
-->
