<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRosterRequest;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Event;
use App\Models\FiscalLedger;
use App\Models\Shift;
use App\Models\VolunteerHours;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepartmentManagementController extends Controller
{
    public function createRoster(Department $department): View
    {
        $this->authorize('manage', $department);

        $department->load([
            'heads' => fn ($query) => $query->orderBy('name'),
        ]);

        return view('departments.rosters.create', compact('department'));
    }

    public function show(Request $request, Department $department): View
    {
        $this->authorize('manage', $department);

        $department->load('sector');
        $currentLedger = FiscalLedger::query()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $membersQuery = $this->membersQuery($request, $department, $currentLedger);
        $members = $membersQuery->paginate(25)->withQueryString();
        $memberIds = $department->users()->pluck('users.id');

        $upcomingShifts = Shift::query()
            ->with([
                'event:id,name',
                'users' => fn ($query) => $query
                    ->whereIn('users.id', $memberIds)
                    ->select('users.id', 'users.name'),
            ])
            ->where('start_time', '>=', now())
            ->whereHas('users', fn (Builder $query) => $query->whereIn('users.id', $memberIds))
            ->orderBy('start_time')
            ->limit(12)
            ->get();

        $departmentHours = VolunteerHours::query()
            ->where('primary_dept_id', $department->id);

        $bccList = $department->users()
            ->where('active', true)
            ->whereNotNull('email')
            ->orderBy('name')
            ->get(['users.name', 'users.email'])
            ->map(fn ($member): string => "{$member->displayName()} <{$member->email}>")
            ->join(', ');

        $departmentEvents = Event::query()
            ->with(['creator:id,name', 'editors:id,name', 'shifts.users:id'])
            ->withCount('shifts')
            ->whereHas('requiredDepartments', fn (Builder $query) => $query->whereKey($department->id))
            ->orderByDesc('start_date')
            ->limit(20)
            ->get();

        return view('departments.manage', [
            'department' => $department,
            'members' => $members,
            'currentLedger' => $currentLedger,
            'upcomingShifts' => $upcomingShifts,
            'bccList' => $bccList,
            'departmentEvents' => $departmentEvents,
            'summary' => [
                'staff' => $department->users()->count(),
                'active' => $department->users()->where('active', true)->count(),
                'inactive' => $department->users()->where('active', false)->count(),
                'current_hours' => $currentLedger
                    ? (clone $departmentHours)->where('fiscal_ledger_id', $currentLedger->id)->sum('hours')
                    : 0,
            ],
        ]);
    }

    public function storeRoster(
        StoreDepartmentRosterRequest $request,
        Department $department
    ): RedirectResponse {
        $this->authorize('manage', $department);

        $event = DB::transaction(function () use ($request, $department): Event {
            $event = Event::query()->create([
                ...$request->safe()->only([
                    'name',
                    'description',
                    'start_date',
                    'end_date',
                    'location',
                    'visibility',
                ]),
                'require_eligibility' => true,
                'created_by' => $request->user()->id,
            ]);

            $event->requiredDepartments()->sync([$department->id]);
            $event->editors()->sync(
                $department->heads()
                    ->where('users.id', '!=', $request->user()->id)
                    ->pluck('users.id')
                    ->all()
            );

            return $event;
        });

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Staffing roster <span class=\"text-brand-green\">{$event->name}</span> created. Add coverage shifts below.",
                'action_text' => 'Back to Department',
                'action_url' => route('departments.manage', $department),
            ]);
    }

    public function export(Request $request, Department $department): StreamedResponse
    {
        $this->authorize('manage', $department);

        $currentLedger = FiscalLedger::query()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $members = $this->membersQuery($request, $department, $currentLedger)->get();

        AuditLog::query()->create([
            'action' => 'exported',
            'auditable_type' => Department::class,
            'auditable_id' => $department->id,
            'changes' => [
                'format' => 'csv',
                'staff_count' => $members->count(),
                'filters' => $request->only(['search', 'status']),
            ],
            'comment' => 'Department staff list exported.',
            'user_id' => $request->user()->id,
        ]);

        $filename = (string) str($department->name)->slug()->append('-staff.csv');

        return Response::streamDownload(function () use ($members): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Display Name',
                'Email',
                'Status',
                'Volunteer Code',
                'Department Joined',
                'Current Period Hours',
                'Lifetime Department Hours',
                'Last Volunteered',
            ]);

            foreach ($members as $member) {
                fputcsv($output, [
                    $this->csvValue($member->displayName()),
                    $this->csvValue($member->email),
                    $member->active ? 'Active' : 'Inactive',
                    $member->vol_code,
                    $member->pivot?->created_at?->toDateString(),
                    $member->current_period_hours ?? 0,
                    $member->lifetime_department_hours ?? 0,
                    $member->last_volunteered_at,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function csvValue(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'{$value}" : $value;
    }

    private function membersQuery(
        Request $request,
        Department $department,
        ?FiscalLedger $currentLedger
    ): BelongsToMany {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        return $department->users()
            ->select([
                'users.id',
                'users.name',
                'users.first_name',
                'users.last_name',
                'users.pronouns',
                'users.email',
                'users.active',
                'users.vol_code',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.first_name', 'like', "%{$search}%")
                        ->orWhere('users.last_name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('users.vol_code', $search);
                });
            })
            ->when($status === 'active', fn (Builder $query) => $query->where('users.active', true))
            ->when($status === 'inactive', fn (Builder $query) => $query->where('users.active', false))
            ->withSum([
                'volunteerHours as lifetime_department_hours' => fn (Builder $query) => $query
                    ->where('primary_dept_id', $department->id),
            ], 'hours')
            ->when(
                $currentLedger,
                fn (Builder $query) => $query->withSum([
                    'volunteerHours as current_period_hours' => fn (Builder $query) => $query
                        ->where('primary_dept_id', $department->id)
                        ->where('fiscal_ledger_id', $currentLedger->id),
                ], 'hours')
            )
            ->withMax([
                'volunteerHours as last_volunteered_at' => fn (Builder $query) => $query
                    ->where('primary_dept_id', $department->id),
            ], 'volunteer_date')
            ->orderBy('users.name');
    }
}
