<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::query()
            ->with(['creator', 'departments', 'sectors'])
            ->latest()
            ->paginate(20);

        return view('announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        return view('announcements.create', ['sectors' => $this->sectors()]);
    }

    public function show(Request $request, Announcement $announcement): View
    {
        $announcement = Announcement::query()
            ->active()
            ->visibleTo($request->user())
            ->findOrFail($announcement->id);

        return view('announcements.show', compact('announcement'));
    }

    public function store(SaveAnnouncementRequest $request): RedirectResponse
    {
        $announcement = Announcement::create([
            ...$request->safe()->only(['title', 'body', 'expires_at']),
            'volunteers_only' => $request->boolean('volunteers_only'),
            'created_by' => $request->user()->id,
        ]);

        $this->syncAudience($announcement, $request);

        return redirect()->route('announcements.index')->with('success', [
            'message' => 'Announcement created successfully.',
        ]);
    }

    public function edit(Announcement $announcement): View
    {
        $announcement->load(['departments', 'sectors']);

        return view('announcements.edit', [
            'announcement' => $announcement,
            'sectors' => $this->sectors(),
        ]);
    }

    public function update(SaveAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            ...$request->safe()->only(['title', 'body', 'expires_at']),
            'volunteers_only' => $request->boolean('volunteers_only'),
        ]);
        $this->syncAudience($announcement, $request);

        return redirect()->route('announcements.index')->with('success', [
            'message' => 'Announcement updated successfully.',
        ]);
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', [
            'message' => 'Announcement deleted successfully.',
        ]);
    }

    private function syncAudience(Announcement $announcement, SaveAnnouncementRequest $request): void
    {
        $announcement->departments()->sync($request->validated('departments', []));
        $announcement->sectors()->sync($request->validated('sectors', []));
    }

    private function sectors(): Collection
    {
        return Sector::query()
            ->with(['departments' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();
    }
}
