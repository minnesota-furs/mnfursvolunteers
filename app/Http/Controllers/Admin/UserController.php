<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\VolunteerHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Bulk log hours for multiple users
     */
    public function bulkLogHours(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|string',
            'hours' => 'required|numeric|min:0|max:24',
            'date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string|max:500',
        ]);

        $userIds = array_filter(explode(',', $validated['user_ids']));
        
        if (empty($userIds)) {
            return back()->with('error', 'No users selected.');
        }

        // Find the appropriate fiscal ledger for the date
        $fiscalLedger = FiscalLedger::where('start_date', '<=', $validated['date'])
            ->where('end_date', '>=', $validated['date'])
            ->first();

        if (!$fiscalLedger) {
            return back()->with('error', 'No fiscal ledger found for the selected date.');
        }

        $department = Department::findOrFail($validated['department_id']);
        $successCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if (!$user) {
                    $errors[] = "User ID {$userId} not found";
                    continue;
                }

                VolunteerHours::create([
                    'user_id' => $user->id,
                    'hours' => $validated['hours'],
                    'volunteer_date' => $validated['date'],
                    'primary_dept_id' => $validated['department_id'],
                    'fiscal_ledger_id' => $fiscalLedger->id,
                    'description' => $validated['description'] ?? "Bulk hours logged for {$department->name}",
                    'notes' => $validated['notes'] ?? null,
                ]);

                $successCount++;
            }

            DB::commit();

            $message = "Successfully logged {$validated['hours']} hours for {$successCount} user(s).";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->route('users.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk log hours failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to log hours: ' . $e->getMessage());
        }
    }

    /**
     * Bulk add tags to multiple users
     */
    public function bulkAddTags(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|string',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $userIds = array_filter(explode(',', $validated['user_ids']));
        
        if (empty($userIds)) {
            return back()->with('error', 'No users selected.');
        }

        if (empty($validated['tag_ids'])) {
            return back()->with('error', 'No tags selected.');
        }

        $successCount = 0;
        
        DB::beginTransaction();
        try {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    // Sync will add tags without removing existing ones
                    $user->tags()->syncWithoutDetaching($validated['tag_ids']);
                    $successCount++;
                }
            }

            DB::commit();

            return redirect()->route('users.index')->with('success', 
                "Successfully added " . count($validated['tag_ids']) . " tag(s) to {$successCount} user(s)."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk add tags failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to add tags: ' . $e->getMessage());
        }
    }

    /**
     * Bulk remove tags from multiple users
     */
    public function bulkRemoveTags(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|string',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $userIds = array_filter(explode(',', $validated['user_ids']));
        
        if (empty($userIds)) {
            return back()->with('error', 'No users selected.');
        }

        if (empty($validated['tag_ids'])) {
            return back()->with('error', 'No tags selected.');
        }

        $successCount = 0;
        
        DB::beginTransaction();
        try {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->tags()->detach($validated['tag_ids']);
                    $successCount++;
                }
            }

            DB::commit();

            return redirect()->route('users.index')->with('success', 
                "Successfully removed " . count($validated['tag_ids']) . " tag(s) from {$successCount} user(s)."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk remove tags failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove tags: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign department to multiple users
     */
    public function bulkAssignDepartment(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $userIds = array_filter(explode(',', $validated['user_ids']));
        
        if (empty($userIds)) {
            return back()->with('error', 'No users selected.');
        }

        $successCount = 0;
        
        DB::beginTransaction();
        try {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    // Sync will add department without removing existing ones
                    $user->departments()->syncWithoutDetaching([$validated['department_id']]);
                    $successCount++;
                }
            }

            DB::commit();

            $department = Department::find($validated['department_id']);
            return redirect()->route('users.index')->with('success', 
                "Successfully assigned {$department->name} department to {$successCount} user(s)."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk assign department failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to assign department: ' . $e->getMessage());
        }
    }
}
