<?php

namespace App\Jobs\StatusPipeline;

use App\Media;
use App\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewStatusPipeline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $status;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Increased timeout to handle cloud storage operations
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Number of times to attempt the job
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Backoff periods between retries (in seconds)
     *
     * @var array
     */
    public $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Status $status)
    {
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Skip media check if cloud storage isn't enabled or fast processing is on
        if (! config_cache('pixelfed.cloud_storage') || config('pixelfed.media_fast_process')) {
            // Federation removed - centralized mode

            return;
        }

        // Check for media still processing
        $stillProcessing = Media::whereStatusId($this->status->id)
            ->whereNull('cdn_url')
            ->exists();

        if ($stillProcessing) {
            // Get the oldest processing media item
            $oldestProcessingMedia = Media::whereStatusId($this->status->id)
                ->whereNull('cdn_url')
                ->oldest()
                ->first();

            // If media has been processing for more than 10 minutes, proceed anyway
            if ($oldestProcessingMedia && $oldestProcessingMedia->replicated_at && $oldestProcessingMedia->replicated_at->diffInMinutes(now()) > 10) {
                if (config('federation.activitypub.delivery.logger.enabled')) {
                    Log::warning('Media processing timeout for status '.$this->status->id.'. Proceeding with federation.');
                }
                // Federation removed - centralized mode

                return;
            }

            // Release job back to queue with delay of 30 seconds
            $this->release(30);

            return;
        }

        // All media processed - centralized mode, no federation needed
    }



    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        if (config('federation.activitypub.delivery.logger.enabled')) {
            Log::error('NewStatusPipeline failed for status '.$this->status->id, [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
