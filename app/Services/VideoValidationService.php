<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Http\UploadedFile;

class VideoValidationService
{
    /**
     * Validate video duration
     */
    public static function validateDuration(UploadedFile $video, int $maxDuration = 40): bool
    {
        try {
            $ffprobe = FFProbe::create();
            $duration = $ffprobe
                ->format($video->getPathname())
                ->get('duration');

            return $duration <= $maxDuration;
        } catch (\Exception $e) {
            \Log::error('Video duration validation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get video duration in seconds
     */
    public static function getDuration(UploadedFile $video): ?float
    {
        try {
            $ffprobe = FFProbe::create();
            return $ffprobe
                ->format($video->getPathname())
                ->get('duration');
        } catch (\Exception $e) {
            \Log::error('Failed to get video duration: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate video file
     */
    public static function validateVideo(UploadedFile $video): array
    {
        $maxDuration = config('centralized.limits.max_video_duration', 40);
        
        $result = [
            'valid' => true,
            'errors' => [],
            'duration' => null,
        ];

        // Check if it's a video file
        if (!str_starts_with($video->getMimeType(), 'video/')) {
            $result['valid'] = false;
            $result['errors'][] = 'الملف ليس فيديو صالح';
            return $result;
        }

        // Get duration
        $duration = self::getDuration($video);
        $result['duration'] = $duration;

        if ($duration === null) {
            $result['valid'] = false;
            $result['errors'][] = 'لا يمكن قراءة مدة الفيديو';
            return $result;
        }

        // Check duration limit
        if ($duration > $maxDuration) {
            $result['valid'] = false;
            $result['errors'][] = "مدة الفيديو تتجاوز الحد المسموح ({$maxDuration} ثانية)";
        }

        return $result;
    }
}