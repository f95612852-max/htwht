<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthorizeInteractionController extends Controller
{
    public function get(Request $request)
    {
        // Handle authorize interaction
        return view('authorize-interaction');
    }
}