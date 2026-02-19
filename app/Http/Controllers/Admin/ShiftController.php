<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvancedDuplicateShiftRequest;
use App\Models\Event;
use App\Models\Shift;
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
        $shifts = $event->shifts()->orderBy('start_time', 'asc')->get();
        return view('admin.shifts.index', compact('event', 'shifts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        return view('admin.shifts.create', compact('event'));
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
            'user_id'        => 'nullable|array', // Accept array of user IDs
            'user_id.*'      => 'integer|exists:users,id' // Validate each user ID
        ]);

        $shiftData = $request->only(['name', 'description', 'start_time', 'end_time', 'max_volunteers']);
        $shiftData['double_hours'] = $request->has('double_hours');

        $shift = $event->shifts()->create($shiftData);

        if ($request->filled('user_id')) {
            $userIds = $request->input('user_id');
            // Ensure we have an array of user IDs
            $userIds = is_array($userIds) ? $userIds : [$userIds];
            $shift->users()->syncWithoutDetaching($userIds);
        }

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
        return view('admin.shifts.create', compact('event', 'shift'));
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
            'user_id'        => 'nullable|array', // Accept array of user IDs
            'user_id.*'      => 'integer|exists:users,id' // Validate each user ID
        ]);


        $updateData = $request->only(['name', 'description', 'start_time', 'double_hours', 'end_time', 'max_volunteers']);

        $updateData['double_hours'] = $request->has('double_hours');
        
        $shift->update($updateData);

        if ($request->filled('user_id')) {
            $userIds = $request->input('user_id');
            // Ensure we have an array of user IDs
            $userIds = is_array($userIds) ? $userIds : [$userIds];
            $shift->users()->syncWithoutDetaching($userIds);
        }

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
