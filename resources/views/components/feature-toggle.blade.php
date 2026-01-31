@props(['feature', 'label', 'description', 'beta' => false])

<div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
    <div class="flex-1">
        <div class="flex items-center gap-2">
            <label for="feature_{{ $feature }}" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ $label }}
            </label>
            @if($beta)
                <span class="px-2 text-xs font-bold bg-purple-500 text-white rounded uppercase">
                    EXPERIMENTAL
                </span>
            @endif
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
    </div>
    <input type="checkbox" name="feature_{{ $feature }}" id="feature_{{ $feature }}" value="1"
        {{ old('feature_' . $feature, app_setting('feature_' . $feature, true)) ? 'checked' : '' }}
        class="h-4 w-4 rounded border-gray-300 text-brand-green focus:ring-brand-green">
</div>
