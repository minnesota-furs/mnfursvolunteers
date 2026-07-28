<div class="mb-6 pt-6 border-t border-gray-200 dark:border-gray-700">
    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Restrictions</h2>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Optionally limit who can check in to this event. Leave everything unchecked to allow any volunteer.</p>

    @if(app_setting('feature_user_tags', false))
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Required Tags</label>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Volunteers must have all selected tags to check in.</p>
            @if(isset($tags) && $tags->isNotEmpty())
                <div class="space-y-2">
                    @foreach ($tags as $tag)
                        <label class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                name="required_tags[]"
                                value="{{ $tag->id }}"
                                {{ (isset($oneOffEvent) && $oneOffEvent->requiredTags->contains($tag->id)) || (is_array(old('required_tags')) && in_array($tag->id, old('required_tags'))) ? 'checked' : '' }}
                                class="rounded border-gray-300 dark:border-gray-600 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            <span class="inline-flex items-center">
                                @if($tag->color)
                                    <span class="inline-block w-3 h-3 rounded mr-1" style="background-color: {{ $tag->color }}"></span>
                                @endif
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $tag->name }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500 dark:text-gray-400 italic">No tags available. <a href="{{ route('admin.tags.create') }}" class="text-brand-green hover:underline">Create tags</a> to use this feature.</p>
            @endif
        </div>
    @endif

    <div class="mb-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department / Sector Restrictions</label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
            Check "Entire sector" to allow any department under it (e.g. all departments within "MNFurs"), or check individual departments. A volunteer qualifies if they match any one selection below.
        </p>
        @if(isset($sectors) && $sectors->isNotEmpty())
            <div class="max-h-80 overflow-y-auto rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                @foreach ($sectors as $sector)
                    @if($sector->departments->isNotEmpty())
                        <div>
                            <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-600/50 flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wide">
                                    {{ $sector->name }}
                                </span>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="required_sectors[]"
                                        value="{{ $sector->id }}"
                                        {{ (isset($oneOffEvent) && $oneOffEvent->requiredSectors->contains($sector->id)) || (is_array(old('required_sectors')) && in_array($sector->id, old('required_sectors'))) ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Entire sector</span>
                                </label>
                            </div>
                            <div class="px-3 py-2 space-y-1.5">
                                @foreach ($sector->departments as $department)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="required_departments[]"
                                            value="{{ $department->id }}"
                                            {{ (isset($oneOffEvent) && $oneOffEvent->requiredDepartments->contains($department->id)) || (is_array(old('required_departments')) && in_array($department->id, old('required_departments'))) ? 'checked' : '' }}
                                            class="rounded border-gray-300 dark:border-gray-600 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $department->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-xs text-gray-500 dark:text-gray-400 italic">No departments available.</p>
        @endif
    </div>
</div>
