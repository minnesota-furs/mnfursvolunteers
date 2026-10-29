@props(['election'])

@php
    $modalName = 'voter-confidence-'.$election->id;
    $periodName = $election->fiscal_ledger_id && $election->fiscalLedger
        ? $election->fiscalLedger->name
        : 'the current fiscal year';
@endphp

<div x-data {{ $attributes }}>
    <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', '{{ $modalName }}')"
        class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-brand-green hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 dark:text-gray-400 dark:hover:text-green-400">
        <x-heroicon-o-shield-check class="h-3.5 w-3.5" />
        How is voting kept fair and secure?
    </button>

    <template x-teleport="body">
        <x-modal :name="$modalName" maxWidth="2xl" focusable>
        <div class="p-6">
            <div class="flex items-start justify-between gap-4">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-shield-check class="h-6 w-6 text-brand-green" />
                    Voter confidence
                </h2>
                <button type="button" x-data x-on:click="$dispatch('close-modal', '{{ $modalName }}')"
                    class="rounded-md text-gray-400 hover:text-gray-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-600 dark:hover:text-gray-200"
                    aria-label="Close">
                    <x-heroicon-s-x-mark class="h-5 w-5" />
                </button>
            </div>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Here is what the voting system does to keep this election fair, and what it doesn't do.
                These claims can be validated against the open source codebase, which is available at
                <a href="https://github.com/minnesota-furs/mnfursvolunteers" target="_blank" rel="noopener noreferrer"
                    class="font-medium text-brand-green underline hover:no-underline dark:text-green-400">github.com/minnesota-furs/mnfursvolunteers</a>.
            </p>

            <dl class="mt-4 flex flex-col gap-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-900 dark:text-gray-100">Only eligible members vote</dt>
                    <dd class="mt-1 text-gray-600 dark:text-gray-400">
                        You must be signed in to vote.
                        @if($election->min_voter_hours > 0)
                            This election also requires at least {{ format_hours($election->min_voter_hours) }} volunteer {{ Str::plural('hour', $election->min_voter_hours) }} in {{ $periodName }}.
                        @endif
                        Eligibility is checked by the server every time a vote is submitted.
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-900 dark:text-gray-100">No double voting</dt>
                    <dd class="mt-1 text-gray-600 dark:text-gray-400">
                        You can choose up to {{ $election->max_positions }} {{ Str::plural('candidate', $election->max_positions) }}, and the database itself rejects a second vote for the same candidate from the same account.
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-900 dark:text-gray-100">Only real candidates and a fixed window</dt>
                    <dd class="mt-1 text-gray-600 dark:text-gray-400">
                        Votes only count for approved candidates in this election, and are only accepted between
                        <x-user-time :time="$election->start_date" /> and <x-user-time :time="$election->end_date" />.
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-900 dark:text-gray-100">No running tallies</dt>
                    <dd class="mt-1 text-gray-600 dark:text-gray-400">
                        Nobody, including election administrators, can see how many votes each candidate has until voting closes. Results are then published for everyone.
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-900 dark:text-gray-100">Check your own vote</dt>
                    <dd class="mt-1 text-gray-600 dark:text-gray-400">
                        After you vote, this page shows the candidates you chose. If that doesn't match what you selected, tell the elections team right away.
                    </dd>
                </div>
                <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-900/40">
                    <dt class="font-medium text-gray-900 dark:text-gray-100">What isn't anonymous</dt>
                    <dd class="mt-1 text-gray-600 dark:text-gray-400">
                        Votes are stored against your account so that eligibility and one-vote-per-candidate can be enforced. Administrators with election management permission can see who voted and when, only for auditing, to check that ballots are accurate, and to help with election support queries. The voter list does not show who anyone voted for.
                    </dd>
                </div>
                <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-900/40">
                    <dt class="font-medium text-gray-900 dark:text-gray-100">Ballots can't be changed</dt>
                    <dd class="mt-1 text-gray-600 dark:text-gray-400">
                        The system has no feature for anyone, administrators included, to change who a ballot is for or to cast or edit a vote on someone else's behalf.
                    </dd>
                </div>
            </dl>

            <div class="mt-6 flex justify-end">
                <button type="button" x-data x-on:click="$dispatch('close-modal', '{{ $modalName }}')"
                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
        </x-modal>
    </template>
</div>
