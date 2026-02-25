<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InviteCode;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InviteCodeController extends Controller
{
    /**
     * Display a listing of all invite codes.
     */
    public function index(): View
    {
        $codes = InviteCode::with(['tags', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.invite-codes.index', compact('codes'));
    }

    /**
     * Show the form to create a new invite code.
     */
    public function create(): View
    {
        $tags = Tag::orderBy('name')->get();

        return view('admin.invite-codes.create', compact('tags'));
    }

    /**
     * Store a new invite code.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label'    => 'nullable|string|max:255',
            'code'     => 'nullable|string|max:32|unique:invite_codes,code',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'is_active'  => 'boolean',
            'tags'     => 'nullable|array',
            'tags.*'   => 'exists:tags,id',
        ]);

        $inviteCode = InviteCode::create([
            'label'      => $validated['label'] ?? null,
            'code'       => $validated['code'] ?? null, // null triggers auto-generation in boot
            'max_uses'   => $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active'  => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        if (! empty($validated['tags'])) {
            $inviteCode->tags()->sync($validated['tags']);
        }

        return redirect()->route('admin.invite-codes.index')
            ->with('success', 'Invite code created successfully.');
    }

    /**
     * Show the details of a single invite code.
     */
    public function show(InviteCode $inviteCode): View
    {
        $inviteCode->load(['tags', 'creator']);

        return view('admin.invite-codes.show', compact('inviteCode'));
    }

    /**
     * Show the edit form for an invite code.
     */
    public function edit(InviteCode $inviteCode): View
    {
        $inviteCode->load('tags');
        $tags = Tag::orderBy('name')->get();

        return view('admin.invite-codes.edit', compact('inviteCode', 'tags'));
    }

    /**
     * Update an invite code.
     */
    public function update(Request $request, InviteCode $inviteCode): RedirectResponse
    {
        $validated = $request->validate([
            'label'      => 'nullable|string|max:255',
            'code'       => 'required|string|max:32|unique:invite_codes,code,' . $inviteCode->id,
            'max_uses'   => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active'  => 'boolean',
            'tags'       => 'nullable|array',
            'tags.*'     => 'exists:tags,id',
        ]);

        $inviteCode->update([
            'label'      => $validated['label'] ?? null,
            'code'       => strtoupper($validated['code']),
            'max_uses'   => $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        $inviteCode->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('admin.invite-codes.index')
            ->with('success', 'Invite code updated successfully.');
    }

    /**
     * Delete an invite code.
     */
    public function destroy(InviteCode $inviteCode): RedirectResponse
    {
        $inviteCode->tags()->detach();
        $inviteCode->delete();

        return redirect()->route('admin.invite-codes.index')
            ->with('success', 'Invite code deleted.');
    }

    /**
     * Regenerate the code string for an existing invite code.
     */
    public function regenerate(InviteCode $inviteCode): RedirectResponse
    {
        $inviteCode->update(['code' => InviteCode::generateUniqueCode()]);

        return back()->with('success', 'Invite code regenerated.');
    }
}
