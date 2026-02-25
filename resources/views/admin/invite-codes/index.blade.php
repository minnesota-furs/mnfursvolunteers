<x-app-layout>
    @section('title', 'Invite Codes')
    <x-slot name="header">
        {{ __('Invite Codes') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.invite-codes.create') }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <x-heroicon-s-plus class="w-4 inline"/> Create Invite Code
        </a>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if($codes->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No invite codes have been created yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code / Label</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tags Assigned</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Uses</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($codes as $code)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $code->code }}</div>
                                            @if($code->label)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $code->label }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($code->tags as $tag)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white"
                                                            style="background-color: {{ $tag->color ?? '#6B7280' }}">
                                                        {{ $tag->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">None</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $code->uses_count }}
                                            @if($code->max_uses !== null)
                                                / {{ $code->max_uses }}
                                            @else
                                                <span class="text-xs text-gray-400">/ ∞</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if($code->expires_at)
                                                <span class="{{ $code->expires_at->isPast() ? 'text-red-500' : '' }}">
                                                    {{ $code->expires_at->format('M j, Y') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">Never</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($code->isUsable())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.invite-codes.show', $code) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                            <a href="{{ route('admin.invite-codes.edit', $code) }}" class="text-brand-green hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Edit</a>
                                            <form action="{{ route('admin.invite-codes.destroy', $code) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('Delete this invite code? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-slot name="right">
        <p class="py-4">
            Invite codes can be shared with prospective volunteers during onboarding. When a new user registers
            with a valid invite code, any tags associated with that code are automatically applied to their account.
            You can set usage limits and expiry dates to control access.
        </p>
        <ul class="text-sm space-y-1 list-disc list-inside text-gray-600 dark:text-gray-400">
            <li>Leave <strong>Max Uses</strong> blank for unlimited uses.</li>
            <li>Leave <strong>Expires</strong> blank for no expiry.</li>
            <li>Deactivating a code immediately prevents it from being used.</li>
        </ul>
    </x-slot>
</x-app-layout>
