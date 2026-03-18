<?php

namespace App\Http\Controllers;

use App\Models\VolunteerPerk;
use App\Models\VolunteerPerkRedemption;
use App\Models\VolunteerPerkSet;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class VolunteerPerkController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $userRedemptions = VolunteerPerkRedemption::where('user_id', $user->id)
            ->get()
            ->keyBy('volunteer_perk_id');

        $sets = VolunteerPerkSet::with([
            'perks' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('min_hours'),
            'perks.events',
            'fiscalLedger',
        ])
        ->current()
        ->orderBy('sort_order')
        ->get()
        ->map(fn ($set) => [
            'set'   => $set,
            'perks' => $set->perks->map(function ($perk) use ($user, $userRedemptions) {
                $breakdown  = $perk->getUserProgressBreakdown($user);
                $progress   = $breakdown['completed'] + $breakdown['upcoming'];
                $percentage = $perk->min_hours > 0
                    ? min(100.0, $progress / $perk->min_hours * 100)
                    : 100.0;
                $earned     = $perk->min_hours <= 0 || $breakdown['completed'] >= $perk->min_hours;
                $onTrack    = !$earned && $perk->min_hours > 0 && $progress >= $perk->min_hours;
                return [
                    'perk'        => $perk,
                    'breakdown'   => $breakdown,
                    'progress'    => $progress,
                    'percentage'  => $percentage,
                    'earned'      => $earned,
                    'on_track'    => $onTrack,
                    'redemption'  => $userRedemptions->get($perk->id),
                ];
            }),
        ])
        ->filter(fn ($item) => $item['perks']->isNotEmpty())
        ->values();

        return view('events.perks', compact('sets'));
    }

    public function history(): View
    {
        $user = auth()->user();

        $userRedemptions = VolunteerPerkRedemption::where('user_id', $user->id)
            ->get()
            ->keyBy('volunteer_perk_id');

        $sets = VolunteerPerkSet::with([
            'perks' => fn ($q) => $q->orderBy('sort_order')->orderBy('min_hours'),
            'perks.events',
            'fiscalLedger',
        ])
        ->expired()
        ->orderBy('visible_until', 'desc')
        ->get()
        ->map(fn ($set) => [
            'set'   => $set,
            'perks' => $set->perks->map(function ($perk) use ($user, $userRedemptions) {
                $breakdown  = $perk->getUserProgressBreakdown($user);
                $progress   = $breakdown['completed'] + $breakdown['upcoming'];
                $percentage = $perk->min_hours > 0
                    ? min(100.0, $progress / $perk->min_hours * 100)
                    : 100.0;
                $earned     = $perk->min_hours <= 0 || $breakdown['completed'] >= $perk->min_hours;
                $onTrack    = !$earned && $perk->min_hours > 0 && $progress >= $perk->min_hours;
                return [
                    'perk'       => $perk,
                    'breakdown'  => $breakdown,
                    'progress'   => $progress,
                    'percentage' => $percentage,
                    'earned'     => $earned,
                    'on_track'   => $onTrack,
                    'redemption' => $userRedemptions->get($perk->id),
                ];
            }),
        ])
        ->filter(fn ($item) => $item['perks']->isNotEmpty())
        ->values();

        return view('events.perks-history', compact('sets'));
    }

    public function redeem(VolunteerPerk $perk): JsonResponse
    {
        $user = auth()->user();

        if (!$perk->has_physical_reward) {
            return response()->json(['error' => 'This perk cannot be redeemed.'], 422);
        }

        if (!$perk->hasEarned($user)) {
            return response()->json(['error' => 'You have not earned this perk yet.'], 403);
        }

        $existing = VolunteerPerkRedemption::where('user_id', $user->id)
            ->where('volunteer_perk_id', $perk->id)
            ->first();

        if ($existing) {
            return response()->json([
                'error'       => 'This perk has already been redeemed.',
                'redeemed_at' => $existing->redeemed_at->toIso8601String(),
            ], 409);
        }

        $redemption = VolunteerPerkRedemption::create([
            'user_id'           => $user->id,
            'volunteer_perk_id' => $perk->id,
            'redeemed_at'       => now(),
        ]);

        return response()->json([
            'redeemed_at' => $redemption->redeemed_at->toIso8601String(),
            'message'     => 'Perk redeemed successfully!',
        ]);
    }
}
