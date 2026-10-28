@php
    $isEditingThis = $milestone === null ? old('milestone_id') === null : (int) old('milestone_id') === $milestone->id;
    $value = fn (string $field, $default = null) => $isEditingThis ? old($field, $default) : $default;
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="{{ $idPrefix }}-title" value="Name" />
        <input type="text" id="{{ $idPrefix }}-title" name="title" required maxlength="255"
            value="{{ $value('title', $milestone?->title) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 sm:text-sm">
        @if($isEditingThis)<x-input-error :messages="$errors->get('title')" class="mt-1" />@endif
    </div>
    <div>
        <x-input-label for="{{ $idPrefix }}-starts-at" value="Date and time" />
        <input type="datetime-local" id="{{ $idPrefix }}-starts-at" name="starts_at" required
            value="{{ $value('starts_at', $milestone?->starts_at?->format('Y-m-d\TH:i')) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 sm:text-sm">
        @if($isEditingThis)<x-input-error :messages="$errors->get('starts_at')" class="mt-1" />@endif
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="{{ $idPrefix }}-location" value="Location (optional)" />
        <input type="text" id="{{ $idPrefix }}-location" name="location" maxlength="255"
            value="{{ $value('location', $milestone?->location) }}"
            placeholder="e.g. Main Hall, or a video call link"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 sm:text-sm">
        @if($isEditingThis)<x-input-error :messages="$errors->get('location')" class="mt-1" />@endif
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="{{ $idPrefix }}-description" value="Description (optional)" />
        <p class="text-xs text-gray-500 dark:text-gray-400">Supports Markdown, like **bold**, *italic*, lists and [links](https://example.com).</p>
        <textarea id="{{ $idPrefix }}-description" name="description" rows="3" maxlength="5000"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 sm:text-sm">{{ $value('description', $milestone?->description) }}</textarea>
        @if($isEditingThis)<x-input-error :messages="$errors->get('description')" class="mt-1" />@endif
    </div>
</div>
