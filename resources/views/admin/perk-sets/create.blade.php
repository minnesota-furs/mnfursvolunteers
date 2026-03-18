<x-app-layout>
    @section('title', isset($perkSet) ? 'Edit Perk Set: ' . $perkSet->name : 'Create Perk Set')
    <x-slot name="header">
        {{ isset($perkSet) ? 'Edit Perk Set: ' . $perkSet->name : 'Create Perk Set' }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.perk-sets.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
        @if(isset($perkSet))
            <form action="{{ route('admin.perk-sets.destroy', $perkSet) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700"
                    onclick="return confirm('Delete this perk set? Perks in this set will not be deleted but will become unassigned.');">
                    <x-heroicon-s-trash class="w-4 inline"/> Delete
                </button>
            </form>
        @endif
    </x-slot>

    <div class="py-6d">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($perkSet) ? route('admin.perk-sets.update', $perkSet) : route('admin.perk-sets.store') }}">
                @csrf
                @if(isset($perkSet))
                    @method('PUT')
                @endif

                {{-- Name --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Set Name</dt>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-full text-sm" type="text" name="name" id="name"
                            :value="old('name', $perkSet->name ?? '')" required placeholder="e.g. MNFurs 2026 Perks" />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                {{-- Description --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Description <span class="font-normal text-gray-400">(optional)</span></dt>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-textarea-input name="description" id="description" rows="3"
                            class="block w-full text-sm" placeholder="Briefly describe this set of perks...">{{ old('description', $perkSet->description ?? '') }}</x-textarea-input>
                        <x-form-validation for="description" />
                    </dd>
                </div>

                {{-- Fiscal Year --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Fiscal Year <span class="font-normal text-gray-400">(optional)</span></dt>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Limit general-hours perks in this set to a specific fiscal year.
                            Only applies to perks that do not have specific events linked.
                        </p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-select-input name="fiscal_ledger_id" id="fiscal_ledger_id" class="block w-64 text-sm">
                            <option value="">— All time —</option>
                            @foreach($fiscalLedgers as $ledger)
                                <option value="{{ $ledger->id }}"
                                    {{ old('fiscal_ledger_id', $perkSet->fiscal_ledger_id ?? '') == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->name }}
                                    @if($ledger->start_date && $ledger->end_date)
                                        ({{ $ledger->start_date->format('M j, Y') }} – {{ $ledger->end_date->format('M j, Y') }})
                                    @endif
                                </option>
                            @endforeach
                        </x-select-input>
                        <x-form-validation for="fiscal_ledger_id" />
                    </dd>
                </div>

                {{-- Visibility Dates --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Visible From <span class="font-normal text-gray-400">(optional)</span></dt>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Start showing this set to volunteers on this date. Leave blank to show immediately.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-48 text-sm" type="date" name="visible_from" id="visible_from"
                            :value="old('visible_from', isset($perkSet) && $perkSet->visible_from ? $perkSet->visible_from->format('Y-m-d') : '')" />
                        <x-form-validation for="visible_from" />
                    </dd>
                </div>

                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Visible Until <span class="font-normal text-gray-400">(optional)</span></dt>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">After this date the set moves to the volunteer perk history page. Leave blank to keep it on the current perks page indefinitely.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-48 text-sm" type="date" name="visible_until" id="visible_until"
                            :value="old('visible_until', isset($perkSet) && $perkSet->visible_until ? $perkSet->visible_until->format('Y-m-d') : '')" />
                        <x-form-validation for="visible_until" />
                    </dd>
                </div>

                {{-- Sort Order --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Sort Order</dt>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Controls display order. Lower numbers appear first.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-number-input class="block w-24 text-sm" name="sort_order" id="sort_order" min="0" step="1"
                            :value="old('sort_order', $perkSet->sort_order ?? 0)" />
                        <x-form-validation for="sort_order" />
                    </dd>
                </div>

                {{-- Active --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Active</dt>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-checkbox-input name="is_active" id="is_active"
                            :checked="old('is_active', $perkSet->is_active ?? true)" />
                        <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Show this perk set to volunteers
                        </label>
                        <x-form-validation for="is_active" />
                    </dd>
                </div>

                <div class="px-4 pt-4 pb-6 sm:px-0">
                    <button type="submit"
                        class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        {{ isset($perkSet) ? 'Save Changes' : 'Create Perk Set' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
