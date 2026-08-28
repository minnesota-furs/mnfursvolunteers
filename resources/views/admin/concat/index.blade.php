<x-app-layout>
    @section('title', 'Manage Concat')
    <x-slot name="header">
        {{ __('Manage Concat') }}
    </x-slot>

    <x-slot name="actions">
        <form action="{{ route('admin.concat.sync') }}" method="POST">
            @csrf
            <button type="submit"
                class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <x-heroicon-s-arrow-path class="w-4 inline"/> Sync Now
            </button>
        </form>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Watched sectors are reconciled automatically every 15 minutes: a volunteer newly assigned to a
                    department in a watched sector is looked up in ConCat by email and granted the mapped role, and
                    a volunteer with no remaining department in that sector has the role revoked.
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Last sync:
                    @if($lastSyncedAt)
                        {{ \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() }}
                    @else
                        never
                    @endif
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <form action="{{ route('admin.concat.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($sectors->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No sectors have been created yet.</p>
                    @elseif($roles->isEmpty())
                        <p class="text-red-600 dark:text-red-400 text-center py-8">
                            ConCat returned no roles. Double-check the connected app has roles configured on the ConCat side.
                        </p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Watch</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sector</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ConCat Role</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scope</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Active Grants</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($sectors as $sector)
                                    @php $mapping = $sector->concatRoleMapping; @endphp
                                    <tr>
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="sectors[{{ $sector->id }}][watched]" value="1"
                                                {{ $mapping ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-brand-green shadow-sm focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600">
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ $sector->name }}</td>
                                        <td class="px-4 py-4">
                                            <select name="sectors[{{ $sector->id }}][concat_role_id]"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                                <option value="">— Select a role —</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role['id'] }}" {{ $mapping && $mapping->concat_role_id === $role['id'] ? 'selected' : '' }}>
                                                        {{ $role['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-4">
                                            <select name="sectors[{{ $sector->id }}][concat_scope]"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-green focus:ring-brand-green dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                                <option value="convention" {{ (!$mapping || $mapping->concat_scope === 'convention') ? 'selected' : '' }}>This convention</option>
                                                <option value="global" {{ $mapping && $mapping->concat_scope === 'global' ? 'selected' : '' }}>Global (all conventions)</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $grantCounts[$sector->id] ?? 0 }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="rounded-md bg-brand-green px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-green-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-green">
                                Save Mappings
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <x-slot name="right">
        <p class="py-4">
            Only sectors with both the checkbox checked and a role selected are watched. Unchecking a previously
            watched sector immediately revokes every role it granted.
        </p>
        <ul class="text-sm space-y-1 list-disc list-inside text-gray-600 dark:text-gray-400">
            <li>A volunteer must have a matching email address in ConCat to be granted a role.</li>
            <li>Moving between departments within the same watched sector doesn't change anything.</li>
            <li>Leaving every department in a watched sector revokes the role automatically.</li>
        </ul>
    </x-slot>
</x-app-layout>
