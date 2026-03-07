<x-app-layout>
    @section('title', 'Create Shift Series for ' . $event->name)
    <x-slot name="header">
        Create Shift Series for Event: {{ $event->name }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.events.shifts.index', $event) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Cancel
        </a>
    </x-slot>

    <div x-data="shiftSeriesBuilder()" x-init="generatePreview()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('admin.events.shifts.store-series', $event) }}"
                  @submit.prevent="submitIfValid($el)">
                @csrf

                {{-- ───────────────────────────────── Shift Details ───────────────────────────────── --}}
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-heroicon-o-queue-list class="w-5 h-5 text-brand-green"/>
                            Shift Details
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            These settings apply to every shift in the series.
                        </p>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">

                        {{-- Series / base name --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="form-label">Series Name</dt>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                <x-text-input class="block w-72 text-sm" type="text" name="name" id="name"
                                    x-model="seriesName"
                                    @input="generatePreview"
                                    :value="old('name', '')" placeholder="e.g. Coat Check" required />
                                <p class="text-xs text-gray-500 mt-1">This is the base name used in the naming pattern below.</p>
                                <x-form-validation for="name" />
                            </dd>
                        </div>

                        {{-- Naming pattern --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <div>
                                <dt class="form-label">Naming Pattern</dt>
                                <p class="text-xs text-gray-500 mt-1">
                                    Tokens: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{name}</code>
                                    <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{start_time}</code>
                                    <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{n}</code>
                                </p>
                            </div>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                <x-text-input class="block w-72 text-sm" type="text" name="naming_pattern" id="naming_pattern"
                                    x-model="namingPattern"
                                    @input="generatePreview"
                                    :value="old('naming_pattern', '{name} - {start_time}')" required />
                                <p class="text-xs text-gray-500 mt-1">
                                    Example: <span class="font-medium" x-text="previewFirstName || '—'"></span>
                                </p>
                                <x-form-validation for="naming_pattern" />
                            </dd>
                        </div>

                        {{-- Description --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="form-label">Description</dt>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                <x-textarea-input id="description" rows="4" name="description"
                                    class="block w-full text-sm">{{ old('description', '') }}</x-textarea-input>
                                <x-form-validation for="description" />
                            </dd>
                        </div>

                        {{-- Max volunteers --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="form-label">Volunteers Needed</dt>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                <x-text-input class="block w-32 text-sm" type="number" name="max_volunteers"
                                    id="max_volunteers" min="1"
                                    value="{{ old('max_volunteers', 1) }}" required />
                                <x-form-validation for="max_volunteers" />
                            </dd>
                        </div>

                        {{-- Double hours --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <div>
                                <dt class="form-label">Double Hours</dt>
                                <p class="text-xs text-gray-500 mt-1">Count hours as double when crediting volunteers.</p>
                            </div>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                <x-checkbox-input name="double_hours" id="double_hours"
                                    checked="{{ old('double_hours', false) }}" />
                            </dd>
                        </div>

                        {{-- Tags --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <div>
                                <dt class="form-label">Shift Tags</dt>
                                <p class="text-xs text-gray-500 mt-1">Applied to every shift in the series.</p>
                            </div>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                @if(isset($tags) && $tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-3">
                                        @foreach ($tags as $tag)
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox" name="shift_tags[]" value="{{ $tag->id }}"
                                                    {{ is_array(old('shift_tags')) && in_array($tag->id, old('shift_tags')) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-brand-green shadow-sm focus:border-brand-green focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                                <span class="inline-flex items-center text-sm text-gray-800 dark:text-gray-200">
                                                    @if($tag->color)
                                                        <span class="inline-block w-3 h-3 rounded mr-1" style="background-color: {{ $tag->color }}"></span>
                                                    @endif
                                                    {{ $tag->name }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-gray-500 italic">
                                        No tags available. <a href="{{ route('admin.tags.create') }}" class="text-brand-green hover:underline">Create tags</a>
                                    </p>
                                @endif
                            </dd>
                        </div>

                    </div>
                </div>

                {{-- ───────────────────────────────── Recurrence Settings ───────────────────────────────── --}}
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-heroicon-o-arrow-path class="w-5 h-5 text-brand-green"/>
                            Recurrence Settings
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Define when the first shift starts and how the series repeats.
                        </p>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">

                        {{-- First shift start --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="form-label">First Shift Starts</dt>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                <x-text-input class="block w-64 text-sm" type="datetime-local" name="start_time"
                                    id="start_time"
                                    x-model="startTime"
                                    @change="generatePreview"
                                    value="{{ old('start_time', $event->start_date->format('Y-m-d\TH:i')) }}" required />
                                <x-form-validation for="start_time" />
                            </dd>
                        </div>

                        {{-- Duration --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <div>
                                <dt class="form-label">Shift Duration</dt>
                                <p class="text-xs text-gray-500 mt-1">How long each individual shift lasts.</p>
                            </div>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0 flex items-center gap-2">
                                <x-text-input class="w-20 text-sm" type="number" name="duration_hours"
                                    id="duration_hours" min="0" max="23"
                                    x-model.number="durationHours"
                                    @input="generatePreview"
                                    value="{{ old('duration_hours', 2) }}" required />
                                <span class="text-sm text-gray-600 dark:text-gray-300">hr</span>
                                <x-text-input class="w-20 text-sm" type="number" name="duration_minutes"
                                    id="duration_minutes" min="0" max="59"
                                    x-model.number="durationMinutes"
                                    @input="generatePreview"
                                    value="{{ old('duration_minutes', 0) }}" required />
                                <span class="text-sm text-gray-600 dark:text-gray-300">min</span>
                                <x-form-validation for="duration_hours" />
                            </dd>
                        </div>

                        {{-- Occurrences --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <div>
                                <dt class="form-label">Number of Occurrences</dt>
                                <p class="text-xs text-gray-500 mt-1">Total number of shifts to create in the series.</p>
                            </div>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0">
                                <x-text-input class="block w-32 text-sm" type="number" name="occurrences"
                                    id="occurrences" min="1" max="100"
                                    x-model.number="occurrences"
                                    @input="generatePreview"
                                    value="{{ old('occurrences', 6) }}" required />
                                <x-form-validation for="occurrences" />
                            </dd>
                        </div>

                        {{-- Gap --}}
                        <div class="px-6 py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <div>
                                <dt class="form-label">Gap Between Shifts</dt>
                                <p class="text-xs text-gray-500 mt-1">Break time between each shift ending and the next starting.</p>
                            </div>
                            <dd class="mt-1 sm:col-span-2 sm:mt-0 flex items-center gap-2">
                                <x-text-input class="w-20 text-sm" type="number" name="gap_hours"
                                    id="gap_hours" min="0" max="23"
                                    x-model.number="gapHours"
                                    @input="generatePreview"
                                    value="{{ old('gap_hours', 0) }}" required />
                                <span class="text-sm text-gray-600 dark:text-gray-300">hr</span>
                                <x-text-input class="w-20 text-sm" type="number" name="gap_minutes"
                                    id="gap_minutes" min="0" max="59"
                                    x-model.number="gapMinutes"
                                    @input="generatePreview"
                                    value="{{ old('gap_minutes', 0) }}" required />
                                <span class="text-sm text-gray-600 dark:text-gray-300">min</span>
                                <x-form-validation for="gap_hours" />
                            </dd>
                        </div>

                    </div>
                </div>

                {{-- ───────────────────────────────── Live Preview ───────────────────────────────── --}}
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <x-heroicon-o-eye class="w-5 h-5 text-brand-green"/>
                                Preview
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                This is what will be created. Review before submitting.
                            </p>
                        </div>
                        <span class="text-sm font-medium text-brand-green"
                              x-text="previewShifts.length + ' shift' + (previewShifts.length !== 1 ? 's' : '')">
                        </span>
                    </div>

                    <div class="px-6 py-4">
                        <template x-if="previewError">
                            <p class="text-sm text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                <x-heroicon-m-exclamation-triangle class="w-4 h-4"/>
                                <span x-text="previewError"></span>
                            </p>
                        </template>

                        <template x-if="!previewError && previewShifts.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    <thead>
                                        <tr>
                                            <th class="py-3 pr-4 text-left font-semibold text-gray-900 dark:text-gray-100 w-6">#</th>
                                            <th class="py-3 pr-4 text-left font-semibold text-gray-900 dark:text-gray-100">Name</th>
                                            <th class="py-3 pr-4 text-left font-semibold text-gray-900 dark:text-gray-100 w-44">Start</th>
                                            <th class="py-3 pr-4 text-left font-semibold text-gray-900 dark:text-gray-100 w-44">End</th>
                                            <th class="py-3 text-left font-semibold text-gray-900 dark:text-gray-100 w-24">Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <template x-for="(shift, idx) in previewShifts" :key="idx">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                                <td class="py-2 pr-4 text-gray-400 dark:text-gray-500" x-text="idx + 1"></td>
                                                <td class="py-2 pr-4 text-gray-900 dark:text-gray-100 font-medium" x-text="shift.name"></td>
                                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300" x-text="shift.startLabel"></td>
                                                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300" x-text="shift.endLabel"></td>
                                                <td class="py-2 text-gray-500 dark:text-gray-400" x-text="shift.durationLabel"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="!previewError && previewShifts.length === 0">
                            <p class="text-sm text-gray-400 italic">Fill in the settings above to see a preview.</p>
                        </template>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="py-4 flex justify-end space-x-3">
                    <a href="{{ route('admin.events.shifts.index', $event) }}"
                        class="rounded-md bg-gray-400 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                        Cancel
                    </a>
                    <button type="submit"
                        :disabled="previewShifts.length === 0 || !!previewError"
                        class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-s-plus class="w-4 inline -mt-0.5 mr-1"/>
                        Create <span x-text="previewShifts.length > 0 ? previewShifts.length + ' Shift' + (previewShifts.length !== 1 ? 's' : '') : 'Series'"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function shiftSeriesBuilder() {
        return {
            // Form state (mirrors input fields for live preview)
            seriesName:      '{{ old('name', '') }}',
            namingPattern:   '{{ old('naming_pattern', '{name} - {start_time}') }}',
            startTime:       '{{ old('start_time', $event->start_date->format('Y-m-d\TH:i')) }}',
            durationHours:   {{ old('duration_hours', 2) }},
            durationMinutes: {{ old('duration_minutes', 0) }},
            occurrences:     {{ old('occurrences', 6) }},
            gapHours:        {{ old('gap_hours', 0) }},
            gapMinutes:      {{ old('gap_minutes', 0) }},

            // Output
            previewShifts:   [],
            previewError:    null,
            previewFirstName: '',

            formatTime(date) {
                return date.toLocaleString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric',
                    hour: 'numeric', minute: '2-digit', hour12: true,
                });
            },

            formatShortTime(date) {
                return date.toLocaleTimeString('en-US', {
                    hour: 'numeric', minute: '2-digit', hour12: true,
                });
            },

            applyPattern(pattern, name, startDate, n) {
                const timeStr = this.formatShortTime(startDate);
                return pattern
                    .replace(/{name}/g, name)
                    .replace(/{start_time}/g, timeStr)
                    .replace(/{n}/g, n);
            },

            generatePreview() {
                this.previewError   = null;
                this.previewShifts  = [];
                this.previewFirstName = '';

                const name    = (this.seriesName || '').trim();
                const pattern = (this.namingPattern || '').trim();
                const totalDurationMins = (this.durationHours * 60) + this.durationMinutes;
                const totalGapMins      = (this.gapHours * 60) + this.gapMinutes;
                const occurrences = Math.max(1, Math.min(100, parseInt(this.occurrences) || 0));

                if (!this.startTime) {
                    this.previewError = 'Please set a start date and time.';
                    return;
                }
                if (totalDurationMins < 1) {
                    this.previewError = 'Total duration must be at least 1 minute.';
                    return;
                }
                if (!pattern) {
                    this.previewError = 'Naming pattern is required.';
                    return;
                }

                const baseStart = new Date(this.startTime);
                if (isNaN(baseStart.getTime())) {
                    this.previewError = 'Invalid start date/time.';
                    return;
                }

                const shifts = [];
                const intervalMins = totalDurationMins + totalGapMins;

                for (let i = 0; i < occurrences; i++) {
                    const shiftStart = new Date(baseStart.getTime() + intervalMins * 60000 * i);
                    const shiftEnd   = new Date(shiftStart.getTime() + totalDurationMins * 60000);

                    const shiftName = this.applyPattern(pattern, name || '{name}', shiftStart, i + 1);

                    // Duration label
                    const durH = Math.floor(totalDurationMins / 60);
                    const durM = totalDurationMins % 60;
                    const durationLabel = durH > 0 && durM > 0
                        ? `${durH}h ${durM}m`
                        : durH > 0 ? `${durH}h` : `${durM}m`;

                    shifts.push({
                        name:          shiftName,
                        startLabel:    this.formatTime(shiftStart),
                        endLabel:      this.formatTime(shiftEnd),
                        durationLabel: durationLabel,
                    });
                }

                this.previewShifts   = shifts;
                this.previewFirstName = shifts.length > 0 ? shifts[0].name : '';
            },

            submitIfValid(form) {
                if (this.previewShifts.length === 0 || this.previewError) return;
                form.submit();
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
