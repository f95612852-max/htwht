<?php

namespace App\Jobs;

use App\Models\UserEarnings;
use App\Models\PostViewLog;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateUserEarnings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;
    protected float $earningsRate;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, float $earningsRate = 0.3)
    {
        $this->user = $user;
        $this->earningsRate = $earningsRate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $this->calculateEarnings();
            });
        } catch (\Exception $e) {
            Log::error('Failed to calculate earnings for user ' . $this->user->id, [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id,
            ]);
            
            throw $e;
        }
    }

    private function calculateEarnings(): void
    {
        // Get or create earnings record
        $earnings = UserEarnings::firstOrCreate(
            ['user_id' => $this->user->id],
            [
                'total_views' => 0,
                'total_earnings' => 0.00,
                'pending_earnings' => 0.00,
                'paid_earnings' => 0.00,
                'last_calculated_at' => now(),
            ]
        );

        // Get total unique views for user's posts
        $totalViews = PostViewLog::join('statuses', 'post_view_logs.status_id', '=', 'statuses.id')
            ->join('profiles', 'statuses.profile_id', '=', 'profiles.id')
            ->where('profiles.user_id', $this->user->id)
            ->where('post_view_logs.viewed_at', '>', $earnings->last_calculated_at ?? now()->subYears(10))
            ->count();

        // Calculate new earnings
        $newEarnings = ($totalViews / 1000) * $this->earningsRate;
        
        // Update earnings record
        $earnings->update([
            'total_views' => $earnings->total_views + $totalViews,
            'total_earnings' => $earnings->total_earnings + $newEarnings,
            'pending_earnings' => $earnings->pending_earnings + $newEarnings,
            'last_calculated_at' => now(),
        ]);

        Log::info('Calculated earnings for user', [
            'user_id' => $this->user->id,
            'new_views' => $totalViews,
            'new_earnings' => $newEarnings,
            'total_earnings' => $earnings->total_earnings,
        ]);
    }
}