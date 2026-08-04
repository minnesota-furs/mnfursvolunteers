@csrf
@if(isset($announcement))
    @method('PUT')
@endif

<div class="flex flex-col gap-6" x-data="{ volunteersOnly: @js((bool) old('volunteers_only', $announcement->volunteers_only ?? false)) }">
    <div>
        <x-input-label for="title" value="Announcement title" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
            :value="old('title', $announcement->title ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="body" value="Announcement" />
        <textarea id="body" name="body" rows="8" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700 dark:text-white">{{ old('body', $announcement->body ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Markdown supported. Raw HTML is escaped for safety.</p>
        <x-input-error :messages="$errors->get('body')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="expires_at" value="Expiration date and time (optional)" />
        <x-text-input id="expires_at" name="expires_at" type="datetime-local" class="mt-1 block w-full max-w-md"
            :value="old('expires_at', isset($announcement) && $announcement->expires_at ? $announcement->expires_at->timezone(user_timezone())->format('Y-m-d\\TH:i') : '')" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The announcement automatically disappears from dashboards at this time. Leave blank for no expiration.</p>
        <x-input-error :messages="$errors->get('expires_at')" class="mt-2" />
    </div>

    <div class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
        <label class="flex cursor-pointer items-start gap-3">
            <input type="hidden" name="volunteers_only" value="0">
            <input type="checkbox" name="volunteers_only" value="1" x-model="volunteersOnly"
                class="mt-0.5 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600">
            <span>
                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Only show to volunteers without a department</span>
                <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">Use this instead of department or sector restrictions. Users assigned to any department will not see the announcement.</span>
            </span>
        </label>
        <x-input-error :messages="$errors->get('volunteers_only')" class="mt-2" />
    </div>

    <div x-bind:class="{ 'opacity-50': volunteersOnly }">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Department / Sector Restrictions</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Select entire sectors or individual departments. A user sees the announcement if they belong to any selected audience. Leave everything unchecked to show it to everyone.
        </p>

        <div class="mt-3 max-h-96 overflow-y-auto rounded-md border border-gray-300 bg-white divide-y divide-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:divide-gray-600">
            @forelse($sectors as $sector)
                <div>
                    <div class="flex items-center justify-between gap-2 bg-gray-50 px-3 py-2 dark:bg-gray-600/50">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">{{ $sector->name }}</span>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="checkbox" name="sectors[]" value="{{ $sector->id }}"
                                x-bind:disabled="volunteersOnly"
                                {{ in_array($sector->id, old('sectors', isset($announcement) ? $announcement->sectors->pluck('id')->all() : [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Entire sector</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-2 px-3 py-3 sm:grid-cols-2">
                        @foreach($sector->departments as $department)
                            <label class="flex cursor-pointer items-center gap-2">
                                <input type="checkbox" name="departments[]" value="{{ $department->id }}"
                                    x-bind:disabled="volunteersOnly"
                                    {{ in_array($department->id, old('departments', isset($announcement) ? $announcement->departments->pluck('id')->all() : [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $department->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="p-4 text-sm italic text-gray-500 dark:text-gray-400">No sectors or departments are available.</p>
            @endforelse
        </div>
        <x-input-error :messages="$errors->get('departments.*')" class="mt-2" />
        <x-input-error :messages="$errors->get('sectors.*')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ isset($announcement) ? 'Save Announcement' : 'Create Announcement' }}</x-primary-button>
        <a href="{{ route('announcements.index') }}" class="text-sm text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
    </div>
</div>
