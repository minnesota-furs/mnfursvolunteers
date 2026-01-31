<?php

namespace App\Http\Controllers;

use App\Services\FeatureService;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function __construct(protected FeatureService $features)
    {
    }

    /**
     * Show the feature flags demo page.
     */
    public function demo()
    {
        return view('features.demo');
    }

    /**
     * Get all features as JSON.
     */
    public function index()
    {
        return response()->json($this->features->all());
    }

    /**
     * Toggle a feature flag.
     */
    public function toggle(Request $request, string $feature)
    {
        $newState = $this->features->toggle($feature);

        return response()->json([
            'success' => true,
            'feature' => $feature,
            'enabled' => $newState,
        ]);
    }
}
