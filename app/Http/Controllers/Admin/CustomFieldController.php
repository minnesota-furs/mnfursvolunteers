<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customFields = CustomField::ordered()->get();
        
        return view('admin.custom-fields.index', compact('customFields'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.custom-fields.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_key' => 'required|string|max:255|unique:custom_fields,field_key|regex:/^[a-z0-9_]+$/',
            'field_type' => 'required|in:text,textarea,select,checkbox,date,number',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        // Validate that options are provided for select/checkbox fields
        if (in_array($request->field_type, ['select', 'checkbox'])) {
            $options = array_values(array_filter($request->options ?? []));
            if (empty($options)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['options' => 'At least one option is required for select and checkbox fields.']);
            }
        }

        // Handle boolean fields (checkboxes that aren't checked don't send a value)
        $validated['is_required'] = $request->has('is_required') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['user_editable'] = $request->has('user_editable') ? 1 : 0;

        // Convert options array to proper format for select/checkbox fields
        if (in_array($validated['field_type'], ['select', 'checkbox']) && !empty($validated['options'])) {
            $validated['options'] = array_values(array_filter($validated['options']));
        } else {
            $validated['options'] = null;
        }

        // Set default sort order if not provided
        if (!isset($validated['sort_order']) || $validated['sort_order'] === '') {
            $maxOrder = CustomField::max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        CustomField::create($validated);

        return redirect()->route('admin.custom-fields.index')
            ->with('success', 'Custom field created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomField $customField)
    {
        return view('admin.custom-fields.show', compact('customField'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomField $customField)
    {

        return view('admin.custom-fields.edit', compact('customField'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomField $customField)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_key' => 'required|string|max:255|regex:/^[a-z0-9_]+$/|unique:custom_fields,field_key,' . $customField->id,
            'field_type' => 'required|in:text,textarea,select,checkbox,date,number',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        // Handle boolean fields (checkboxes that aren't checked don't send a value)
        $validated['is_required'] = $request->has('is_required') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['user_editable'] = $request->has('user_editable') ? 1 : 0;

        // Convert options array to proper format for select/checkbox fields
        if (in_array($validated['field_type'], ['select', 'checkbox']) && !empty($validated['options'])) {
            $validated['options'] = array_values(array_filter($validated['options']));
        } else {
            $validated['options'] = null;
        }

        $customField->update($validated);

        return redirect()->route('admin.custom-fields.index')
            ->with('success', 'Custom field updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomField $customField)
    {
        $customField->delete();

        return redirect()->route('admin.custom-fields.index')
            ->with('success', 'Custom field deleted successfully.');
    }

    /**
     * Reorder custom fields
     */
    public function reorder(Request $request)
    {
        $order = $request->input('order', []);
        
        foreach ($order as $index => $id) {
            CustomField::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
