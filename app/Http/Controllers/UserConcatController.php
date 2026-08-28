<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ConcatService;
use App\Services\ConcatSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserConcatController extends Controller
{
    public function __construct(private ConcatService $concat, private ConcatSyncService $syncService) {}

    /**
     * Search ConCat by an email address that may differ from the user's own,
     * and link the account immediately if exactly one match is found.
     */
    public function search(Request $request, User $user): RedirectResponse
    {
        if (! $this->concat->isConfigured()) {
            return back()->with('error', 'ConCat is not connected. Configure it under Application Settings first.');
        }

        $validated = $request->validate([
            'concat_search_email' => 'required|email',
        ]);

        $match = $this->concat->findUserByEmail($validated['concat_search_email']);

        if (! $match) {
            return back()->with('error', "No ConCat account found with the email {$validated['concat_search_email']}.");
        }

        $this->syncService->associateUser($user, $match['id']);

        return back()->with('success', [
            'message' => "Linked to ConCat account: {$match['firstName']} {$match['lastName']} ({$match['email']}).",
        ]);
    }

    /**
     * Link directly to a known ConCat user ID, verifying it exists first.
     */
    public function link(Request $request, User $user): RedirectResponse
    {
        if (! $this->concat->isConfigured()) {
            return back()->with('error', 'ConCat is not connected. Configure it under Application Settings first.');
        }

        $validated = $request->validate([
            'concat_user_id' => 'required|string',
        ]);

        $match = $this->concat->getUserById($validated['concat_user_id']);

        if (! $match) {
            return back()->with('error', "No ConCat account exists with ID {$validated['concat_user_id']}.");
        }

        $this->syncService->associateUser($user, $match['id']);

        return back()->with('success', [
            'message' => "Linked to ConCat account: {$match['firstName']} {$match['lastName']} ({$match['email']}).",
        ]);
    }

    /**
     * Remove the ConCat association and revoke any roles it granted.
     */
    public function unlink(User $user): RedirectResponse
    {
        $this->syncService->disassociateUser($user);

        return back()->with('success', [
            'message' => 'ConCat account unlinked and any granted roles were revoked.',
        ]);
    }
}
