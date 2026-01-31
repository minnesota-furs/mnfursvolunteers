<?php

namespace App\Http\Controllers;

use App\Models\PageContent;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $pageContent = PageContent::where('page_key', 'welcome')->first();
        
        return view('welcome', compact('pageContent'));
    }
}
