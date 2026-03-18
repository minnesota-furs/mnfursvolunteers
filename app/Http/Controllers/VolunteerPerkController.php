<?php

namespace App\Http\Controllers;

use App\Models\VolunteerPerk;
use Illuminate\View\View;

class VolunteerPerkController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $perks = VolunteerPerk::with(['fiscalLedger', 'events'])
            ->active()
            ->get()
            ->map(function (VolunteerPerk $perk) use ($user) {
                $progress    = $perk->getUserProgress($user);
                $percentage  = $perk->getUserProgressPercentage($user);
                $earned      = $perk->hasEarned($user);

                return [
                    'perk'       => $perk,
                    'progress'   => $progress,
                    'percentage' => $percentage,
                    'earned'     => $earned,
                ];
            });

        return view('events.perks', compact('perks'));
    }
}
