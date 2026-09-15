<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomField;

class CustomFieldController extends Controller
{
    /**
     * Public list of active custom field definitions, e.g. for a
     * third-party integration's admin screen to pick which field to
     * display back to a volunteer (name/type/options only — no per-user
     * values here).
     */
    public function index()
    {
        $fields = CustomField::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'field_key', 'field_type', 'options']);

        return response()->json([
            'status' => 'success',
            'data' => $fields,
        ]);
    }
}
