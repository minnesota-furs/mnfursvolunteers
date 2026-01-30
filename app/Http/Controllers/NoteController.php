<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteComment;
use App\Models\User;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of notes for a user.
     */
    public function index(User $user)
    {
        $currentUser = auth()->user();
        $canManageUsers = $currentUser->isAdmin() || $currentUser->hasPermission('manage-users');
        
        $notes = $user->userNotes()
            ->with(['creator', 'comments.user'])
            ->when(!$canManageUsers, function ($query) use ($user) {
                // If user is viewing their own profile, only show public notes
                if (auth()->id() === $user->id) {
                    $query->where('private', false);
                } else {
                    // If user doesn't have manage-users permission and viewing someone else, show no notes
                    $query->whereRaw('1 = 0');
                }
            })
            ->latest()
            ->get();

        return view('users.notes.index', compact('user', 'notes'));
    }

    /**
     * Show the form for creating a new note.
     */
    public function create(User $user)
    {
        // Only users with manage-users permission can create notes
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('manage-users')) {
            abort(403, 'Unauthorized to create notes.');
        }
        
        return view('users.notes.create', compact('user'));
    }

    /**
     * Store a newly created note.
     */
    public function store(Request $request, User $user)
    {
        // Only users with manage-users permission can create notes
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('manage-users')) {
            abort(403, 'Unauthorized to create notes.');
        }
        
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:Standard,Writeup',
            'content' => 'required|string',
            'private' => 'boolean',
        ]);

        $note = $user->userNotes()->create([
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'content' => $validated['content'],
            'private' => $request->boolean('private'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('users.notes.index', $user)
            ->with('success', 'Note created successfully.');
    }

    /**
     * Update the specified note.
     */
    public function update(Request $request, User $user, Note $note)
    {
        // Only the creator or admins can update
        if ($note->created_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:Standard,Writeup',
            'content' => 'required|string',
            'private' => 'boolean',
        ]);

        $note->update([
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'content' => $validated['content'],
            'private' => $request->boolean('private'),
        ]);

        return redirect()->route('users.notes.index', $user)
            ->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified note.
     */
    public function destroy(User $user, Note $note)
    {
        // Only the creator or admins can delete
        if ($note->created_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $note->delete();

        return redirect()->route('users.notes.index', $user)
            ->with('success', 'Note deleted successfully.');
    }

    /**
     * Store a comment on a note.
     */
    public function storeComment(Request $request, User $user, Note $note)
    {
        // Check if user can view this note
        if ($note->private && $note->created_by !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $note->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('users.notes.index', $user)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Delete a comment from a note.
     */
    public function destroyComment(User $user, Note $note, NoteComment $comment)
    {
        // Only the comment creator or admins can delete
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $comment->delete();

        return redirect()->route('users.notes.index', $user)
            ->with('success', 'Comment deleted successfully.');
    }
}
