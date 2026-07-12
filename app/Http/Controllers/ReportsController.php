<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Event;
use App\Models\Shift;
use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\UserRelationship;

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

    public function volunteerRelationships(Request $request)
    {
        $typeFilter = $request->input('type', 'all');
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $allowedSorts = ['created_at', 'user_name', 'target_name', 'type'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = UserRelationship::with(['user.departments', 'targetUser.departments']);

        if ($typeFilter !== 'all') {
            $query->where('type', $typeFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('targetUser', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($sort === 'user_name') {
            $query->join('users as u', 'user_relationships.user_id', '=', 'u.id')
                  ->orderBy('u.name', $direction)
                  ->select('user_relationships.*');
        } elseif ($sort === 'target_name') {
            $query->join('users as t', 'user_relationships.target_user_id', '=', 't.id')
                  ->orderBy('t.name', $direction)
                  ->select('user_relationships.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $relationships = $query->paginate(25)->withQueryString();

        // Summary stats
        $totalFavorites = UserRelationship::where('type', 'favorite')->count();
        $totalAvoids = UserRelationship::where('type', 'avoid')->count();
        $uniqueUsers = UserRelationship::distinct('user_id')->count('user_id');

        // Most avoided users (top 5)
        $mostAvoided = UserRelationship::where('type', 'avoid')
            ->selectRaw('target_user_id, count(*) as avoid_count')
            ->groupBy('target_user_id')
            ->orderByDesc('avoid_count')
            ->limit(5)
            ->with('targetUser')
            ->get();

        return view('reports.volunteer-relationships', compact(
            'relationships', 'typeFilter', 'search', 'sort', 'direction',
            'totalFavorites', 'totalAvoids', 'uniqueUsers', 'mostAvoided'
        ));
    }

    public function noShows(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'no_show_count');
        $direction = $request->input('direction', 'desc');
        $eventId = $request->input('event_id');

        $allowedSorts = ['no_show_count', 'name', 'email', 'latest_no_show'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'no_show_count';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $events = Event::orderBy('start_date', 'desc')->get();

        // Build the query for users with no-shows
        $query = User::query()
            ->whereHas('shifts', function ($q) use ($eventId) {
                $q->where('shift_signups.no_show', true);
                if ($eventId) {
                    $q->where('shifts.event_id', $eventId);
                }
            })
            ->withCount(['shifts as no_show_count' => function ($q) use ($eventId) {
                $q->where('shift_signups.no_show', true);
                if ($eventId) {
                    $q->where('shifts.event_id', $eventId);
                }
            }])
            ->withMax(['shifts as latest_no_show' => function ($q) use ($eventId) {
                $q->where('shift_signups.no_show', true);
                if ($eventId) {
                    $q->where('shifts.event_id', $eventId);
                }
            }], 'shift_signups.no_show_marked_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort, $direction);

        $users = $query->paginate(25)->withQueryString();

        // Summary stats
        $totalNoShows = \DB::table('shift_signups')
            ->where('no_show', true)
            ->when($eventId, fn ($q) => $q->whereIn('shift_id', Shift::where('event_id', $eventId)->pluck('id')))
            ->count();

        $uniqueNoShowUsers = \DB::table('shift_signups')
            ->where('no_show', true)
            ->when($eventId, fn ($q) => $q->whereIn('shift_id', Shift::where('event_id', $eventId)->pluck('id')))
            ->distinct('user_id')
            ->count('user_id');

        $repeatOffenders = \DB::table('shift_signups')
            ->where('no_show', true)
            ->when($eventId, fn ($q) => $q->whereIn('shift_id', Shift::where('event_id', $eventId)->pluck('id')))
            ->selectRaw('user_id, count(*) as cnt')
            ->groupBy('user_id')
            ->having('cnt', '>=', 2)
            ->count();

        // Recent no-shows (last 30 days) with shift & event details
        $recentNoShows = \DB::table('shift_signups')
            ->join('shifts', 'shift_signups.shift_id', '=', 'shifts.id')
            ->join('events', 'shifts.event_id', '=', 'events.id')
            ->join('users', 'shift_signups.user_id', '=', 'users.id')
            ->where('shift_signups.no_show', true)
            ->where('shift_signups.no_show_marked_at', '>=', now()->subDays(30))
            ->when($eventId, fn ($q) => $q->where('shifts.event_id', $eventId))
            ->select('users.name as user_name', 'users.id as user_id', 'shifts.name as shift_name', 'events.name as event_name', 'shift_signups.no_show_marked_at')
            ->orderByDesc('shift_signups.no_show_marked_at')
            ->limit(10)
            ->get();

        return view('reports.no-shows', compact(
            'users', 'search', 'sort', 'direction', 'events', 'eventId',
            'totalNoShows', 'uniqueNoShowUsers', 'repeatOffenders', 'recentNoShows'
        ));
    }

    public function newSignupsWithNoShifts(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $allowedSorts = ['name', 'email', 'created_at'];
        $allowedDays = [30, 60, 90, 0];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }
        if (!in_array($days, $allowedDays)) {
            $days = 30;
        }

        $query = User::query()
            ->doesntHave('shifts')
            ->where('active', 1)
            ->with('departments');

        if ($days > 0) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort, $direction);

        $users = $query->paginate(25)->withQueryString();

        // Summary stats (always unfiltered by current $days/$search)
        $totalLast30 = User::doesntHave('shifts')->where('active', 1)
            ->where('created_at', '>=', now()->subDays(30))->count();

        $totalLast60 = User::doesntHave('shifts')->where('active', 1)
            ->where('created_at', '>=', now()->subDays(60))->count();

        $totalAllTime = User::doesntHave('shifts')->where('active', 1)->count();

        return view('reports.new-signups-no-shifts', compact(
            'users', 'search', 'sort', 'direction', 'days',
            'totalLast30', 'totalLast60', 'totalAllTime'
        ));
    }

    public function departmentsWithoutHead(Request $request)
    {
        $reportTitle = 'Departments Without Head';
        $reportDescription = 'This report lists all departments that do not have a head assigned.';
        $search = $request->input('search');

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['name', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query = Department::query()
            ->doesntHave('heads')
            ->with('sector');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->orderBy($sort, $direction);

        $departments = $query->paginate(25)->withQueryString();

        return view('reports.departments-without-head', compact(
            'departments', 'search', 'sort', 'direction', 'reportTitle', 'reportDescription'
        ));
    }

    public function destroyRelationship(UserRelationship $relationship)
    {
        $relationship->delete();

        return back()->with('success', 'Relationship removed.');
    }
}
