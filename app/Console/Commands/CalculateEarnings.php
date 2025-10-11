<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEarningsForAllUsers;
use Illuminate\Console\Command;

class CalculateEarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'earnings:calculate {--rate=0.3 : Earnings rate per 1000 views}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate earnings for all users based on post views';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rate = (float) $this->option('rate');
        
        $this->info("Starting earnings calculation with rate: $" . $rate . " per 1000 views");
        
        // Dispatch the job to process all users
        ProcessEarningsForAllUsers::dispatch($rate);
        
        $this->info('Earnings calculation job dispatched successfully!');
        $this->info('Check the queue worker logs for progress updates.');
        
        return 0;
    }
}