{{-- Advanced Duplicate Event Modal --}}
<div x-data="advancedDuplicateEventModal()" 
     @open-event-duplicate-modal.window="openModal($event.detail)"
     x-cloak
     class="relative">
    
    {{-- Modal Overlay --}}
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-gray-500 bg-opacity-75 transition-opacity"
         style="backdrop-filter: blur(0px);"
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
         class="fixed inset-0 z-50 overflow-y-auto flex items-end justify-center p-4 sm:items-center sm:p-0"
         style="pointer-events: none;">
        
        <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
             style="pointer-events: auto;"
             @click.stop>
                
                {{-- Header --}}
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Duplicate Event</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a copy with all shifts (volunteers not included)</p>
                            </div>
                        </div>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form :action="`/admin/events/${eventId}/advanced-duplicate`" method="POST" class="mt-6">
                        @csrf
                        
                        <div class="space-y-6">
                            {{-- Event Name --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Event Details
                                </h4>
                                
                                <div>
                                    <label for="event_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        New Event Name
                                    </label>
                                    <input type="text" 
                                           name="event_name" 
                                           id="event_name" 
                                           x-model="eventName"
                                           placeholder="e.g., My Event (Copy)"
                                           required
                                           class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Original name: <span x-text="originalEventName" class="font-mono"></span></p>
                                </div>
                            </div>

                            {{-- Event Date Adjustment --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Event Date
                                </h4>

                                <label class="flex items-start mb-3">
                                    <input type="checkbox" 
                                           name="adjust_event_dates" 
                                           x-model="adjustEventDates"
                                           @change="generateDatePreview"
                                           value="1"
                                           class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                    <span class="ml-3">
                                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adjust Event Dates</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Shift the event start and end dates, and all shifts within it</span>
                                    </span>
                                </label>

                                <div x-show="adjustEventDates" x-transition class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label for="event_date_offset_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Offset Value
                                            </label>
                                            <input type="number" 
                                                   name="event_date_offset_value" 
                                                   id="event_date_offset_value"
                                                   x-model.number="eventDateOffsetValue"
                                                   @input="generateDatePreview"
                                                   min="-365"
                                                   value="365"
                                                   class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Positive for future dates, negative for past dates</p>
                                        </div>
                                        <div>
                                            <label for="event_date_offset_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Unit
                                            </label>
                                            <select name="event_date_offset_unit" 
                                                    id="event_date_offset_unit"
                                                    x-model="eventDateOffsetUnit"
                                                    @change="generateDatePreview"
                                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                <option value="days">Day(s)</option>
                                                <option value="weeks">Week(s)</option>
                                                <option value="months">Month(s)</option>
                                                <option value="years">Year(s)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Date Preview --}}
                            <div x-show="adjustEventDates" x-transition class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Date Preview
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Original Event Dates:</p>
                                        <p class="font-mono text-gray-900 dark:text-gray-100" x-text="formatDateRange(originalEventStartDate, originalEventEndDate)"></p>
                                    </div>
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded border-2 border-orange-300 dark:border-orange-600">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">New Event Dates:</p>
                                        <p class="font-mono text-orange-700 dark:text-orange-300 font-semibold" x-text="formatDateRange(previewEventStartDate, previewEventEndDate)"></p>
                                    </div>
                                    <div x-show="previewShifts.length > 0" class="mt-3">
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Sample Shifted Shifts:</p>
                                        <div class="space-y-1 max-h-32 overflow-y-auto">
                                            <template x-for="shift in previewShifts.slice(0, 3)" :key="shift.id">
                                                <div class="bg-white dark:bg-gray-800 p-2 rounded text-xs">
                                                    <p class="font-medium text-gray-900 dark:text-gray-100" x-text="shift.name"></p>
                                                    <p class="text-gray-600 dark:text-gray-400" x-text="formatTimeRange(shift.originalStart, shift.originalEnd)"></p>
                                                    <p class="text-orange-600 dark:text-orange-400 font-mono mt-1" x-text="'→ ' + formatTimeRange(shift.newStart, shift.newEnd)"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Options --}}
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Options
                                </h4>

                                <label class="flex items-start">
                                    <input type="checkbox" 
                                           name="copy_required_tags" 
                                           x-model="copyRequiredTags"
                                           value="1"
                                           class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-0.5">
                                    <span class="ml-3">
                                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Copy Required Tags</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Include the same tag requirements as the original event</span>
                                    </span>
                                </label>
                            </div>

                            {{-- Summary --}}
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Summary
                                </h4>
                                <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                    <p>Event Name: <span class="font-semibold" x-text="eventName || 'Not set'"></span></p>
                                    <p>Total Shifts to Duplicate: <span class="font-semibold" x-text="shiftsCount"></span></p>
                                    <p>Volunteers: <span class="font-semibold">Will not be copied</span></p>
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
                                    class="rounded-md bg-green-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                Duplicate Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function advancedDuplicateEventModal() {
    return {
        open: false,
        eventId: null,
        originalEventName: '',
        eventName: '',
        shiftsCount: 0,
        adjustEventDates: false,
        eventDateOffsetValue: 365,
        eventDateOffsetUnit: 'days',
        copyRequiredTags: false,
        originalEventStartDate: null,
        originalEventEndDate: null,
        previewEventStartDate: null,
        previewEventEndDate: null,
        previewShifts: [],

        openModal(event) {
            console.log('Opening event duplicate modal:', event);
            this.eventId = event.id;
            this.originalEventName = event.name;
            this.eventName = event.name + ' (Copy)';
            this.shiftsCount = event.shiftsCount || 0;
            this.originalEventStartDate = event.startDate ? new Date(event.startDate) : null;
            this.originalEventEndDate = event.endDate ? new Date(event.endDate) : null;
            this.open = true;
            this.generateDatePreview();
        },

        generateDatePreview() {
            if (!this.adjustEventDates || !this.originalEventStartDate) {
                this.previewEventStartDate = null;
                this.previewEventEndDate = null;
                this.previewShifts = [];
                return;
            }

            // Calculate new event dates
            this.previewEventStartDate = this.calculateOffsetDate(this.originalEventStartDate);
            this.previewEventEndDate = this.calculateOffsetDate(this.originalEventEndDate);

            // Generate preview shifts (this is mock data since we don't have actual shift data)
            // In a real scenario, you'd pass shift data from the server
            this.previewShifts = [];
        },

        calculateOffsetDate(date) {
            if (!date) return null;
            
            const newDate = new Date(date);
            const offsetValue = this.eventDateOffsetValue;

            switch (this.eventDateOffsetUnit) {
                case 'days':
                    newDate.setDate(newDate.getDate() + offsetValue);
                    break;
                case 'weeks':
                    newDate.setDate(newDate.getDate() + (offsetValue * 7));
                    break;
                case 'months':
                    newDate.setMonth(newDate.getMonth() + offsetValue);
                    break;
                case 'years':
                    newDate.setFullYear(newDate.getFullYear() + offsetValue);
                    break;
            }

            return newDate;
        },

        formatDateRange(startDate, endDate) {
            if (!startDate || !endDate) return 'N/A';
            
            const start = this.formatDate(startDate);
            const end = this.formatDate(endDate);
            return `${start} to ${end}`;
        },

        formatTimeRange(startTime, endTime) {
            if (!startTime || !endTime) return 'N/A';
            
            const start = this.formatDateTime(startTime);
            const end = this.formatDateTime(endTime);
            return `${start} to ${end}`;
        },

        formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            return d.toLocaleDateString('en-US', options);
        },

        formatDateTime(dateTime) {
            if (!dateTime) return '';
            const d = new Date(dateTime);
            const options = { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            return d.toLocaleDateString('en-US', options);
        }
    }
}
</script>
