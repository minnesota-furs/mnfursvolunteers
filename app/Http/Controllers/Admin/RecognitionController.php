<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recognition;
use App\Models\User;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecognitionController extends Controller
{
    /**
     * Display a listing of recognitions.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('manage-recognition')) {
            abort(403, 'Unauthorized');
        }

        $query = Recognition::with(['user', 'grantedByUser', 'sector']);

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by sector
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by privacy
        if ($request->filled('is_private')) {
            $query->where('is_private', $request->is_private);
        }

        $recognitions = $query->orderBy('date', 'desc')->paginate(15);
        
        $users = User::orderBy('name')->get();
        $sectors = Sector::orderBy('name')->get();
        $types = ['General', 'Physical Award', 'Shoutout', 'Other'];

        return view('admin.recognition.index', compact('recognitions', 'users', 'sectors', 'types'));
    }

    /**
     * Show the form for creating a new recognition.
     */
    public function create()
    {
        if (!auth()->user()->hasPermission('manage-recognition')) {
            abort(403, 'Unauthorized');
        }

        $users = User::orderBy('name')->get();
        $sectors = Sector::orderBy('name')->get();
        $types = ['General', 'Physical Award', 'Shoutout', 'Other'];

        return view('admin.recognition.create', compact('users', 'sectors', 'types'));
    }

    /**
     * Store a newly created recognition.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('manage-recognition')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:General,Physical Award,Shoutout,Other',
            'date' => 'required|date',
            'sector_id' => 'nullable|exists:sectors,id',
            'description' => 'nullable|string|max:1000',
            'is_private' => 'boolean',
        ]);

        $validated['granted_by_user_id'] = auth()->id();
        $validated['is_private'] = $request->has('is_private') ? true : false;

        Recognition::create($validated);

        return redirect()->route('admin.recognitions.index')
            ->with('success', 'Recognition created successfully.');
    }

    /**
     * Display the specified recognition.
     */
    public function show(Recognition $recognition)
    {
        if (!auth()->user()->hasPermission('manage-recognition')) {
            abort(403, 'Unauthorized');
        }

        return view('admin.recognition.show', compact('recognition'));
    }

    /**
     * Show the form for editing the specified recognition.
     */
    public function edit(Recognition $recognition)
    {
        if (!auth()->user()->hasPermission('manage-recognition')) {
            abort(403, 'Unauthorized');
        }

        $users = User::orderBy('name')->get();
        $sectors = Sector::orderBy('name')->get();
        $types = ['General', 'Physical Award', 'Shoutout', 'Other'];

        return view('admin.recognition.edit', compact('recognition', 'users', 'sectors', 'types'));
    }

    /**
     * Update the specified recognition.
     */
    public function update(Request $request, Recognition $recognition)
    {
        if (!auth()->user()->hasPermission('manage-recognition')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:General,Physical Award,Shoutout,Other',
            'date' => 'required|date',
            'sector_id' => 'nullable|exists:sectors,id',
            'description' => 'nullable|string|max:1000',
            'is_private' => 'boolean',
        ]);

        $validated['is_private'] = $request->has('is_private') ? true : false;

        $recognition->update($validated);

        return redirect()->route('admin.recognitions.index')
            ->with('success', 'Recognition updated successfully.');
    }

    /**
     * Remove the specified recognition.
     */
    public function destroy(Recognition $recognition)
    {
        if (!auth()->user()->hasPermission('manage-recognition')) {
            abort(403, 'Unauthorized');
        }

        $recognition->delete();

        return redirect()->route('admin.recognitions.index')
            ->with('success', 'Recognition deleted successfully.');
    }
}
