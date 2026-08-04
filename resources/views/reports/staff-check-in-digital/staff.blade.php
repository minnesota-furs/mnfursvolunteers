<x-app-layout>
    @section('title', 'Check in '.$user->name)
    <x-slot name="header">Check in {{ $user->name }}</x-slot>
    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6" x-data="signatureCapture(@js($checkIn?->signature_data ?? ''))">
        <form method="POST" action="{{ route('report.staffCheckIn.digital.complete', [$staffCheckInSession, $user]) }}" class="space-y-6 rounded-xl bg-white p-6 shadow dark:bg-gray-800">
            @csrf
            @method('PUT')
            <div><h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2><p class="text-gray-500 dark:text-gray-400">Complete each item provided during check-in.</p></div>
            @if($selectedCustomFields->isNotEmpty())
                <dl class="grid gap-3 sm:grid-cols-2">
                    @foreach($selectedCustomFields as $customField)
                        <div class="rounded-xl bg-green-50 p-4 dark:bg-green-900/20">
                            <dt class="text-sm font-semibold text-green-800 dark:text-green-300">{{ $customField->name }}</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $user->customFieldValues->firstWhere('custom_field_id', $customField->id)?->value ?: 'Not provided' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
            <div class="space-y-3">
                @foreach($staffCheckInSession->checklist_items ?? [] as $item)
                    <label class="flex min-h-16 items-center gap-4 rounded-xl border border-gray-300 p-4 text-lg font-semibold text-gray-900 dark:border-gray-600 dark:text-white">
                        <input type="checkbox" name="completed_items[]" value="{{ $item }}" @checked(in_array($item, old('completed_items', $checkIn?->completed_items ?? []))) class="h-7 w-7 rounded text-brand-green focus:ring-brand-green">
                        {{ $item }}
                    </label>
                @endforeach
            </div>
            @if($staffCheckInSession->collect_signature)
                <input type="hidden" name="signature_data" x-model="signatureData">
                <div class="rounded-xl border border-gray-300 p-4 dark:border-gray-600">
                    <div class="flex items-center justify-between gap-3"><div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Staff signature</h3><p class="text-sm text-gray-500">Hand the tablet to the staff member to sign.</p></div><span x-show="signatureData" class="font-semibold text-green-700">Signature captured</span></div>
                    <button type="button" @click="open()" class="mt-4 w-full rounded-lg bg-gray-700 px-5 py-4 text-lg font-bold text-white" x-text="signatureData ? 'Replace signature' : 'Collect signature'"></button>
                </div>
                <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 flex flex-col bg-white p-4">
                    <div class="flex items-center justify-between"><h2 class="text-2xl font-bold">Sign below</h2><button type="button" @click="isOpen = false" class="p-3 text-lg">Cancel</button></div>
                    <canvas x-ref="signatureCanvas" class="my-4 min-h-0 flex-1 w-full touch-none rounded-xl border-2 border-gray-400"></canvas>
                    <div class="grid grid-cols-2 gap-4"><button type="button" @click="clear()" class="rounded-lg border border-gray-400 px-5 py-4 text-lg font-bold">Clear</button><button type="button" @click="accept()" class="rounded-lg bg-brand-green px-5 py-4 text-lg font-bold text-white">Accept signature</button></div>
                </div>
            @endif
            @if($errors->any())<div class="rounded-md bg-red-50 p-3 text-red-700">{{ $errors->first() }}</div>@endif
            <div class="grid gap-3 sm:grid-cols-2"><a href="{{ route('report.staffCheckIn.digital.show', $staffCheckInSession) }}" class="rounded-lg border border-gray-300 px-5 py-4 text-center text-lg font-bold text-gray-700 dark:text-gray-200">Back to staff list</a><button class="rounded-lg bg-brand-green px-5 py-4 text-lg font-bold text-white">Complete Check-in</button></div>
        </form>
    </div>
</x-app-layout>
