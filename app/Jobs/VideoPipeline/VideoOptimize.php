<?php

namespace App\Jobs\VideoPipeline;

use App\Media;
use App\Services\MediaStorageService;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VideoOptimize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $media;
    public $timeout = 1800; // 30 minutes
    public $tries = 3;
    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $media = $this->media;
        
        if ($media->mime !== 'video/mp4') {
            return;
        }

        try {
            $this->optimizeVideo($media);
        } catch (\Exception $e) {
            Log::error('Video optimization failed: ' . $e->getMessage(), [
                'media_id' => $media->id,
                'media_path' => $media->media_path
            ]);
        }
    }

    /**
     * Optimize video file
     */
    protected function optimizeVideo(Media $media)
    {
        $inputPath = storage_path('app/' . $media->media_path);
        
        if (!file_exists($inputPath)) {
            Log::error('Video file not found for optimization', ['path' => $inputPath]);
            return;
        }

        // Create optimized filename
        $pathInfo = pathinfo($media->media_path);
        $optimizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_optimized.' . $pathInfo['extension'];
        $outputPath = storage_path('app/' . $optimizedPath);

        try {
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($inputPath);

            // Create format with compression settings
            $format = new X264();
            $format->setKiloBitrate(1000) // 1Mbps bitrate
                   ->setAudioCodec('aac')
                   ->setVideoCodec('libx264');

            // Save optimized video
            $video->save($format, $outputPath);

            // Check if optimization was successful and file size is smaller
            if (file_exists($outputPath)) {
                $originalSize = filesize($inputPath);
                $optimizedSize = filesize($outputPath);

                if ($optimizedSize < $originalSize && $optimizedSize > 0) {
                    // Replace original with optimized version
                    unlink($inputPath);
                    rename($outputPath, $inputPath);
                    
                    // Update media size
                    $media->size = $optimizedSize;
                    $media->save();

                    Log::info('Video optimized successfully', [
                        'media_id' => $media->id,
                        'original_size' => $originalSize,
                        'optimized_size' => $optimizedSize,
                        'compression_ratio' => round((1 - $optimizedSize / $originalSize) * 100, 2) . '%'
                    ]);
                } else {
                    // Optimization didn't improve file size, keep original
                    if (file_exists($outputPath)) {
                        unlink($outputPath);
                    }
                    Log::info('Video optimization skipped - no size improvement', [
                        'media_id' => $media->id
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Clean up on error
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
            throw $e;
        }
    }
}
