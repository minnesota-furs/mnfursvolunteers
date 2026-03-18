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

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if($codes->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No invite codes have been created yet.</p>
                @else
                    <div>
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code / Label</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tags Assigned</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Uses</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($codes as $code)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-block w-2 h-2 rounded-full flex-shrink-0 {{ $code->isUsable() ? 'bg-green-500' : 'bg-red-500' }}"
                                                      title="{{ $code->isUsable() ? 'Active' : 'Inactive' }}"></span>
                                                <span class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $code->code }}</span>
                                            </div>
                                            @if($code->label)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 ml-4">{{ $code->label }}</div>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.invite-codes.show', $code) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                            <a href="{{ route('admin.invite-codes.edit', $code) }}" class="text-brand-green hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Edit</a>
                                            <x-tailwind-dropdown buttonClass="dropdown-link text-blue-600" label="Manage" id="{{ $code->id }}">
                                                <div class="py-1" role="none">
                                                    <button type="button"
                                                        onclick="copyInviteLink('{{ urlencode($code->code) }}', this)"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                        <x-heroicon-s-link class="w-4 inline"/> Copy Link
                                                    </button>
                                                </div>
                                                <div class="py-1" role="none">
                                                    <form action="{{ route('admin.invite-codes.destroy', $code) }}" method="POST"
                                                            onsubmit="return confirm('Delete this invite code? This cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <x-heroicon-o-trash class="w-4 inline"/> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </x-tailwind-dropdown>
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

<script>
function copyInviteLink(code, btn) {
    const url = '{{ route('register') }}?code=' + code;
    navigator.clipboard.writeText(url).then(() => {
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = original, 2000);
    });
}
</script>
