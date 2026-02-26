<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Event;
use App\Models\Shift;
use App\Models\FiscalLedger;

class ReportsController extends Controller
{
    public function usersWithoutDepartments(Request $request)
    {
        $reportTitle = 'Users Without Departments';
        $reportDescription = 'This report lists all users who are not assigned to any department.';
        $search = $request->input('search');

        $sort = $request->input('sort', 'name'); // Default sort column
        $direction = $request->input('direction', 'asc'); // Default sort direction

        // Get the current fiscal ledger
        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        // Users with no department relationships
        $users = User::query()
            ->doesntHave('departments')
            ->where('active', 1)
            ->paginate(15);

        return view('reports.users', compact('users','sort','direction','search','reportTitle','reportDescription'));
    }

    public function usersWithoutHoursThisPeriod(request $request)
    {
        $reportTitle = 'Users Without Hours This Period';
        $reportDescription = 'This report lists all users who have not logged any hours in the current fiscal period.';

        $search = $request->input('search');

        $sort = $request->input('sort', 'name'); // Default sort column
        $direction = $request->input('direction', 'asc'); // Default sort direction

        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$currentLedger) {
            return back()->with('error', 'No current fiscal ledger found.');
        }

        $users = User::whereDoesntHave('volunteerHours', function ($query) use ($currentLedger) {
            $query->where('fiscal_ledger_id', $currentLedger->id);
        })->where('active', 1)
        ->paginate(15);

        return view('reports.users', compact('users', 'currentLedger', 'sort','direction','search','reportTitle','reportDescription'));
    }

    public function eventShiftHoursReport(Request $request)
    {
        $events = Event::orderBy('start_date', 'desc')->get();
        $selectedEventIds = array_filter((array) $request->input('event_ids', []));
        $minHours = (float) $request->input('min_hours', 20);
        $results = collect();

        if ($request->has('event_ids') && !empty($selectedEventIds)) {
            $results = $this->buildShiftHoursResults($selectedEventIds, $minHours);
        }

        $selectedEvents = !empty($selectedEventIds)
            ? Event::whereIn('id', $selectedEventIds)->orderBy('start_date')->get()
            : collect();

        return view('reports.event-shift-hours', compact(
            'events', 'selectedEventIds', 'minHours', 'results', 'selectedEvents'
        ));
    }

    public function eventShiftHoursExportCsv(Request $request)
    {
        $selectedEventIds = array_filter((array) $request->input('event_ids', []));
        $minHours = (float) $request->input('min_hours', 20);

        if (empty($selectedEventIds)) {
            return redirect()->route('report.eventShiftHours')
                ->with('error', 'Please select at least one event to export.');
        }

        $results = $this->buildShiftHoursResults($selectedEventIds, $minHours);
        $selectedEvents = Event::whereIn('id', $selectedEventIds)->orderBy('start_date')->get();
        $eventNames = $selectedEvents->pluck('name')->join(' + ');

        $headers = ['Name', 'Email', 'Vol Code', 'Total Shift Hours', 'Shift Count', 'All Hours Credited', 'Shift Breakdown'];

        $rows = $results->map(function ($row) {
            $user   = $row['user'];
            $shifts = collect($row['shifts'])->sortBy(fn ($s) => $s['shift']->start_time);

            $breakdown = $shifts->map(function ($entry) {
                $shift = $entry['shift'];
                $label = $shift->start_time->format('M j Y g:iA') . '-' . $shift->end_time->format('g:iA')
                    . ' ' . $shift->name
                    . ($shift->double_hours ? ' [2x]' : '')
                    . ' (' . number_format($entry['hours'], 1) . 'h)'
                    . ($entry['credited'] ? ' [credited]' : '');
                return $label;
            })->join(' | ');

            $allCredited = $shifts->every(fn ($s) => $s['credited']) ? 'Yes' : 'No';

            return [
                $user->name,
                $user->email,
                $user->vol_code ?? '',
                number_format($row['total_hours'], 2),
                count($row['shifts']),
                $allCredited,
                $breakdown,
            ];
        });

        $csv  = implode(',', array_map(fn ($h) => '"' . $h . '"', $headers)) . "\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        $filename = 'event-shift-hours-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Shared query logic for eventShiftHoursReport and eventShiftHoursExportCsv.
     */
    private function buildShiftHoursResults(array $selectedEventIds, float $minHours): \Illuminate\Support\Collection
    {
        $shifts = Shift::with(['users' => function ($q) {
                $q->withPivot('no_show', 'hours_logged_at');
            }])
            ->whereIn('event_id', $selectedEventIds)
            ->get();

        $userHours  = [];
        $userMeta   = [];
        $userShifts = [];

        foreach ($shifts as $shift) {
            $duration = $shift->double_hours
                ? $shift->durationInHours() * 2
                : $shift->durationInHours();

            foreach ($shift->users as $user) {
                if ($user->pivot->no_show) {
                    continue;
                }

                $uid = $user->id;
                $userHours[$uid] = ($userHours[$uid] ?? 0) + $duration;
                $userMeta[$uid]  = $user;
                $userShifts[$uid][] = [
                    'shift'    => $shift,
                    'hours'    => $duration,
                    'credited' => !is_null($user->pivot->hours_logged_at),
                ];
            }
        }

        return collect($userHours)
            ->filter(fn ($hours) => $hours >= $minHours)
            ->sortByDesc(fn ($hours) => $hours)
            ->map(fn ($hours, $uid) => [
                'user'        => $userMeta[$uid],
                'total_hours' => $hours,
                'shifts'      => $userShifts[$uid],
            ])
            ->values();
    }
}
