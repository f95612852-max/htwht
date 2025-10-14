<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileMigrationController extends Controller
{
    public function index(Request $request)
    {
        return view('profile.migration.index');
    }
    
    public function store(Request $request)
    {
        // Handle profile migration
        return redirect()->back()->with('success', 'Profile migration initiated');
    }
}