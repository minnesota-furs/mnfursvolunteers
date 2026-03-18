<x-app-layout>
    @section('title', isset($perk) ? 'Edit Perk: ' . $perk->name : 'Create Perk')
    <x-slot name="header">
        {{ isset($perk) ? 'Edit Perk: ' . $perk->name : 'Create Volunteer Perk' }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.perks.index') }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back
        </a>
        @if(isset($perk))
            <form action="{{ route('admin.perks.destroy', $perk) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="block rounded-md bg-red-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-md hover:bg-red-700"
                    onclick="return confirm('Delete this perk? This cannot be undone.');">
                    <x-heroicon-s-trash class="w-4 inline"/> Delete
                </button>
            </form>
        @endif
    </x-slot>

    <div class="py-6d">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($perk) ? route('admin.perks.update', $perk) : route('admin.perks.store') }}">
                @csrf
                @if(isset($perk))
                    @method('PUT')
                @endif

                {{-- Name --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Perk Name</dt>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-text-input class="block w-full text-sm" type="text" name="name" id="name"
                            :value="old('name', $perk->name ?? '')" required placeholder="e.g. Free T-Shirt" />
                        <x-form-validation for="name" />
                    </dd>
                </div>

                {{-- Description --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Description</dt>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-textarea-input name="description" id="description" rows="3"
                            class="block w-full text-sm" placeholder="Describe what the volunteer receives...">{{ old('description', $perk->description ?? '') }}</x-textarea-input>
                        <x-form-validation for="description" />
                    </dd>
                </div>

                {{-- Minimum Hours --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Minimum Hours</dt>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Hour threshold to earn this perk.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-number-input class="block w-32 text-sm" name="min_hours" id="min_hours" step="0.25" min="0.25"
                            :value="old('min_hours', isset($perk) ? (float)$perk->min_hours : '')" required />
                        <x-form-validation for="min_hours" />
                    </dd>
                </div>

                {{-- Fiscal Year --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Fiscal Year <span class="font-normal text-gray-400">(optional)</span></dt>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Limit tracked hours to a specific fiscal year. Only applies when no events are linked.</p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-select-input name="fiscal_ledger_id" id="fiscal_ledger_id" class="block w-64 text-sm">
                            <option value="">— All time —</option>
                            @foreach($fiscalLedgers as $ledger)
                                <option value="{{ $ledger->id }}"
                                    {{ old('fiscal_ledger_id', $perk->fiscal_ledger_id ?? '') == $ledger->id ? 'selected' : '' }}>
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

                {{-- Linked Events --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <div>
                        <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Track Event(s) <span class="font-normal text-gray-400">(optional)</span></dt>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            If events are selected, only hours from completed shifts in those events count toward this perk.
                            Leave empty to count <em>all</em> volunteer hours (filtered by fiscal year above, if set).
                        </p>
                    </div>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        @php $selectedEventIds = old('event_ids', isset($perk) ? $perk->events->pluck('id')->toArray() : []); @endphp
                        <div class="border border-gray-300 dark:border-gray-600 rounded-md max-h-60 overflow-y-auto bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($events as $event)
                                <label class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                                    <input type="checkbox" name="event_ids[]" value="{{ $event->id }}"
                                        class="rounded border-gray-300 dark:border-gray-600 text-brand-green focus:ring-brand-green"
                                        {{ in_array($event->id, $selectedEventIds) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $event->name }}</span>
                                    @if($event->start_date)
                                        <span class="ml-auto text-xs text-gray-400">{{ $event->start_date->format('M j, Y') }}</span>
                                    @endif
                                </label>
                            @empty
                                <p class="px-4 py-3 text-sm text-gray-400">No events found.</p>
                            @endforelse
                        </div>
                        <x-form-validation for="event_ids" />
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
                            :value="old('sort_order', $perk->sort_order ?? 0)" />
                        <x-form-validation for="sort_order" />
                    </dd>
                </div>

                {{-- Active --}}
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Active</dt>
                    <dd class="mt-1 text-sm leading-6 sm:col-span-2 sm:mt-0">
                        <x-checkbox-input name="is_active" id="is_active"
                            :checked="old('is_active', $perk->is_active ?? true)" />
                        <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Show this perk to volunteers
                        </label>
                        <x-form-validation for="is_active" />
                    </dd>
                </div>

                <div class="px-4 pt-4 pb-6 sm:px-0">
                    <button type="submit"
                        class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        {{ isset($perk) ? 'Save Changes' : 'Create Perk' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
