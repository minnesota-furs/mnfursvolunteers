<x-app-layout>
    @section('title', 'Invite Code: ' . $inviteCode->code)
    <x-slot name="header">
        Invite Code: <span class="font-mono">{{ $inviteCode->code }}</span>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.invite-codes.edit', $inviteCode) }}"
            class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-brand-green shadow-md hover:bg-gray-100">
            <x-heroicon-s-pencil class="w-4 inline"/> Edit
        </a>
    </x-slot>

    <div class="">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Details</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</dt>
                            <dd class="mt-1 font-mono text-xl font-bold text-gray-900 dark:text-gray-100 tracking-widest">{{ $inviteCode->code }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Label</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $inviteCode->label ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</dt>
                            <dd class="mt-1">
                                @if($inviteCode->isUsable())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uses</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $inviteCode->uses_count }}
                                @if($inviteCode->max_uses !== null)
                                    / {{ $inviteCode->max_uses }}
                                    <span class="text-xs text-gray-500">({{ $inviteCode->max_uses - $inviteCode->uses_count }} remaining)</span>
                                @else
                                    / <span class="text-gray-400">Unlimited</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expires</dt>
                            <dd class="mt-1 text-sm {{ $inviteCode->expires_at?->isPast() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ $inviteCode->expires_at ? $inviteCode->expires_at->format('M j, Y g:i A') : 'Never' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                @if($inviteCode->creator)
                                    <a href="{{ route('users.show', $inviteCode->creator) }}" class="text-brand-green hover:underline">
                                        {{ $inviteCode->creator->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $inviteCode->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Tags Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tags Assigned on Registration</h3>
                    @if($inviteCode->tags->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No tags — users who register with this code receive no automatic tags.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($inviteCode->tags as $tag)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white"
                                      style="background-color: {{ $tag->color ?? '#6B7280' }}">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.invite-codes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    ← Back to Invite Codes
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
