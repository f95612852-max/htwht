<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class RemoveFederationFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'centralized:remove-federation {--force : Force removal without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove federation and ActivityPub features for centralized mode';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will remove federation features. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 1;
            }
        }

        $this->info('Removing federation features...');

        // Disable federation in config
        $this->disableFederationConfig();
        
        // Clean up federation-related database records
        $this->cleanupFederationData();
        
        // Remove federation routes (if they exist in separate files)
        $this->removeFederationRoutes();
        
        $this->info('Federation features removed successfully!');
        $this->info('Please restart your application and clear caches.');
        
        return 0;
    }

    private function disableFederationConfig(): void
    {
        $this->info('Disabling federation in configuration...');
        
        $configPath = config_path('centralized.php');
        
        if (!File::exists($configPath)) {
            File::put($configPath, "<?php\n\nreturn [\n    'federation_enabled' => false,\n    'activitypub_enabled' => false,\n    'remote_follows_enabled' => false,\n];\n");
        }
        
        $this->info('✓ Federation configuration updated');
    }

    private function cleanupFederationData(): void
    {
        $this->info('Cleaning up federation-related data...');
        
        try {
            // Clean up remote profiles and follows
            DB::table('profiles')->where('domain', '!=', null)->delete();
            DB::table('followers')->where('following_id', 'NOT IN', function($query) {
                $query->select('id')->from('profiles')->whereNull('domain');
            })->delete();
            
            // Clean up federation-related activities
            DB::table('activities')->whereIn('type', [
                'Follow', 'Accept', 'Reject', 'Undo', 'Block', 'Announce'
            ])->delete();
            
            $this->info('✓ Federation data cleaned up');
        } catch (\Exception $e) {
            $this->warn('Some federation data cleanup failed: ' . $e->getMessage());
        }
    }

    private function removeFederationRoutes(): void
    {
        $this->info('Checking for federation routes...');
        
        $federationRoutes = [
            'routes/federation.php',
            'routes/activitypub.php',
            'routes/webfinger.php',
        ];
        
        foreach ($federationRoutes as $routeFile) {
            $path = base_path($routeFile);
            if (File::exists($path)) {
                File::delete($path);
                $this->info("✓ Removed $routeFile");
            }
        }
    }
}