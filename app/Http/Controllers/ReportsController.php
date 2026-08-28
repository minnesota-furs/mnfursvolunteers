<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomFieldReportRequest;
use App\Http\Requests\DepartmentReportRequest;
use App\Http\Requests\StaffCheckInReportRequest;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\Event;
use App\Models\FiscalLedger;
use App\Models\Sector;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserRelationship;
use App\Services\ConcatService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function staffCheckInExperience(): View
    {
        return view('reports.staff-check-in-experience');
    }

    public function staffCheckIn(StaffCheckInReportRequest $request): View
    {
        return view('reports.staff-check-in', $this->buildStaffCheckInReportData($request));
    }

    public function staffCheckInPrint(StaffCheckInReportRequest $request): View|RedirectResponse
    {
        $reportData = $this->buildStaffCheckInReportData($request);

        if ($reportData['staff'] === null) {
            return redirect()->route('report.staffCheckIn')
                ->with('error', 'Please generate a staff check-in report before printing.');
        }

        return view('reports.staff-check-in-print', $reportData);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStaffCheckInReportData(StaffCheckInReportRequest $request): array
    {
        $scope = $request->input('scope', '');
        $selectedSectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;
        $selectedDepartmentId = $request->filled('department_id') ? $request->integer('department_id') : null;
        $selectedCustomFieldIds = collect($request->input('custom_fields', []))->map(fn ($id) => (int) $id);
        $checklistItems = collect($request->input('checklist_items', []))
            ->map(fn (?string $item) => trim((string) $item))
            ->filter()
            ->values();
        $includeSignature = $request->boolean('include_signature');
        $groupAlphabetically = $request->boolean('group_alphabetically');
        $alphabeticalBy = $request->input('alphabetical_by', 'name');
        $listLegalName = $request->boolean('list_legal_name');
        $sectors = Sector::query()->orderBy('name')->get();
        $departments = Department::query()->with('sector')->orderBy('name')->get();
        $customFields = CustomField::active()->ordered()->get();
        $selectedCustomFields = $customFields->whereIn('id', $selectedCustomFieldIds)->values();
        $staff = null;
        $staffGroups = null;
        $reportGroupName = null;

        if ($scope === 'sector' && $selectedSectorId) {
            $sector = $sectors->firstWhere('id', $selectedSectorId);
            $reportGroupName = $sector?->name;
            $staff = $this->buildStaffCheckInQuery($selectedCustomFieldIds, $scope, $selectedSectorId, $alphabeticalBy)
                ->whereHas('departments', fn ($query) => $query->where('sector_id', $selectedSectorId))
                ->get();
        }

        if ($scope === 'department' && $selectedDepartmentId) {
            $department = $departments->firstWhere('id', $selectedDepartmentId);
            $reportGroupName = $department ? $department->sector->name.': '.$department->name : null;
            $staff = $this->buildStaffCheckInQuery($selectedCustomFieldIds, $scope, $selectedDepartmentId, $alphabeticalBy)
                ->whereHas('departments', fn ($query) => $query->whereKey($selectedDepartmentId))
                ->get();
        }

        if ($staff !== null) {
            $staffGroups = $groupAlphabetically
                ? $staff->groupBy(fn (User $user) => Str::upper(Str::substr(
                    (string) ($user->{$alphabeticalBy} ?: $user->name),
                    0,
                    1
                )))
                : collect(['' => $staff]);
        }

        return compact(
            'scope', 'selectedSectorId', 'selectedDepartmentId', 'selectedCustomFieldIds', 'checklistItems',
            'includeSignature', 'groupAlphabetically', 'alphabeticalBy', 'listLegalName', 'sectors',
            'departments', 'customFields', 'selectedCustomFields', 'staff', 'staffGroups', 'reportGroupName'
        );
    }

    private function buildStaffCheckInQuery(
        Collection $customFieldIds,
        string $scope,
        int $groupId,
        string $alphabeticalBy
    ): Builder {
        return User::query()
            ->where('active', true)
            ->with([
                'departments' => fn ($query) => $query
                    ->when(
                        $scope === 'sector',
                        fn ($query) => $query->where('sector_id', $groupId),
                        fn ($query) => $query->whereKey($groupId)
                    )
                    ->orderBy('name'),
                'customFieldValues' => fn ($query) => $query->whereIn('custom_field_id', $customFieldIds),
            ])
            ->orderBy($alphabeticalBy)
            ->orderBy('name');
    }

    public function volunteersWithMultipleDepartments(DepartmentReportRequest $request): View
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'department_count');
        $direction = $request->input('direction', 'desc');

        $users = User::query()
            ->where('active', true)
            ->has('departments', '>=', 2)
            ->with(['departments' => fn ($query) => $query->with('sector')->orderBy('name')])
            ->withCount(['departments as department_count'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy($sort, $direction)
            ->paginate(25)
            ->withQueryString();

        return view('reports.volunteers-with-multiple-departments', compact(
            'users', 'search', 'sort', 'direction'
        ));
    }

    public function departmentMembership(DepartmentReportRequest $request): View
    {
        $monthCount = $request->integer('months', 12);
        $selectedSectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;
        $sectors = Sector::query()->orderBy('name')->get();
        $months = collect(range($monthCount - 1, 0))
            ->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));

        $departments = Department::query()
            ->when($selectedSectorId, fn ($query) => $query->where('sector_id', $selectedSectorId))
            ->with('sector')
            ->with(['users' => fn ($query) => $query->where('active', true)])
            ->withCount(['users as active_users_count' => fn ($query) => $query->where('active', true)])
            ->orderByDesc('active_users_count')
            ->orderBy('name')
            ->get()
            ->each(function (Department $department) use ($months) {
                $department->monthly_memberships = $months->mapWithKeys(
                    fn ($month) => [
                        $month->format('Y-m') => $department->users
                            ->filter(fn (User $user) => $user->pivot->created_at?->isSameMonth($month))
                            ->count(),
                    ]
                );
            });

        $activeVolunteerCount = User::query()
            ->where('active', true)
            ->whereHas('departments', fn ($query) => $query
                ->when($selectedSectorId, fn ($query) => $query->where('sector_id', $selectedSectorId)))
            ->count();
        $activeMembershipCount = $departments->sum('active_users_count');
        $monthlyTotals = $months->mapWithKeys(fn ($month) => [
            $month->format('Y-m') => $departments->sum(
                fn (Department $department) => $department->monthly_memberships->get($month->format('Y-m'), 0)
            ),
        ]);
        $multipleDepartmentCount = User::query()
            ->where('active', true)
            ->whereHas(
                'departments',
                fn ($query) => $query
                    ->when($selectedSectorId, fn ($query) => $query->where('sector_id', $selectedSectorId)),
                '>=',
                2
            )
            ->count();

        return view('reports.department-membership', compact(
            'departments', 'sectors', 'selectedSectorId', 'months', 'monthCount',
            'activeVolunteerCount', 'activeMembershipCount', 'monthlyTotals',
            'multipleDepartmentCount'
        ));
    }

    public function staffConcat(ConcatService $concat): View|RedirectResponse
    {
        if (! $concat->isConfigured()) {
            return redirect()->route('settings.index')
                ->with('error', 'Connect ConCat under External Integrations before viewing this report.');
        }

        return view('reports.staff-concat-experience');
    }

    public function staffConcatUnlinked(Request $request, ConcatService $concat): View|RedirectResponse
    {
        if ($redirect = $this->requireConcatConfigured($concat)) {
            return $redirect;
        }

        [$sectors, $selectedSectorId] = $this->staffConcatFilters($request);

        $unlinkedUsers = $this->staffConcatBaseQuery($selectedSectorId)
            ->whereNull('concat_user_id')->orderBy('name')->get();

        return view('reports.staff-concat-unlinked', compact('sectors', 'selectedSectorId', 'unlinkedUsers'));
    }

    public function staffConcatUnlinkedExportCsv(Request $request, ConcatService $concat): StreamedResponse|RedirectResponse
    {
        if ($redirect = $this->requireConcatConfigured($concat)) {
            return $redirect;
        }

        $selectedSectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;
        $unlinkedUsers = $this->staffConcatBaseQuery($selectedSectorId)
            ->whereNull('concat_user_id')->orderBy('name')->get();

        return $this->streamStaffConcatCsv($unlinkedUsers, 'unlinked-concat-users');
    }

    public function staffConcatWithRegistration(Request $request, ConcatService $concat): View|RedirectResponse
    {
        if ($redirect = $this->requireConcatConfigured($concat)) {
            return $redirect;
        }

        [$sectors, $selectedSectorId] = $this->staffConcatFilters($request);
        [$staffWithRegistration] = $this->staffConcatLinkedSplit($concat, $selectedSectorId);

        return view('reports.staff-concat-with-registration', compact('sectors', 'selectedSectorId', 'staffWithRegistration'));
    }

    public function staffConcatWithRegistrationExportCsv(Request $request, ConcatService $concat): StreamedResponse|RedirectResponse
    {
        if ($redirect = $this->requireConcatConfigured($concat)) {
            return $redirect;
        }

        $selectedSectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;
        [$staffWithRegistration] = $this->staffConcatLinkedSplit($concat, $selectedSectorId);

        return $this->streamStaffConcatCsv($staffWithRegistration, 'staff-with-concat-registration');
    }

    public function staffConcatWithoutRegistration(Request $request, ConcatService $concat): View|RedirectResponse
    {
        if ($redirect = $this->requireConcatConfigured($concat)) {
            return $redirect;
        }

        [$sectors, $selectedSectorId] = $this->staffConcatFilters($request);
        [, $staffWithoutRegistration] = $this->staffConcatLinkedSplit($concat, $selectedSectorId);

        return view('reports.staff-concat-without-registration', compact('sectors', 'selectedSectorId', 'staffWithoutRegistration'));
    }

    public function staffConcatWithoutRegistrationExportCsv(Request $request, ConcatService $concat): StreamedResponse|RedirectResponse
    {
        if ($redirect = $this->requireConcatConfigured($concat)) {
            return $redirect;
        }

        $selectedSectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;
        [, $staffWithoutRegistration] = $this->staffConcatLinkedSplit($concat, $selectedSectorId);

        return $this->streamStaffConcatCsv($staffWithoutRegistration, 'staff-without-concat-registration');
    }

    private function requireConcatConfigured(ConcatService $concat): ?RedirectResponse
    {
        if ($concat->isConfigured()) {
            return null;
        }

        return redirect()->route('settings.index')
            ->with('error', 'Connect ConCat under External Integrations before viewing this report.');
    }

    /**
     * @return array{0: Collection, 1: int|null}
     */
    private function staffConcatFilters(Request $request): array
    {
        $sectors = Sector::query()->orderBy('name')->get();
        $selectedSectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;

        return [$sectors, $selectedSectorId];
    }

    private function staffConcatBaseQuery(?int $selectedSectorId): Builder
    {
        return User::query()
            ->where('active', true)
            ->whereHas('departments', fn ($query) => $query
                ->when($selectedSectorId, fn ($query) => $query->where('departments.sector_id', $selectedSectorId)))
            ->with('departments.sector');
    }

    /**
     * Linked staff (in the given sector, or all if null) split into those
     * with a ConCat registration and those without. Requires one batched
     * ConCat call per invocation, so callers should only call this once.
     *
     * @return array{0: Collection, 1: Collection}
     */
    private function staffConcatLinkedSplit(ConcatService $concat, ?int $selectedSectorId): array
    {
        $linkedUsers = $this->staffConcatBaseQuery($selectedSectorId)
            ->whereNotNull('concat_user_id')->orderBy('name')->get();

        $registeredConcatIds = collect();
        if ($linkedUsers->isNotEmpty()) {
            $registrations = $concat->searchRegistrationsByUserIds($linkedUsers->pluck('concat_user_id')->all());
            $registeredConcatIds = collect($registrations)->pluck('user.id')->filter()->unique();
        }

        [$withRegistration, $withoutRegistration] = $linkedUsers
            ->partition(fn (User $user) => $registeredConcatIds->contains($user->concat_user_id));

        return [$withRegistration->values(), $withoutRegistration->values()];
    }

    private function streamStaffConcatCsv(Collection $users, string $filenamePrefix): StreamedResponse
    {
        $filename = $filenamePrefix.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($users): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Name', 'Email', 'Departments']);

            foreach ($users as $user) {
                fputcsv($output, [
                    $user->name,
                    $user->email,
                    $user->departments->pluck('name')->implode(', '),
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function customFields(CustomFieldReportRequest $request): View
    {
        $customFields = CustomField::active()->ordered()->get();
        $sectors = Sector::query()->orderBy('name')->get();
        $selectedSectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;
        $selectedField = $request->filled('custom_field_id')
            ? $customFields->firstWhere('id', $request->integer('custom_field_id'))
            : null;
        $mode = $request->input('mode', 'count');
        $search = $request->input('search');
        $responseFilter = $request->input('response');
        $counts = collect();
        $responseOptions = collect();
        $users = null;

        if ($selectedField && $mode === 'count') {
            $counts = $this->buildCustomFieldCounts($selectedField, $selectedSectorId);
        }

        if ($selectedField && $mode === 'people') {
            $responseOptions = $this->buildCustomFieldCounts($selectedField, $selectedSectorId)->keys();
            $users = $this->buildCustomFieldPeopleQuery(
                $selectedField,
                $selectedSectorId,
                $search,
                $responseFilter
            )
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString();
        }

        return view('reports.custom-fields', compact(
            'customFields', 'sectors', 'selectedSectorId', 'selectedField', 'mode', 'search', 'responseFilter',
            'responseOptions', 'counts', 'users'
        ));
    }

    public function customFieldsExportCsv(CustomFieldReportRequest $request): StreamedResponse
    {
        $customField = CustomField::active()->findOrFail($request->integer('custom_field_id'));
        $sectorId = $request->filled('sector_id') ? $request->integer('sector_id') : null;
        $mode = $request->input('mode', 'count');
        $filename = (string) str($customField->name)->slug()->append('-responses-', now()->format('Y-m-d'), '.csv');

        if ($mode === 'count') {
            $counts = $this->buildCustomFieldCounts($customField, $sectorId);

            return response()->streamDownload(function () use ($counts): void {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Response', 'Volunteers']);

                foreach ($counts as $response => $count) {
                    fputcsv($output, [$response, $count]);
                }

                fclose($output);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $users = $this->buildCustomFieldPeopleQuery(
            $customField,
            $sectorId,
            $request->input('search'),
            $request->input('response')
        )->orderBy('name')->get();

        return response()->streamDownload(function () use ($customField, $users): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Volunteer', 'Email', $customField->name]);

            foreach ($users as $user) {
                fputcsv($output, [
                    $user->name,
                    $user->email,
                    $user->customFieldValues->first()?->value ?: 'Not provided',
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildCustomFieldPeopleQuery(
        CustomField $customField,
        ?int $sectorId,
        ?string $search,
        ?string $responseFilter
    ): Builder {
        return User::query()
            ->where('active', true)
            ->when($sectorId, fn ($query) => $query->whereHas(
                'departments',
                fn ($query) => $query->where('sector_id', $sectorId)
            ))
            ->with(['customFieldValues' => fn ($query) => $query
                ->where('custom_field_id', $customField->id)])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($responseFilter === 'Not provided', fn ($query) => $query->whereDoesntHave(
                'customFieldValues',
                fn ($query) => $query
                    ->where('custom_field_id', $customField->id)
                    ->whereNotNull('value')
                    ->where('value', '!=', '')
            ))
            ->when($responseFilter && $responseFilter !== 'Not provided', function ($query) use ($customField, $responseFilter) {
                $query->whereHas('customFieldValues', function ($query) use ($customField, $responseFilter) {
                    $query->where('custom_field_id', $customField->id)
                        ->where(function ($query) use ($customField, $responseFilter) {
                            $query->where('value', $responseFilter);

                            if ($customField->field_type === 'checkbox') {
                                $query->orWhere('value', 'like', $responseFilter.',%')
                                    ->orWhere('value', 'like', '%,'.$responseFilter)
                                    ->orWhere('value', 'like', '%,'.$responseFilter.',%');
                            }
                        });
                });
            });
    }

    private function buildCustomFieldCounts(CustomField $customField, ?int $sectorId = null): Collection
    {
        $values = CustomFieldValue::query()
            ->where('custom_field_id', $customField->id)
            ->whereHas('user', fn ($query) => $query
                ->where('active', true)
                ->when($sectorId, fn ($query) => $query->whereHas(
                    'departments',
                    fn ($query) => $query->where('sector_id', $sectorId)
                )))
            ->pluck('value');

        $counts = $values
            ->flatMap(fn (?string $value) => $customField->field_type === 'checkbox'
                ? array_filter(array_map('trim', explode(',', (string) $value)))
                : [trim((string) $value)])
            ->filter()
            ->countBy();

        if (in_array($customField->field_type, ['select', 'checkbox'])) {
            $orderedCounts = collect($customField->options)
                ->mapWithKeys(fn (string $option) => [$option => $counts->get($option, 0)]);
            $counts = $orderedCounts->merge($counts->except($orderedCounts->keys()));
        } else {
            $counts = $counts->sortKeys();
        }

        $answeredUserCount = CustomFieldValue::query()
            ->where('custom_field_id', $customField->id)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->whereHas('user', fn ($query) => $query
                ->where('active', true)
                ->when($sectorId, fn ($query) => $query->whereHas(
                    'departments',
                    fn ($query) => $query->where('sector_id', $sectorId)
                )))
            ->distinct('user_id')
            ->count('user_id');
        $activeUserCount = User::query()
            ->where('active', true)
            ->when($sectorId, fn ($query) => $query->whereHas(
                'departments',
                fn ($query) => $query->where('sector_id', $sectorId)
            ))
            ->count();
        $unansweredUserCount = $activeUserCount - $answeredUserCount;

        if ($unansweredUserCount > 0) {
            $counts->put('Not provided', $unansweredUserCount);
        }

        return $counts;
    }

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

        return view('reports.users', compact('users', 'sort', 'direction', 'search', 'reportTitle', 'reportDescription'));
    }

    public function usersWithoutHoursThisPeriod(Request $request)
    {
        $reportTitle = 'Users Without Hours This Period';
        $reportDescription = 'This report lists all users who have not logged any hours in the current fiscal period.';

        $search = $request->input('search');

        $sort = $request->input('sort', 'name'); // Default sort column
        $direction = $request->input('direction', 'asc'); // Default sort direction

        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (! $currentLedger) {
            return back()->with('error', 'No current fiscal ledger found.');
        }

        $users = User::whereDoesntHave('volunteerHours', function ($query) use ($currentLedger) {
            $query->where('fiscal_ledger_id', $currentLedger->id);
        })->where('active', 1)
            ->paginate(15);

        return view('reports.users', compact('users', 'currentLedger', 'sort', 'direction', 'search', 'reportTitle', 'reportDescription'));
    }

    public function eventShiftHoursReport(Request $request)
    {
        $events = Event::orderBy('start_date', 'desc')->get();
        $selectedEventIds = array_filter((array) $request->input('event_ids', []));
        $minHours = (float) $request->input('min_hours', 20);
        $results = collect();

        if ($request->has('event_ids') && ! empty($selectedEventIds)) {
            $results = $this->buildShiftHoursResults($selectedEventIds, $minHours);
        }

        $selectedEvents = ! empty($selectedEventIds)
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
            $user = $row['user'];
            $shifts = collect($row['shifts'])->sortBy(fn ($s) => $s['shift']->start_time);

            $breakdown = $shifts->map(function ($entry) {
                $shift = $entry['shift'];
                $label = $shift->start_time->format('M j Y g:iA').'-'.$shift->end_time->format('g:iA')
                    .' '.$shift->name
                    .($shift->double_hours ? ' [2x]' : '')
                    .' ('.number_format($entry['hours'], 1).'h)'
                    .($entry['credited'] ? ' [credited]' : '');

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

        $csv = implode(',', array_map(fn ($h) => '"'.$h.'"', $headers))."\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', $v).'"', $row))."\n";
        }

        $filename = 'event-shift-hours-'.now()->format('Y-m-d').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Shared query logic for eventShiftHoursReport and eventShiftHoursExportCsv.
     */
    private function buildShiftHoursResults(array $selectedEventIds, float $minHours): Collection
    {
        $shifts = Shift::with(['users' => function ($q) {
            $q->withPivot('no_show', 'hours_logged_at');
        }])
            ->whereIn('event_id', $selectedEventIds)
            ->get();

        $userHours = [];
        $userMeta = [];
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
                $userMeta[$uid] = $user;
                $userShifts[$uid][] = [
                    'shift' => $shift,
                    'hours' => $duration,
                    'credited' => ! is_null($user->pivot->hours_logged_at),
                ];
            }
        }

        return collect($userHours)
            ->filter(fn ($hours) => $hours >= $minHours)
            ->sortByDesc(fn ($hours) => $hours)
            ->map(fn ($hours, $uid) => [
                'user' => $userMeta[$uid],
                'total_hours' => $hours,
                'shifts' => $userShifts[$uid],
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

        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
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
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'no_show_count';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
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

        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }
        if (! in_array($days, $allowedDays)) {
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
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
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
