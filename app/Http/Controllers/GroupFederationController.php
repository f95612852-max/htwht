<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupFederationController extends Controller
{
    /**
     * Since we're in centralized mode, all federation features are disabled
     */
    
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * All federation methods return empty responses or redirect to home
     */
    public function __call($method, $parameters)
    {
        // In centralized mode, federation features are disabled
        if (request()->expectsJson()) {
            return response()->json(['error' => 'Federation features are disabled in centralized mode'], 404);
        }
        
        return redirect('/')->with('error', 'Federation features are disabled in centralized mode');
    }
}