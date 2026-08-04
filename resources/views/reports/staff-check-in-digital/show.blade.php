<x-app-layout>
    @section('title', $staffCheckInSession->name)
    <x-slot name="header">{{ $staffCheckInSession->name }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('report.staffCheckIn.digital.edit', $staffCheckInSession) }}"
           class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-m-pencil class="inline w-4" /> Edit session
        </a>
    </x-slot>
    <div class="mx-auto max-w-7xl space-y-4 px-4 py-8 sm:px-6 lg:px-8"
         x-data="{ search: '', hideFullyCheckedIn: false, followUpOnly: false }">
        <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div><p class="font-semibold text-gray-900 dark:text-white">{{ $staffCheckInSession->groupName() }}</p><p class="text-sm text-gray-500 dark:text-gray-400">{{ $checkInsByUser->count() }} of {{ $staff->count() }} staff checked in</p></div>
                <a href="{{ route('report.staffCheckIn.digital.index') }}" class="text-sm font-semibold text-brand-green">All sessions</a>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto] md:items-center">
                <input type="search" x-model="search" placeholder="Search staff name, first name, or last name…" autofocus
                       class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" x-model="hideFullyCheckedIn" class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                    Hide fully checked in
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" x-model="followUpOnly" class="rounded border-gray-300 text-brand-green focus:ring-brand-green dark:border-gray-600 dark:bg-gray-700">
                    Follow-up needed only
                </label>
            </div>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($staff as $staffMember)
                @php
                    $checkIn = $checkInsByUser->get($staffMember->id);
                    $missingItems = $checkIn
                        ? collect($staffCheckInSession->checklist_items ?? [])->diff($checkIn->completed_items ?? [])
                        : collect();
                    $isMissingSignature = $checkIn && $staffCheckInSession->collect_signature && blank($checkIn->signature_data);
                    $needsFollowUp = $checkIn && ($missingItems->isNotEmpty() || $isMissingSignature);
                    $missingRequirements = $missingItems->values();

                    if ($isMissingSignature) {
                        $missingRequirements->push('Signature');
                    }

                    $searchableName = strtolower(collect([
                        $staffMember->name,
                        $staffMember->first_name,
                        $staffMember->last_name,
                    ])->filter()->join(' '));
                    $isFullyCheckedIn = $checkIn && ! $needsFollowUp;
                @endphp
                <a href="{{ route('report.staffCheckIn.digital.staff', [$staffCheckInSession, $staffMember]) }}"
                   x-show="@js($searchableName).includes(search.toLowerCase())
                       && (! hideFullyCheckedIn || ! @js($isFullyCheckedIn))
                       && (! followUpOnly || @js($needsFollowUp))"
                   class="flex items-center justify-between gap-4 rounded-lg bg-white p-4 shadow ring-1 ring-gray-200 transition hover:bg-gray-50 dark:bg-gray-800 dark:ring-gray-700 dark:hover:bg-gray-700">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $staffMember->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $staffMember->departments->pluck('name')->join(', ') }}</p>
                        @if($needsFollowUp)
                            <p class="mt-2 text-sm font-semibold text-amber-700 dark:text-amber-300">
                                Missing: {{ $missingRequirements->join(', ') }}
                            </p>
                        @endif
                    </div>
                    @if($needsFollowUp)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Needs follow-up</span>
                    @elseif($checkIn)
                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Checked in</span>
                    @else
                        <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-400" />
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
