<x-app-layout>
    @section('title', 'Milestones - ' . $election->title)
    <x-slot name="header">
        Milestones: {{ $election->title }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('admin.elections.show', $election) }}"
            class="block rounded-md px-3 py-2 text-center text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            Back to Election
        </a>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Add a milestone</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Extra dates voters should know about, like "Candidate Bios Due" or a "Q&amp;A Debate". They appear under Key Dates on the election page.
                    </p>

                    <form action="{{ route('admin.elections.milestones.store', $election) }}" method="POST" class="mt-4 flex flex-col gap-4">
                        @csrf
                        @include('admin.elections._milestone-fields', ['milestone' => null, 'idPrefix' => 'new'])
                        <div>
                            <button type="submit"
                                class="rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                                <x-heroicon-s-plus class="w-4 inline" /> Add Milestone
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Milestones</h3>

                    @forelse($milestones as $milestone)
                        <details class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700" @if($errors->any() && (int) old('milestone_id') === $milestone->id) open @endif>
                            <summary class="flex cursor-pointer flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $milestone->title }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $milestone->starts_at->format('M j, Y g:i A') }}
                                    @if($milestone->location) · {{ $milestone->location }} @endif
                                </span>
                            </summary>
                            <div class="flex flex-col gap-4 border-t border-gray-200 p-4 dark:border-gray-700">
                                <form action="{{ route('admin.elections.milestones.update', [$election, $milestone]) }}" method="POST" class="flex flex-col gap-4">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="milestone_id" value="{{ $milestone->id }}">
                                    @include('admin.elections._milestone-fields', ['milestone' => $milestone, 'idPrefix' => 'milestone-'.$milestone->id])
                                    <div>
                                        <button type="submit"
                                            class="rounded-md bg-brand-green px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                                <form action="{{ route('admin.elections.milestones.destroy', [$election, $milestone]) }}" method="POST"
                                    onsubmit="return confirm('Remove this milestone?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                                        <x-heroicon-o-trash class="w-4 inline" /> Remove
                                    </button>
                                </form>
                            </div>
                        </details>
                    @empty
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No milestones yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
