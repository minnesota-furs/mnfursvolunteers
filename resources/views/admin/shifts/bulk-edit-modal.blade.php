{{-- Bulk Edit Shifts Modal --}}
<div x-data="bulkEditModal()"
     @open-bulk-edit-modal.window="openModal($event.detail)"
     x-cloak
     class="relative z-50">

    {{-- Modal Overlay --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         @click="open = false">
    </div>

    {{-- Modal Panel --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-50 overflow-y-auto">

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl"
                 @click.stop>

                {{-- Header --}}
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-pencil-square class="h-6 w-6 text-blue-600 dark:text-blue-300"/>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Bulk Edit Shifts</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Editing <span x-text="shiftIds.length"></span> shift<span x-show="shiftIds.length !== 1">s</span>. Only checked fields below will be changed.
                                </p>
                            </div>
                        </div>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('admin.events.shifts.bulk-update', $event) }}" method="POST" class="mt-6">
                        @csrf
                        @method('PATCH')

                        <template x-for="id in shiftIds" :key="id">
                            <input type="hidden" name="shift_ids[]" :value="id">
                        </template>

                        <div class="space-y-4">
                            {{-- Volunteers Needed --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <label class="flex items-start">
                                    <input type="checkbox" name="apply_max_volunteers" value="1"
                                           x-model="applyMaxVolunteers"
                                           class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                    <span class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Update Volunteers Needed</span>
                                </label>
                                <input type="number" name="max_volunteers" min="1"
                                       x-model.number="maxVolunteers"
                                       :disabled="!applyMaxVolunteers"
                                       class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            </div>

                            {{-- Description --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <label class="flex items-start">
                                    <input type="checkbox" name="apply_description" value="1"
                                           x-model="applyDescription"
                                           class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                    <span class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Update Description</span>
                                </label>
                                <textarea name="description" rows="3"
                                          x-model="description"
                                          :disabled="!applyDescription"
                                          class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"></textarea>
                            </div>

                            {{-- Double Hours --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <label class="flex items-start">
                                    <input type="checkbox" name="apply_double_hours" value="1"
                                           x-model="applyDoubleHours"
                                           class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                    <span class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Update Double Hours</span>
                                </label>
                                <label class="mt-2 flex items-center" :class="{ 'opacity-50': !applyDoubleHours }">
                                    <input type="checkbox" name="double_hours" value="1"
                                           x-model="doubleHours"
                                           :disabled="!applyDoubleHours"
                                           class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Shift counts as double hours</span>
                                </label>
                            </div>

                            @feature('accessibility_disclosures')
                                {{-- Accessibility Conflicts --}}
                                <div class="bg-gray-50 p-4 dark:bg-gray-900">
                                <label class="flex items-start">
                                    <input type="checkbox" name="apply_accessibility_conflicts" value="1"
                                           x-model="applyAccessibilityConflicts"
                                           class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                    <span class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Update Accessibility Conflicts</span>
                                </label>
                                <p class="ml-7 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Selected conflicts will replace the current settings on every chosen shift. Leave all options unchecked to clear them.
                                </p>
                                <div class="ml-7 mt-3 grid gap-2 sm:grid-cols-2" :class="{ 'opacity-50': !applyAccessibilityConflicts }">
                                    @foreach ($accessibilityNeeds as $accessibilityNeed)
                                        <label class="flex items-start gap-2">
                                            <input
                                                type="checkbox"
                                                name="accessibility_conflicts[]"
                                                value="{{ $accessibilityNeed }}"
                                                x-model="accessibilityConflicts"
                                                :disabled="!applyAccessibilityConflicts"
                                                class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-700">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $accessibilityNeed }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                </div>
                            @endfeature
                        </div>

                        {{-- Footer --}}
                        <div class="mt-6 flex items-center justify-end gap-x-3">
                            <button type="button"
                                    @click="open = false"
                                    class="rounded-md bg-white dark:bg-gray-700 px-3.5 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="!canSubmit"
                                    class="rounded-md bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                Update <span x-text="shiftIds.length"></span> Shift<span x-show="shiftIds.length !== 1">s</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function bulkEditModal() {
    return {
        open: false,
        shiftIds: [],
        applyMaxVolunteers: false,
        maxVolunteers: 1,
        applyDescription: false,
        description: '',
        applyDoubleHours: false,
        doubleHours: false,
        applyAccessibilityConflicts: false,
        accessibilityConflicts: [],

        openModal(detail) {
            this.shiftIds = detail.ids || [];
            this.applyMaxVolunteers = false;
            this.maxVolunteers = 1;
            this.applyDescription = false;
            this.description = '';
            this.applyDoubleHours = false;
            this.doubleHours = false;
            this.applyAccessibilityConflicts = false;
            this.accessibilityConflicts = [];
            this.open = true;
        },

        get canSubmit() {
            return this.shiftIds.length > 0 &&
                (this.applyMaxVolunteers || this.applyDescription || this.applyDoubleHours || this.applyAccessibilityConflicts);
        }
    }
}
</script>
