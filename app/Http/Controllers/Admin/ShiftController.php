<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvancedDuplicateShiftRequest;
use App\Models\Event;
use App\Models\Shift;
use App\Models\Tag;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Statement;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $shifts = $event->shifts()->with(['users', 'tags'])->orderBy('start_time', 'asc')->get();
        return view('admin.shifts.index', compact('event', 'shifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        $tags = Tag::forShifts()->orderBy('name')->get();
        return view('admin.shifts.create', compact('event', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_time'     => 'required|date',
            'end_time'       => 'required|date|after:start_time',
            'max_volunteers' => 'required|integer|min:1',
            'double_hours'   => 'nullable|boolean',
            'user_id'        => 'nullable|array',
            'user_id.*'      => 'integer|exists:users,id',
            'shift_tags'     => 'nullable|array',
            'shift_tags.*'   => 'integer|exists:tags,id',
        ]);

        $shiftData = $request->only(['name', 'description', 'start_time', 'end_time', 'max_volunteers']);
        $shiftData['double_hours'] = $request->has('double_hours');

        $shift = $event->shifts()->create($shiftData);

        if ($request->filled('user_id')) {
            $userIds = $request->input('user_id');
            $userIds = is_array($userIds) ? $userIds : [$userIds];
            $shift->users()->syncWithoutDetaching($userIds);
        }

        $shift->tags()->sync($request->input('shift_tags', []));

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Shift <span class=\"text-brand-green\">{$shift->name}</span> created successfully",
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event, Shift $shift)
    {
        $tags = Tag::forShifts()->orderBy('name')->get();
        return view('admin.shifts.create', compact('event', 'shift', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event, Shift $shift)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'start_time'     => 'required|date',
            'end_time'       => 'required|date|after:start_time',
            'max_volunteers' => 'required|integer|min:1',
            'double_hours'   => 'nullable|boolean',
            'user_id'        => 'nullable|array',
            'user_id.*'      => 'integer|exists:users,id',
            'shift_tags'     => 'nullable|array',
            'shift_tags.*'   => 'integer|exists:tags,id',
        ]);

        $updateData = $request->only(['name', 'description', 'start_time', 'double_hours', 'end_time', 'max_volunteers']);
        $updateData['double_hours'] = $request->has('double_hours');

        $shift->update($updateData);

        if ($request->filled('user_id')) {
            $userIds = $request->input('user_id');
            $userIds = is_array($userIds) ? $userIds : [$userIds];
            $shift->users()->syncWithoutDetaching($userIds);
        }

        $shift->tags()->sync($request->input('shift_tags', []));

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Shift <span class=\"text-brand-green\">{$shift->name}</span> updated successfully",
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Shift $shift)
    {
        $shift->delete();
        return back()->with('success', [
            'message' => "Shift <span class=\"text-brand-green\">{$shift->name}</span> deleted",
        ]);
    }

    public function duplicate(Event $event, Shift $shift)
    {
        $newShift = $shift->replicate();
        $newShift->name = $shift->name . ' (Copy)';
        $newShift->start_time = $newShift->start_time->addHour();
        $newShift->end_time = $newShift->end_time->addHour(1);
        $newShift->save();

        $event->shifts()->save($newShift);
        $newShift->tags()->sync($shift->tags->pluck('id'));

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Shift <span class=\"text-brand-green\">{$shift->name}</span> duplicated successfully",
            ]); 
    }

    public function removeVolunteer(Event $event, Shift $shift, User $user)
    {
        $shift->users()->detach($user->id);

        AuditLog::create([
            'action'         => 'shift_volunteer_removed',
            'auditable_type' => Event::class,
            'auditable_id'   => $shift->event->id,
            'comment'        => "User {$user->name} removed from {$shift->name} (ID: {$shift->id}) by " . auth()->user()->name,
            'user_id'        => auth()->id(),
        ]);

        // Return JSON for AJAX requests
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$user->name} has been removed from the shift.",
                'shift_count' => $shift->users()->count()
            ]);
        }

        return redirect()->back()
            ->with('success', [
                'message' => "<span class=\"text-brand-green\">{$user->name}</span> removed from shift",
            ]); 
    }

    public function addVolunteer(Event $event, Shift $shift, User $user)
    {
        // Check if user is already assigned to this shift
        if ($shift->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$user->name} is already assigned to this shift."
            ], 400);
        }

        // Check if shift is full
        if ($shift->users()->count() >= $shift->max_volunteers) {
            return response()->json([
                'success' => false,
                'message' => "This shift is full ({$shift->max_volunteers} volunteers maximum)."
            ], 400);
        }

        // Add the user to the shift
        $shift->users()->attach($user->id, ['signed_up_at' => now()]);

        // Log the action
        AuditLog::create([
            'action'         => 'shift_volunteer_added',
            'auditable_type' => Event::class,
            'auditable_id'   => $shift->event->id,
            'comment'        => "User {$user->name} added to {$shift->name} (ID: {$shift->id}) by " . auth()->user()->name,
            'user_id'        => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$user->name} has been added to the shift successfully.",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'shift_count' => $shift->users()->count()
        ]);
    }

    public function setNoShow(Request $request, Event $event, Shift $shift, User $user)
    {
        $request->validate([
            'no_show' => 'required|boolean',
        ]);

        $signup = $shift->users()->where('user_id', $user->id)->first();

        if (! $signup) {
            return response()->json([
                'success' => false,
                'message' => 'Volunteer is not assigned to this shift.',
            ], 404);
        }

        $noShow = $request->boolean('no_show');

        if ($noShow && $signup->pivot->hours_logged_at) {
            return response()->json([
                'success' => false,
                'message' => 'Hours have already been credited for this volunteer. Remove hours before marking a no show.',
            ], 422);
        }

        $shift->users()->updateExistingPivot($user->id, [
            'no_show' => $noShow,
            'no_show_marked_at' => $noShow ? now() : null,
        ]);

        AuditLog::create([
            'action'         => $noShow ? 'shift_volunteer_no_show_marked' : 'shift_volunteer_no_show_cleared',
            'auditable_type' => Event::class,
            'auditable_id'   => $shift->event->id,
            'comment'        => "User {$user->name} " . ($noShow ? 'marked as no show' : 'unmarked as no show') . " for {$shift->name} (ID: {$shift->id}) by " . auth()->user()->name,
            'user_id'        => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'no_show' => $noShow,
            'message' => $noShow ? "{$user->name} marked as a no show." : "{$user->name} is no longer marked as a no show.",
        ]);
    }

    /**
     * Process advanced duplicate request to create multiple shift copies
     */
    public function advancedDuplicate(AdvancedDuplicateShiftRequest $request, Event $event, Shift $shift)
    {
        $validated = $request->validated();
        
        $recurrence = $validated['recurrence'];
        $interval = $validated['interval'];
        $intervalUnit = $validated['interval_unit'];
        $namingPattern = $validated['naming_pattern'];
        $customPrefix = $validated['custom_prefix'] ?? '';
        $customSuffix = $validated['custom_suffix'] ?? '';
        $copyVolunteers = $validated['copy_volunteers'] ?? false;
        $maintainCapacity = $validated['maintain_capacity'] ?? true;
        $copyDescription = $validated['copy_description'] ?? true;

        // Generate a unique series ID for tracking these duplicates together
        $seriesId = Str::uuid()->toString();
        
        $createdShifts = [];
        $volunteers = $copyVolunteers ? $shift->users->pluck('id')->toArray() : [];
        $shift->load('tags');
        $tagIds = $shift->tags->pluck('id')->toArray();

        for ($i = 1; $i <= $recurrence; $i++) {
            // Calculate new times based on interval
            $timeOffset = $interval * $i;
            
            $newStartTime = clone $shift->start_time;
            $newEndTime = clone $shift->end_time;
            
            switch ($intervalUnit) {
                case 'hours':
                    $newStartTime->addHours($timeOffset);
                    $newEndTime->addHours($timeOffset);
                    break;
                case 'days':
                    $newStartTime->addDays($timeOffset);
                    $newEndTime->addDays($timeOffset);
                    break;
                case 'weeks':
                    $newStartTime->addWeeks($timeOffset);
                    $newEndTime->addWeeks($timeOffset);
                    break;
            }

            // Generate new name based on pattern
            $newName = $this->generateShiftName($shift->name, $namingPattern, $i, $customPrefix, $customSuffix, $newStartTime);

            // Create the new shift
            $newShift = $shift->replicate();
            $newShift->name = $newName;
            $newShift->start_time = $newStartTime;
            $newShift->end_time = $newEndTime;
            $newShift->original_shift_id = $shift->id;
            $newShift->duplicate_series_id = $seriesId;
            $newShift->duplicate_sequence = $i;
            
            if (!$maintainCapacity) {
                $newShift->max_volunteers = 1;
            }
            
            if (!$copyDescription) {
                $newShift->description = null;
            }
            
            $newShift->save();

            // Copy volunteer assignments if requested
            if ($copyVolunteers && !empty($volunteers)) {
                $newShift->users()->attach($volunteers, ['signed_up_at' => now()]);
            }

            // Copy tags
            if (!empty($tagIds)) {
                $newShift->tags()->sync($tagIds);
            }

            $createdShifts[] = $newShift;
        }

        // Log the action
        AuditLog::create([
            'action'         => 'shift_advanced_duplicate',
            'auditable_type' => Event::class,
            'auditable_id'   => $event->id,
            'comment'        => "User " . auth()->user()->name . " created {$recurrence} duplicates of shift '{$shift->name}' (ID: {$shift->id}) with series ID {$seriesId}",
            'user_id'        => auth()->id(),
        ]);

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => "Successfully created <span class=\"text-brand-green\">{$recurrence} duplicate shifts</span> from '{$shift->name}'",
            ]);
    }

    /**
     * Generate shift name based on naming pattern
     */
    private function generateShiftName(string $originalName, string $pattern, int $sequence, string $prefix = '', string $suffix = '', $startTime = null): string
    {
        // Format start time for display (e.g., "2pm", "3pm")
        $formattedTime = $startTime ? $startTime->format('ga') : '';
        
        switch ($pattern) {
            case 'sequence':
                return "{$originalName} ({$sequence})";
            
            case 'start_time':
                return "{$originalName} ({$formattedTime})";
            
            case 'prefix':
                return "{$prefix} {$originalName}";
            
            case 'suffix':
                return "{$originalName} {$suffix}";
            
            case 'prefix_sequence':
                return "{$prefix} {$originalName} ({$sequence})";
            
            case 'suffix_sequence':
                return "{$originalName} ({$sequence}) {$suffix}";
            
            case 'custom':
                return str_replace(['{n}', '{name}', '{t}'], [$sequence, $originalName, $formattedTime], $prefix);
            
            case 'none':
            default:
                return $originalName;
        }
    }

    /**
     * Show the form for creating a shift series.
     */
    public function createSeries(Event $event)
    {
        $tags = Tag::forShifts()->orderBy('name')->get();
        return view('admin.shifts.create-series', compact('event', 'tags'));
    }

    /**
     * Store a newly created shift series in storage.
     */
    public function storeSeries(Request $request, Event $event)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'naming_pattern'   => 'required|string|max:255',
            'description'      => 'nullable|string',
            'start_time'       => 'required|date',
            'duration_hours'   => 'required|integer|min:0',
            'duration_minutes' => 'required|integer|min:0|max:59',
            'occurrences'      => 'required|integer|min:1|max:100',
            'gap_hours'        => 'required|integer|min:0',
            'gap_minutes'      => 'required|integer|min:0|max:59',
            'max_volunteers'   => 'required|integer|min:1',
            'double_hours'     => 'nullable|boolean',
            'shift_tags'       => 'nullable|array',
            'shift_tags.*'     => 'integer|exists:tags,id',
        ]);

        $durationMinutes = ((int) $request->duration_hours * 60) + (int) $request->duration_minutes;
        $gapMinutes      = ((int) $request->gap_hours * 60) + (int) $request->gap_minutes;

        if ($durationMinutes < 1) {
            return back()->withErrors(['duration_hours' => 'Total duration must be at least 1 minute.'])->withInput();
        }

        $seriesId    = Str::uuid()->toString();
        $startTime   = Carbon::parse($request->start_time);
        $tagIds      = $request->input('shift_tags', []);
        $doubleHours = $request->boolean('double_hours');
        $createdShifts = [];

        for ($i = 0; $i < (int) $request->occurrences; $i++) {
            $shiftStart = $startTime->copy()->addMinutes(($durationMinutes + $gapMinutes) * $i);
            $shiftEnd   = $shiftStart->copy()->addMinutes($durationMinutes);

            $shiftName = $this->applySeriesNamingPattern(
                $request->naming_pattern,
                $request->name,
                $shiftStart,
                $i + 1
            );

            $shift = $event->shifts()->create([
                'name'                 => $shiftName,
                'description'          => $request->description,
                'start_time'           => $shiftStart,
                'end_time'             => $shiftEnd,
                'max_volunteers'       => $request->max_volunteers,
                'double_hours'         => $doubleHours,
                'duplicate_series_id'  => $seriesId,
                'duplicate_sequence'   => $i + 1,
            ]);

            if (!empty($tagIds)) {
                $shift->tags()->sync($tagIds);
            }

            $createdShifts[] = $shift;
        }

        AuditLog::create([
            'action'         => 'shift_series_created',
            'auditable_type' => Event::class,
            'auditable_id'   => $event->id,
            'comment'        => 'User ' . auth()->user()->name . ' created a series of ' . count($createdShifts) . " shifts named \"{$request->name}\" (series ID: {$seriesId})",
            'user_id'        => auth()->id(),
        ]);

        return redirect()->route('admin.events.shifts.index', $event)
            ->with('success', [
                'message' => 'Successfully created <span class="text-brand-green">' . count($createdShifts) . ' shifts</span> in the series.',
            ]);
    }

    /**
     * Replace naming pattern tokens with real values for a series shift.
     */
    private function applySeriesNamingPattern(string $pattern, string $name, Carbon $startTime, int $sequence): string
    {
        return str_replace(
            ['{name}', '{start_time}', '{n}'],
            [$name, $startTime->format('g:i A'), $sequence],
            $pattern
        );
    }

    public function importCsv(Request $request, Event $event)
    {
        \Log::debug('Importing CSV', ['event_id' => $event->id]);
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();

        // Open with League\Csv (composer require league/csv if not present)
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0); // use first row as header

        $records = (new Statement())->process($csv);

        $created = 0;
        foreach ($records as $record) {
            $validator = Validator::make($record, [
                'name'       => 'required|string|max:255',
                'start_time'  => 'required|date',
                'end_time'    => 'required|date|after:start_time',
                'max_volunteers'    => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'double_hours' => 'nullable|boolean',

            ]);

            if ($validator->fails()) {
                \Log::debug('failed validation', $validator->errors()->toArray());
                continue; // you could also collect errors to show later
            }

            $event->shifts()->create([
                'name'       => $record['name'],
                'start_time'  => $record['start_time'],
                'end_time'    => $record['end_time'],
                'max_volunteers'    => $record['max_volunteers'] ?? 1,
                'description' => $record['description'] ?? null,
                'double_hours' => isset($record['double_hours']) ? (bool)$record['double_hours'] : false,
            ]);

            $created++;
        }

        AuditLog::create([
            'action'         => 'csv_import',
            'auditable_type' => Event::class,
            'auditable_id'   => $event->id,
            'comment'        => "User " . auth()->user()->name . " imported $created shifts from CSV",
            'user_id'        => auth()->id(),
        ]);

        return redirect()->route('admin.events.shifts.index', $event)->with('success', [
                'message' => "$created shifts imported successfully.",
            ]);
    }
}
