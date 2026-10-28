<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveElectionMilestoneRequest;
use App\Models\Election;
use App\Models\ElectionMilestone;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ElectionMilestoneController extends Controller
{
    public function index(Election $election): View
    {
        $milestones = $election->milestones()->get();

        return view('admin.elections.milestones', compact('election', 'milestones'));
    }

    public function store(SaveElectionMilestoneRequest $request, Election $election): RedirectResponse
    {
        $election->milestones()->create($request->validated());

        return redirect()->route('admin.elections.milestones.index', $election)
            ->with('success', ['message' => 'Milestone added.']);
    }

    public function update(SaveElectionMilestoneRequest $request, Election $election, ElectionMilestone $milestone): RedirectResponse
    {
        $milestone->update($request->validated());

        return redirect()->route('admin.elections.milestones.index', $election)
            ->with('success', ['message' => 'Milestone updated.']);
    }

    public function destroy(Election $election, ElectionMilestone $milestone): RedirectResponse
    {
        $milestone->delete();

        return redirect()->route('admin.elections.milestones.index', $election)
            ->with('success', ['message' => 'Milestone removed.']);
    }
}
