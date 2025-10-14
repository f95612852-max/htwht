<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDomainBlock;
use App\Http\Resources\MastoApi\Admin\DomainBlockResource;

class DomainBlockController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $blocks = UserDomainBlock::where('profile_id', $user->profile_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return DomainBlockResource::collection($blocks);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255'
        ]);
        
        $user = $request->user();
        
        $block = UserDomainBlock::firstOrCreate([
            'profile_id' => $user->profile_id,
            'domain' => $request->domain
        ]);
        
        return new DomainBlockResource($block);
    }
    
    public function delete(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255'
        ]);
        
        $user = $request->user();
        
        UserDomainBlock::where('profile_id', $user->profile_id)
            ->where('domain', $request->domain)
            ->delete();
            
        return response()->json(['message' => 'Domain block removed successfully']);
    }
}