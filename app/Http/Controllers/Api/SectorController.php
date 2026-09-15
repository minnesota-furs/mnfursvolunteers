<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sector;

class SectorController extends Controller
{
    /**
     * Public list of sectors, e.g. for a third-party "Sign in with
     * OpenVolunteer" integration building an eligibility allow-list.
     */
    public function index()
    {
        $sectors = Sector::query()->orderBy('name')->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'data' => $sectors,
        ]);
    }
}
