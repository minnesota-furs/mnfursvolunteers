<x-app-layout>
    @section('title', 'Create Check-in Session')
    <x-slot name="header">Create Check-in Session</x-slot>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('report.staffCheckIn.digital.store') }}" class="space-y-6 rounded-xl bg-white p-6 shadow dark:bg-gray-800"
              x-data="{ scope: @js(old('scope', 'sector')), items: @js(old('checklist_items', ['Staff gift given', 'Staff badge given'])) }">
            @csrf
            <div><label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Session name</label><input id="name" name="name" value="{{ old('name') }}" required placeholder="Friday staff check-in" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></div>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label for="scope" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group staff by</label><select id="scope" name="scope" x-model="scope" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="sector">Sector</option><option value="department">Department</option></select></div>
                <div x-show="scope === 'sector'"><label for="sector_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sector</label><select id="sector_id" name="sector_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="">Select a sector</option>@foreach($sectors as $sector)<option value="{{ $sector->id }}" @selected(old('sector_id') == $sector->id)>{{ $sector->name }}</option>@endforeach</select></div>
                <div x-show="scope === 'department'"><label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label><select id="department_id" name="department_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="">Select a department</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->sector->name }}: {{ $department->name }}</option>@endforeach</select></div>
            </div>
            <fieldset><legend class="text-sm font-medium text-gray-700 dark:text-gray-300">Items to track</legend><div class="mt-3 space-y-3"><template x-for="(item, index) in items" :key="index"><div class="flex gap-2"><input name="checklist_items[]" x-model="items[index]" maxlength="60" class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"><button type="button" @click="items.splice(index, 1)" class="px-3 text-red-600">Remove</button></div></template></div><button type="button" @click="items.push('')" x-show="items.length < 12" class="mt-3 font-semibold text-brand-green">+ Add item</button></fieldset>
            <fieldset>
                <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">Staff information to show</legend>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select any custom fields the check-in attendant needs, such as T-shirt size.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @forelse($customFields as $customField)
                        <label class="flex min-h-12 items-center gap-3 rounded-lg border border-gray-300 p-3 text-gray-800 dark:border-gray-600 dark:text-gray-200">
                            <input type="checkbox" name="custom_fields[]" value="{{ $customField->id }}" @checked(in_array($customField->id, old('custom_fields', []))) class="h-5 w-5 rounded text-brand-green focus:ring-brand-green">
                            {{ $customField->name }}
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No active custom fields are available.</p>
                    @endforelse
                </div>
            </fieldset>
            <label class="flex items-center gap-3 text-gray-700 dark:text-gray-300"><input type="checkbox" name="collect_signature" value="1" @checked(old('collect_signature')) class="rounded text-brand-green focus:ring-brand-green"><span><strong>Collect signature</strong><span class="block text-sm text-gray-500">Require each staff member to sign on the tablet.</span></span></label>
            @if($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
            <button class="w-full rounded-lg bg-brand-green px-5 py-3 text-lg font-bold text-white">Create and Start Session</button>
        </form>
    </div>
</x-app-layout>
