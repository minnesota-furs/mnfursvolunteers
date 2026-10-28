@props(['election', 'user'])

@php
    $voterShortfall = $election->userVoterHoursShortfall($user);
    $nominationOpen = $election->getNominationStatus() !== 'closed';
    $candidateShortfall = $nominationOpen ? $election->userCandidateHoursShortfall($user) : 0;
    $isNominated = $candidateShortfall > 0 && $election->userIsNominated($user);

    if (! $isNominated && ! $election->selfNominationStillPossible()) {
        $candidateShortfall = 0;
    }
    $reportingLedger = $election->reportingFiscalLedger();
    $ledgerIsOpen = $election->reportingFiscalLedgerIsOpen();
@endphp

@if(($voterShortfall > 0 || $candidateShortfall > 0) && ($ledgerIsOpen || ! $election->fiscal_ledger_id))
    <div {{ $attributes->merge(['class' => 'rounded-md bg-yellow-50 dark:bg-yellow-900/30 p-4 ring-1 ring-inset ring-yellow-600/20', 'role' => 'status']) }}>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex flex-1 min-w-0 gap-3">
                <x-heroicon-s-information-circle class="w-5 h-5 text-yellow-500 shrink-0" />
                <div class="flex-1 min-w-0 text-sm text-yellow-800 dark:text-yellow-200">
                    <p class="font-medium">You still have time to report your hours.</p>
                    <ul class="list-disc pl-5">
                        @if($voterShortfall > 0)
                            <li>Voting: {{ format_hours($voterShortfall) }} more {{ Str::plural('hour', $voterShortfall) }} needed ({{ format_hours($election->min_voter_hours) }} required)</li>
                        @endif
                        @if($candidateShortfall > 0)
                            <li>{{ $isNominated ? 'Candidate' : 'Self-nominating' }}: {{ format_hours($candidateShortfall) }} more {{ Str::plural('hour', $candidateShortfall) }} needed ({{ format_hours($election->min_candidate_hours) }} required)</li>
                            @if($isNominated)
                                <li>You're nominated, so you must accrue or report these hours, otherwise you may be removed from the ballot after the nomination period ends.</li>
                            @endif
                        @endif
                    </ul>
                    @if($ledgerIsOpen)
                        <p>
                            The {{ $reportingLedger->name }} period ends {{ $reportingLedger->end_date->copy()->endOfDay()->diffForHumans() }}
                            ({{ $reportingLedger->end_date->format('M j, Y') }}).
                        </p>
                    @endif
                </div>
            </div>
            <a href="{{ route('hours.create', $user->id) }}"
                class="w-full shrink-0 rounded-md bg-brand-green px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-green-800 sm:w-auto">
                <x-heroicon-o-clock class="w-4 inline" /> Log Hours
            </a>
        </div>
    </div>
@endif
