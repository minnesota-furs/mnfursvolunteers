<x-app-layout>
    @section('title', 'Report: Custom Fields')

    <x-slot name="header">
        Report: Custom Fields
    </x-slot>

    <x-slot name="actions">
        {{-- intentionally empty --}}
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <form method="GET" action="{{ route('report.customFields') }}"
              class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                Choose a custom field, then view totals for each response or a list of volunteers and their responses.
                Only active volunteers are included.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
                <div>
                    <label for="custom_field_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Custom field
                    </label>
                    <select id="custom_field_id" name="custom_field_id" required
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        <option value="">Select a field...</option>
                        @foreach($customFields as $customField)
                            <option value="{{ $customField->id }}" @selected($selectedField?->is($customField))>
                                {{ $customField->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('custom_field_id')" />
                </div>

                <div>
                    <label for="sector_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Sector
                    </label>
                    <select id="sector_id" name="sector_id"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        <option value="">All sectors</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" @selected($selectedSectorId === $sector->id)>
                                {{ $sector->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('sector_id')" />
                </div>

                <div>
                    <label for="mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Report format
                    </label>
                    <select id="mode" name="mode"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                        <option value="count" @selected($mode === 'count')>Count by response</option>
                        <option value="people" @selected($mode === 'people')>Each person and response</option>
                    </select>
                </div>

                <div>
                    <button type="submit"
                            class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                        <x-heroicon-o-magnifying-glass class="w-4 inline mr-1"/> Generate Report
                    </button>
                </div>
            </div>
        </form>

        @if($selectedField && $mode === 'count')
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedField->name }} totals</h2>
                        <a href="{{ route('report.customFields.export', request()->query()) }}"
                           class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500">
                            Export CSV
                        </a>
                    </div>
                </div>
                @if($counts->isEmpty())
                    <p class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No active volunteers found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Response</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Volunteers</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($counts as $value => $count)
                                    <tr>
                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $value }}</td>
                                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @elseif($selectedField && $mode === 'people')
            <form method="GET" action="{{ route('report.customFields') }}"
                  class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
                <input type="hidden" name="custom_field_id" value="{{ $selectedField->id }}">
                <input type="hidden" name="mode" value="people">
                @if($selectedSectorId)
                    <input type="hidden" name="sector_id" value="{{ $selectedSectorId }}">
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] items-end gap-3">
                    <div class="w-full">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search volunteers</label>
                        <input type="text" id="search" name="search" value="{{ $search }}"
                               placeholder="Name or email..."
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                    </div>
                    <div class="w-full">
                        <label for="response" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Response</label>
                        <select id="response" name="response"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-brand-green focus:border-brand-green text-sm">
                            <option value="">All responses</option>
                            @foreach($responseOptions as $responseOption)
                                <option value="{{ $responseOption }}" @selected($responseFilter === $responseOption)>{{ $responseOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-md bg-brand-green px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                        Filter
                    </button>
                    <a href="{{ route('report.customFields.export', request()->query()) }}"
                       class="rounded-md bg-gray-700 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500">
                        Export CSV
                    </a>
                </div>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedField->name }} by volunteer</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Volunteer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $selectedField->name }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-6 py-3">
                                        <a href="{{ route('users.show', $user) }}" class="text-sm font-medium text-brand-green hover:underline">{{ $user->name }}</a>
                                        @if(Auth::user()->isAdmin())
                                            <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $user->customFieldValues->first()?->value ? str_replace(',', ', ', $user->customFieldValues->first()->value) : 'Not provided' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No volunteers match this search.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
            </div>
        @elseif($customFields->isEmpty())
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                No active custom fields are available.
            </div>
        @endif
    </div>
</x-app-layout>
