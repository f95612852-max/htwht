<?php

namespace App\Http\Middleware;

use App\Models\PostViewLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackPostViews
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only track views for successful GET requests to post pages
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            $this->trackView($request);
        }

        return $response;
    }

    private function trackView(Request $request): void
    {
        // Extract post ID from route
        $postId = $this->extractPostId($request);
        
        if (!$postId) {
            return;
        }

        $viewerId = Auth::id();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Log the view asynchronously to avoid slowing down the response
        dispatch(function () use ($postId, $viewerId, $ipAddress, $userAgent) {
            PostViewLog::logView($postId, $viewerId, $ipAddress, $userAgent);
        })->afterResponse();
    }

    private function extractPostId(Request $request): ?int
    {
        // Check if this is a post view route
        $route = $request->route();
        
        if (!$route) {
            return null;
        }

        $routeName = $route->getName();
        
        // Handle different post view routes
        if (in_array($routeName, ['status.show', 'post.show', 'p.show'])) {
            return $route->parameter('id') ?? $route->parameter('status') ?? $route->parameter('post');
        }

        // Handle URL patterns like /p/{id} or /status/{id}
        $path = $request->path();
        
        if (preg_match('/^p\/(\d+)/', $path, $matches)) {
            return (int) $matches[1];
        }
        
        if (preg_match('/^status\/(\d+)/', $path, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}