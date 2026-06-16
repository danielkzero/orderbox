<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function manual(): View
    {
        return view('admin.docs.manual');
    }

    public function apiGuide(): View
    {
        return view('admin.docs.api-guide');
    }
}
