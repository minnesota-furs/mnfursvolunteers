<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteStaffCheckInRequest;
use App\Http\Requests\StoreStaffCheckInSessionRequest;
use App\Models\CustomField;
use App\Models\Department;
use App\Models\Sector;
use App\Models\StaffCheckIn;
use App\Models\StaffCheckInSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DigitalStaffCheckInController extends Controller
{
    public function index(): View
    {
        $sessions = StaffCheckInSession::query()
            ->with(['sector', 'department.sector', 'creator'])
            ->withCount('checkIns')
            ->latest()
            ->get();

        return view('reports.staff-check-in-digital.index', compact('sessions'));
    }

    public function create(): View
    {
        $sectors = Sector::query()->orderBy('name')->get();
        $departments = Department::query()->with('sector')->orderBy('name')->get();
        $customFields = CustomField::active()->ordered()->get();

        return view('reports.staff-check-in-digital.create', compact('sectors', 'departments', 'customFields'));
    }

    public function store(StoreStaffCheckInSessionRequest $request): RedirectResponse
    {
        $checklistItems = collect($request->input('checklist_items', []))
            ->map(fn (?string $item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        $session = StaffCheckInSession::query()->create([
            'name' => (string) $request->string('name')->trim(),
            'scope' => $request->input('scope'),
            'sector_id' => $request->input('scope') === 'sector' ? $request->integer('sector_id') : null,
            'department_id' => $request->input('scope') === 'department' ? $request->integer('department_id') : null,
            'checklist_items' => $checklistItems,
            'custom_field_ids' => collect($request->input('custom_fields', []))->map(fn ($id) => (int) $id)->all(),
            'collect_signature' => $request->boolean('collect_signature'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('report.staffCheckIn.digital.show', $session)
            ->with('success', 'Check-in session created.');
    }

    public function show(StaffCheckInSession $staffCheckInSession): View
    {
        $staffCheckInSession->load(['sector', 'department.sector']);
        $checkInsByUser = $staffCheckInSession->checkIns()->get()->keyBy('user_id');
        $staff = $staffCheckInSession->eligibleUsers()
            ->with('departments')
            ->orderBy('name')
            ->get();

        return view('reports.staff-check-in-digital.show', compact(
            'staffCheckInSession', 'staff', 'checkInsByUser'
        ));
    }

    public function edit(StaffCheckInSession $staffCheckInSession): View
    {
        $sectors = Sector::query()->orderBy('name')->get();
        $departments = Department::query()->with('sector')->orderBy('name')->get();
        $customFields = CustomField::active()->ordered()->get();

        return view('reports.staff-check-in-digital.edit', compact(
            'staffCheckInSession', 'sectors', 'departments', 'customFields'
        ));
    }

    public function update(
        StoreStaffCheckInSessionRequest $request,
        StaffCheckInSession $staffCheckInSession
    ): RedirectResponse {
        $staffCheckInSession->update([
            'name' => (string) $request->string('name')->trim(),
            'scope' => $request->input('scope'),
            'sector_id' => $request->input('scope') === 'sector' ? $request->integer('sector_id') : null,
            'department_id' => $request->input('scope') === 'department' ? $request->integer('department_id') : null,
            'checklist_items' => collect($request->input('checklist_items', []))
                ->map(fn (?string $item) => trim((string) $item))->filter()->values()->all(),
            'custom_field_ids' => collect($request->input('custom_fields', []))
                ->map(fn ($id) => (int) $id)->all(),
            'collect_signature' => $request->boolean('collect_signature'),
        ]);

        return redirect()->route('report.staffCheckIn.digital.show', $staffCheckInSession)
            ->with('success', 'Check-in session updated.');
    }

    public function staff(StaffCheckInSession $staffCheckInSession, User $user): View
    {
        $this->ensureUserIsEligible($staffCheckInSession, $user);
        $selectedCustomFields = $staffCheckInSession->selectedCustomFields()->get();
        $user->load(['customFieldValues' => fn ($query) => $query->whereIn(
            'custom_field_id',
            $selectedCustomFields->pluck('id')
        )]);
        $checkIn = $staffCheckInSession->checkIns()->where('user_id', $user->id)->first();

        return view('reports.staff-check-in-digital.staff', compact(
            'staffCheckInSession', 'user', 'checkIn', 'selectedCustomFields'
        ));
    }

    public function complete(
        CompleteStaffCheckInRequest $request,
        StaffCheckInSession $staffCheckInSession,
        User $user
    ): RedirectResponse {
        $this->ensureUserIsEligible($staffCheckInSession, $user);
        $completedItems = collect($request->input('completed_items', []))->values();
        $invalidItems = $completedItems->diff($staffCheckInSession->checklist_items ?? []);

        if ($invalidItems->isNotEmpty()) {
            throw ValidationException::withMessages([
                'completed_items' => 'One or more checklist items do not belong to this session.',
            ]);
        }

        if ($staffCheckInSession->collect_signature && ! $request->filled('signature_data')) {
            throw ValidationException::withMessages([
                'signature_data' => 'Please collect the staff member’s signature.',
            ]);
        }

        StaffCheckIn::query()->updateOrCreate(
            [
                'staff_check_in_session_id' => $staffCheckInSession->id,
                'user_id' => $user->id,
            ],
            [
                'completed_items' => $completedItems->all(),
                'signature_data' => $request->input('signature_data'),
                'checked_in_by' => $request->user()->id,
                'checked_in_at' => now(),
            ]
        );

        return redirect()->route('report.staffCheckIn.digital.show', $staffCheckInSession)
            ->with('success', $user->name.' has been checked in. Select another staff member to continue.');
    }

    private function ensureUserIsEligible(StaffCheckInSession $staffCheckInSession, User $user): void
    {
        abort_unless($staffCheckInSession->eligibleUsers()->whereKey($user->id)->exists(), 404);
    }
}
