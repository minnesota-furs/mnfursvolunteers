<x-appPrint-layout>
    @section('title', 'Staff Check-in: '.$reportGroupName)

    <x-slot name="header">
        Staff Check-in
    </x-slot>

    <div class="py-4">
        <div class="mb-4 flex items-start justify-between gap-4">
            <p class="text-sm text-gray-700">{{ $reportGroupName }} &middot; {{ $staff->count() }} staff</p>
            <button type="button" onclick="window.print()"
                    class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600 print:hidden">
                Print
            </button>
        </div>

        <table class="min-w-full border-collapse text-left text-sm print:text-[10px]">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-3 py-2 font-semibold text-gray-900">{{ $listLegalName ? 'First and last name' : 'Staff name' }}</th>
                    <th class="border border-gray-300 px-3 py-2 font-semibold text-gray-900">Department</th>
                    @foreach($selectedCustomFields as $customField)
                        <th class="border border-gray-300 px-3 py-2 font-semibold text-gray-900">{{ $customField->name }}</th>
                    @endforeach
                    @foreach($checklistItems as $checklistItem)
                        <th class="border border-gray-300 px-3 py-2 text-center font-semibold text-gray-900">{{ $checklistItem }}</th>
                    @endforeach
                    @if($includeSignature)
                        <th class="min-w-40 border border-gray-300 px-3 py-2 font-semibold text-gray-900">Signature</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($staffGroups as $letter => $groupedStaff)
                    @if($groupAlphabetically)
                        <tr class="break-after-avoid bg-gray-100">
                            <th colspan="{{ 2 + $selectedCustomFields->count() + $checklistItems->count() + ($includeSignature ? 1 : 0) }}"
                                class="border border-gray-300 px-3 py-1 text-base font-bold text-gray-900">{{ $letter ?: '#' }}</th>
                        </tr>
                    @endif
                    @foreach($groupedStaff as $staffMember)
                        <tr class="break-inside-avoid">
                            <td class="border border-gray-300 px-3 py-3 text-gray-900">
                                {{ $listLegalName ? (trim($staffMember->first_name.' '.$staffMember->last_name) ?: $staffMember->name) : $staffMember->name }}
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-gray-700">{{ $staffMember->departments->pluck('name')->join(', ') }}</td>
                            @foreach($selectedCustomFields as $customField)
                                <td class="border border-gray-300 px-3 py-3 text-gray-700">{{ $staffMember->customFieldValues->firstWhere('custom_field_id', $customField->id)?->value ?: '—' }}</td>
                            @endforeach
                            @foreach($checklistItems as $checklistItem)
                                <td class="border border-gray-300 px-3 py-3 text-center text-lg text-gray-900" aria-label="{{ $checklistItem }}">&#9633;</td>
                            @endforeach
                            @if($includeSignature)
                                <td class="h-12 min-w-40 border border-gray-300 px-3 py-3" aria-label="Signature"></td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ 2 + $selectedCustomFields->count() + $checklistItems->count() + ($includeSignature ? 1 : 0) }}"
                            class="border border-gray-300 p-6 text-center text-gray-500">No active staff found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-appPrint-layout>
