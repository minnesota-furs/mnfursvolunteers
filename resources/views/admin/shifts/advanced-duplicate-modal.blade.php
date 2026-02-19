{{-- Advanced Duplicate Modal --}}
<div x-data="advancedDuplicateModal()" 
     @open-duplicate-modal.window="openModal($event.detail)"
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
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                 @click.stop>
                
                {{-- Header --}}
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Advanced Duplicate Shift</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create multiple shift copies with custom settings</p>
                            </div>
                        </div>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form :action="`{{ route('admin.events.shifts.index', $event) }}/${shiftId}/advanced-duplicate`" method="POST" class="mt-6">
                        @csrf
                        
                        <div class="space-y-6">
                            {{-- Recurrence Settings --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Recurrence Settings
                                </h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="recurrence" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Number of Duplicates
                                        </label>
                                        <input type="number" 
                                               name="recurrence" 
                                               id="recurrence" 
                                               x-model.number="recurrence"
                                               @input="generatePreview"
                                               min="1" 
                                               max="100"
                                               required
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum: 100 duplicates</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Date Increment
                                        </label>
                                        <div class="flex gap-2">
                                            <input type="number" 
                                                   name="interval" 
                                                   x-model.number="interval"
                                                   @input="generatePreview"
                                                   min="1" 
                                                   max="365"
                                                   required
                                                   class="block w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <select name="interval_unit" 
                                                    x-model="intervalUnit"
                                                    @change="generatePreview"
                                                    class="block flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                <option value="days">Day(s)</option>
                                                <option value="weeks">Week(s)</option>
                                                <option value="hours">Hour(s)</option>
                                            </select>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Time between each duplicate</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Naming Pattern --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Naming Pattern
                                </h4>
                                
                                <div class="space-y-3">
                                    <select name="naming_pattern" 
                                            x-model="namingPattern"
                                            @change="generatePreview"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="sequence">Original Name + (Number)</option>
                                        <option value="start_time">Original Name + Start Time</option>
                                        <option value="prefix">Prefix + Original Name</option>
                                        <option value="suffix">Original Name + Suffix</option>
                                        <option value="prefix_sequence">Prefix + Original Name + (Number)</option>
                                        <option value="suffix_sequence">Original Name + (Number) + Suffix</option>
                                        <option value="custom">Custom Pattern</option>
                                        <option value="none">Keep Original Name</option>
                                    </select>

                                    <div x-show="['prefix', 'prefix_sequence', 'custom'].includes(namingPattern)" x-transition>
                                        <label for="custom_prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Prefix Text
                                        </label>
                                        <input type="text" 
                                               name="custom_prefix" 
                                               id="custom_prefix"
                                               x-model="customPrefix"
                                               @input="generatePreview"
                                               placeholder="e.g., Day, Session, Round"
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>

                                    <div x-show="['suffix', 'suffix_sequence'].includes(namingPattern)" x-transition>
                                        <label for="custom_suffix" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Suffix Text
                                        </label>
                                        <input type="text" 
                                               name="custom_suffix" 
                                               id="custom_suffix"
                                               x-model="customSuffix"
                                               @input="generatePreview"
                                               placeholder="e.g., Continued, Extended"
                                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>

                                    <div x-show="namingPattern === 'custom'" x-transition>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                            Use <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">{n}</code> for sequence number, 
                                            <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">{name}</code> for original name, and 
                                            <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">{t}</code> for start time
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Options --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    Additional Options
                                </h4>
                                
                                <div class="space-y-3">
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               name="copy_volunteers" 
                                               x-model="copyVolunteers"
                                               value="1"
                                               class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                        <span class="ml-3">
                                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Copy Assigned Volunteers</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Duplicate volunteer assignments to new shifts</span>
                                        </span>
                                    </label>

                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               name="maintain_capacity" 
                                               value="1"
                                               checked
                                               class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                        <span class="ml-3">
                                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Maintain Max Volunteers</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Keep the same volunteer capacity for duplicates</span>
                                        </span>
                                    </label>

                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               name="copy_description" 
                                               value="1"
                                               checked
                                               class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                        <span class="ml-3">
                                            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Copy Description</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Include shift description in duplicates</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- Preview --}}
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Preview
                                    <span class="ml-2 text-xs font-normal text-gray-600 dark:text-gray-400" x-show="recurrence > 10">(Showing first 10)</span>
                                </h4>
                                <div class="space-y-1 max-h-48 overflow-y-auto">
                                    <template x-for="shift in previewShifts" :key="shift.sequence">
                                        <div class="text-sm text-gray-700 dark:text-gray-300 py-1 px-2 bg-white dark:bg-gray-800 rounded">
                                            <span class="font-mono text-xs text-gray-500 dark:text-gray-400 mr-2" x-text="'#' + shift.sequence"></span>
                                            <span x-text="shift.name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="mt-6 flex items-center justify-end gap-x-3">
                            <button type="button" 
                                    @click="open = false"
                                    class="rounded-md bg-white dark:bg-gray-700 px-3.5 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="rounded-md bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Create <span x-text="recurrence"></span> Duplicate<span x-show="recurrence > 1">s</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function advancedDuplicateModal() {
    return {
        open: false,
        shiftId: null,
        shiftName: '',
        shiftStartTime: null,
        recurrence: 1,
        interval: 1,
        intervalUnit: 'days',
        namingPattern: 'sequence',
        customPrefix: '',
        customSuffix: '',
        copyVolunteers: false,
        dateIncrement: true,
        previewShifts: [],
        
        openModal(shift) {
            console.log('Opening modal for shift:', shift);
            this.shiftId = shift.id;
            this.shiftName = shift.name;
            this.shiftStartTime = shift.startTime ? new Date(shift.startTime) : null;
            this.open = true;
            this.generatePreview();
        },
        
        formatTime(date) {
            // Format time like "2pm", "3pm", etc.
            let hours = date.getHours();
            let ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            return hours + ampm;
        },
        
        calculateStartTime(iteration) {
            if (!this.shiftStartTime) return '';
            
            let newTime = new Date(this.shiftStartTime);
            let offset = this.interval * iteration;
            
            switch(this.intervalUnit) {
                case 'hours':
                    newTime.setHours(newTime.getHours() + offset);
                    break;
                case 'days':
                    newTime.setDate(newTime.getDate() + offset);
                    break;
                case 'weeks':
                    newTime.setDate(newTime.getDate() + (offset * 7));
                    break;
            }
            
            return this.formatTime(newTime);
        },
        
        generatePreview() {
            this.previewShifts = [];
            for (let i = 1; i <= Math.min(this.recurrence, 10); i++) {
                let name = this.shiftName;
                let timeStr = this.calculateStartTime(i);
                
                switch(this.namingPattern) {
                    case 'sequence':
                        name = this.shiftName + ' (' + i + ')';
                        break;
                    case 'start_time':
                        name = this.shiftName + ' (' + timeStr + ')';
                        break;
                    case 'prefix':
                        name = this.customPrefix + ' ' + this.shiftName;
                        break;
                    case 'suffix':
                        name = this.shiftName + ' ' + this.customSuffix;
                        break;
                    case 'prefix_sequence':
                        name = this.customPrefix + ' ' + this.shiftName + ' (' + i + ')';
                        break;
                    case 'suffix_sequence':
                        name = this.shiftName + ' (' + i + ') ' + this.customSuffix;
                        break;
                    case 'custom':
                        name = this.customPrefix
                            .replace('{n}', i)
                            .replace('{name}', this.shiftName)
                            .replace('{t}', timeStr);
                        break;
                }
                
                this.previewShifts.push({
                    sequence: i,
                    name: name
                });
            }
        }
    }
}
</script>