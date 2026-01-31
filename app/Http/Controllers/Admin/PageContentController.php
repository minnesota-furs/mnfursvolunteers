<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;

class PageContentController extends Controller
{
    public function edit($pageKey = 'welcome')
    {
        $pageContent = PageContent::firstOrCreate(
            ['page_key' => $pageKey],
            ['html_content' => '', 'css_content' => '', 'gjs_data' => []]
        );

        return view('admin.page-content.edit', compact('pageContent'));
    }

    public function update(Request $request, $pageKey = 'welcome')
    {
        $request->validate([
            'html_content' => 'nullable|string',
            'css_content' => 'nullable|string',
            'gjs_data' => 'nullable|string',
        ]);

        $pageContent = PageContent::firstOrCreate(['page_key' => $pageKey]);
        
        $pageContent->update([
            'html_content' => $request->input('html_content'),
            'css_content' => $request->input('css_content'),
            'gjs_data' => json_decode($request->input('gjs_data'), true),
        ]);

        return response()->json(['success' => true, 'message' => 'Page content saved successfully']);
    }

    public function load($pageKey = 'welcome')
    {
        $pageContent = PageContent::where('page_key', $pageKey)->first();

        if (!$pageContent) {
            return response()->json([
                'html' => '',
                'css' => '',
                'gjs_data' => []
            ]);
        }

        return response()->json([
            'html' => $pageContent->html_content,
            'css' => $pageContent->css_content,
            'gjs_data' => $pageContent->gjs_data
        ]);
    }
}
