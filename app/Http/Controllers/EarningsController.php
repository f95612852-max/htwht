<?php

namespace App\Http\Controllers;

use App\Models\UserEarnings;
use App\Models\PostViewLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EarningsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get or create earnings record
        $earnings = UserEarnings::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_views' => 0,
                'total_earnings' => 0.00,
                'pending_earnings' => 0.00,
                'paid_earnings' => 0.00,
            ]
        );

        // Get recent view statistics
        $recentViews = $this->getRecentViewStats($user->id);
        
        // Get top performing posts
        $topPosts = $this->getTopPerformingPosts($user->id);

        return view('settings.earnings', compact('earnings', 'recentViews', 'topPosts'));
    }

    public function api()
    {
        $user = Auth::user();
        
        $earnings = UserEarnings::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_views' => 0,
                'total_earnings' => 0.00,
                'pending_earnings' => 0.00,
                'paid_earnings' => 0.00,
            ]
        );

        return response()->json([
            'total_views' => $earnings->total_views,
            'total_earnings' => $earnings->total_earnings,
            'pending_earnings' => $earnings->pending_earnings,
            'paid_earnings' => $earnings->paid_earnings,
            'earnings_rate' => $earnings->getEarningsPerThousandViews(),
            'views_needed_for_next_earning' => $earnings->getViewsNeededForNextEarning(),
            'last_calculated_at' => $earnings->last_calculated_at,
        ]);
    }

    public function viewStats(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '7days'); // 7days, 30days, 90days
        
        $startDate = match($period) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            default => now()->subDays(7),
        };

        $viewStats = PostViewLog::join('statuses', 'post_view_logs.status_id', '=', 'statuses.id')
            ->join('profiles', 'statuses.profile_id', '=', 'profiles.id')
            ->where('profiles.user_id', $user->id)
            ->where('post_view_logs.viewed_at', '>=', $startDate)
            ->selectRaw('DATE(post_view_logs.viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($viewStats);
    }

    private function getRecentViewStats(int $userId): array
    {
        $periods = [
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
        ];

        $stats = [];
        
        foreach ($periods as $period => $startDate) {
            $endDate = match($period) {
                'today' => now()->endOfDay(),
                'yesterday' => now()->subDay()->endOfDay(),
                'this_week' => now()->endOfWeek(),
                'this_month' => now()->endOfMonth(),
            };

            $views = PostViewLog::join('statuses', 'post_view_logs.status_id', '=', 'statuses.id')
                ->join('profiles', 'statuses.profile_id', '=', 'profiles.id')
                ->where('profiles.user_id', $userId)
                ->whereBetween('post_view_logs.viewed_at', [$startDate, $endDate])
                ->count();

            $stats[$period] = $views;
        }

        return $stats;
    }

    private function getTopPerformingPosts(int $userId, int $limit = 5): array
    {
        return PostViewLog::join('statuses', 'post_view_logs.status_id', '=', 'statuses.id')
            ->join('profiles', 'statuses.profile_id', '=', 'profiles.id')
            ->where('profiles.user_id', $userId)
            ->select('statuses.id', 'statuses.caption', DB::raw('COUNT(*) as view_count'))
            ->groupBy('statuses.id', 'statuses.caption')
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function explain()
    {
        return view('earnings.explain');
    }
}