<x-app-layout>
    @section('title', 'Report: Staff Check-in')

    <x-slot name="header">
        Report: Staff Check-in
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8 print:max-w-none print:p-0">
        <form method="GET" action="{{ route('report.staffCheckIn.paper') }}"
              class="space-y-5 rounded-lg bg-white p-6 shadow dark:bg-gray-800 print:hidden"
              x-data="{ scope: @js($scope), checklistItems: @js($checklistItems->isEmpty() ? [''] : $checklistItems->values()) }">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Build a check-in sheet</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose a staff group, optional profile fields, and the items staff should receive.</p>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label for="scope" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group staff by</label>
                    <select id="scope" name="scope" x-model="scope" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select a group</option>
                        <option value="sector">Sector</option>
                        <option value="department">Department</option>
                    </select>
                </div>
                <div x-show="scope === 'sector'" x-cloak>
                    <label for="sector_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sector</label>
                    <select id="sector_id" name="sector_id" :required="scope === 'sector'"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select a sector</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" @selected($selectedSectorId === $sector->id)>{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="scope === 'department'" x-cloak>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                    <select id="department_id" name="department_id" :required="scope === 'department'"
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select a department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected($selectedDepartmentId === $department->id)>{{ $department->sector->name }}: {{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <fieldset>
                <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">Custom fields to include</legend>
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($customFields as $customField)
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="custom_fields[]" value="{{ $customField->id }}" @checked($selectedCustomFieldIds->contains($customField->id))
                                   class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                            {{ $customField->name }}
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No active custom fields are available.</p>
                    @endforelse
                </div>
            </fieldset>

            <fieldset>
                <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">Check-in checklist</legend>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add up to 12 blank checkbox columns, such as Badge or T-shirt collected.</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="(item, index) in checklistItems" :key="index">
                        <div class="flex gap-2">
                            <input type="text" name="checklist_items[]" x-model="checklistItems[index]" maxlength="60" placeholder="Checklist item"
                                   class="min-w-0 flex-1 rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <button type="button" x-show="checklistItems.length > 1" @click="checklistItems.splice(index, 1)"
                                    class="rounded-md px-3 text-gray-500 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700" aria-label="Remove checklist item">&times;</button>
                        </div>
                    </template>
                </div>
                <button type="button" x-show="checklistItems.length < 12" @click="checklistItems.push('')"
                        class="mt-3 text-sm font-semibold text-brand-green hover:underline dark:text-green-400">+ Add checklist item</button>
            </fieldset>

            <fieldset>
                <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">Sheet layout</legend>
                <div class="mt-2 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="include_signature" value="1" @checked($includeSignature)
                               class="mt-0.5 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                        <span><span class="font-medium">Include signature box</span><span class="block text-xs text-gray-500 dark:text-gray-400">Adds a blank signature area for each staff member.</span></span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="list_legal_name" value="1" @checked($listLegalName)
                               class="mt-0.5 rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                        <span><span class="font-medium">List first and last name</span><span class="block text-xs text-gray-500 dark:text-gray-400">Uses legal first and last name instead of staff name.</span></span>
                    </label>
                    <div x-data="{ enabled: @js($groupAlphabetically) }">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="group_alphabetically" value="1" x-model="enabled" @checked($groupAlphabetically)
                                   class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                            Group alphabetically
                        </label>
                        <select name="alphabetical_by" :disabled="! enabled"
                                class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-green focus:ring-brand-green disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="name" @selected($alphabeticalBy === 'name')>Staff name</option>
                            <option value="first_name" @selected($alphabeticalBy === 'first_name')>Legal first name</option>
                            <option value="last_name" @selected($alphabeticalBy === 'last_name')>Legal last name</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            @if($errors->any())
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">Generate report</button>
        </form>

        @if($staff !== null)
            <section class="rounded-lg bg-white p-6 shadow dark:bg-gray-800 print:rounded-none print:p-0 print:shadow-none">
                <div class="mb-5 flex items-start justify-between gap-4 print:mb-3">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white print:text-black">Staff Check-in</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-300 print:text-black">{{ $reportGroupName }} &middot; {{ $staff->count() }} staff</p>
                    </div>
                    <a href="{{ route('report.staffCheckIn.print', request()->query()) }}"
                       class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600 print:hidden">Print</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-left text-sm print:text-[10px]">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 font-semibold text-gray-900 print:text-black">{{ $listLegalName ? 'First and last name' : 'Staff name' }}</th>
                                <th class="border border-gray-300 px-3 py-2 font-semibold text-gray-900 print:text-black">Department</th>
                                @foreach($selectedCustomFields as $customField)
                                    <th class="border border-gray-300 px-3 py-2 font-semibold text-gray-900 print:text-black">{{ $customField->name }}</th>
                                @endforeach
                                @foreach($checklistItems as $checklistItem)
                                    <th class="border border-gray-300 px-3 py-2 text-center font-semibold text-gray-900 print:text-black">{{ $checklistItem }}</th>
                                @endforeach
                                @if($includeSignature)
                                    <th class="min-w-40 border border-gray-300 px-3 py-2 font-semibold text-gray-900 print:text-black">Signature</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffGroups as $letter => $groupedStaff)
                                @if($groupAlphabetically)
                                    <tr class="break-after-avoid bg-gray-100 dark:bg-gray-700 print:bg-gray-100">
                                        <th colspan="{{ 2 + $selectedCustomFields->count() + $checklistItems->count() + ($includeSignature ? 1 : 0) }}"
                                            class="border border-gray-300 px-3 py-1 text-base font-bold text-gray-900 print:text-black">{{ $letter ?: '#' }}</th>
                                    </tr>
                                @endif
                                @foreach($groupedStaff as $staffMember)
                                    <tr class="break-inside-avoid">
                                        <td class="border border-gray-300 px-3 py-3 text-gray-900 dark:text-gray-100 print:text-black">
                                            {{ $listLegalName ? (trim($staffMember->first_name.' '.$staffMember->last_name) ?: $staffMember->name) : $staffMember->name }}
                                        </td>
                                        <td class="border border-gray-300 px-3 py-3 text-gray-700 dark:text-gray-300 print:text-black">{{ $staffMember->departments->pluck('name')->join(', ') }}</td>
                                        @foreach($selectedCustomFields as $customField)
                                            <td class="border border-gray-300 px-3 py-3 text-gray-700 dark:text-gray-300 print:text-black">{{ $staffMember->customFieldValues->firstWhere('custom_field_id', $customField->id)?->value ?: '—' }}</td>
                                        @endforeach
                                        @foreach($checklistItems as $checklistItem)
                                            <td class="border border-gray-300 px-3 py-3 text-center text-lg text-gray-900 print:text-black" aria-label="{{ $checklistItem }}">&#9633;</td>
                                        @endforeach
                                        @if($includeSignature)
                                            <td class="h-12 min-w-40 border border-gray-300 px-3 py-3" aria-label="Signature"></td>
                                        @endif
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="{{ 2 + $selectedCustomFields->count() + $checklistItems->count() + ($includeSignature ? 1 : 0) }}" class="border border-gray-300 p-6 text-center text-gray-500">No active staff found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
