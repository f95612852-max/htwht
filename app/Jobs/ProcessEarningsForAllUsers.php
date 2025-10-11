<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEarningsForAllUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected float $earningsRate;

    /**
     * Create a new job instance.
     */
    public function __construct(float $earningsRate = 0.3)
    {
        $this->earningsRate = $earningsRate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting earnings calculation for all users');

        $processedUsers = 0;
        
        // Process users in chunks to avoid memory issues
        User::whereNotNull('profile_id')
            ->chunk(100, function ($users) use (&$processedUsers) {
                foreach ($users as $user) {
                    try {
                        // Dispatch individual user earnings calculation
                        CalculateUserEarnings::dispatch($user, $this->earningsRate);
                        $processedUsers++;
                    } catch (\Exception $e) {
                        Log::error('Failed to dispatch earnings calculation for user ' . $user->id, [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        Log::info('Dispatched earnings calculation jobs', [
            'total_users' => $processedUsers,
            'earnings_rate' => $this->earningsRate,
        ]);
    }
}