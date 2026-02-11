<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

use Dotlogics\Grapesjs\App\Traits\EditorTrait;

class PageController extends Controller
{
    use EditorTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function editor(Request $request, Page $page)
    {
        return $this->show_gjs_editor($request, $page);
    }

    /**Handle image uploads for GrapesJS
     *Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:pages,slug',
        ]);

        $page = Page::create([
            'slug' => $validated['slug'],
            'gjs_data' => json_encode([
                'assets' => [],
                'styles' => [],
                'pages' => [[
                    'id' => $validated['slug'],
                    'frames' => [[
                        'component' => [
                            'type' => 'wrapper',
                            'stylable' => true,
                            'components' => []
                        ],
                        'styles' => ''
                    ]]
                ]]
            ])
        ]);

        return redirect()->route('settings.index')->with(['success' => 'Page created successfully!', 'activeTab' => 'pages']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        return view('pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
        ]);

        $page->update([
            'slug' => $validated['slug'],
        ]);

        return redirect()->route('settings.index')->with(['success' => 'Page updated successfully!', 'activeTab' => 'pages']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        // Don't allow deletion of home page
        if ($page->slug === 'home') {
            return redirect()->route('settings.index')->with(['error' => 'Cannot delete the home page.', 'activeTab' => 'pages']);
        }

        $page->delete();

        return redirect()->route('settings.index')->with(['success' => 'Page deleted successfully!', 'activeTab' => 'pages']);
    }
}
