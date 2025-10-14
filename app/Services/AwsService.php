<?php

namespace App\Services;

use App\Services\FirebaseService;

/**
 * Legacy AWS Service - Now redirects to Firebase
 * This class maintains compatibility while using Firebase services
 */
class AwsService
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Legacy S3 upload - now uses Firebase Storage
     */
    public function uploadToS3($file, $path)
    {
        return $this->firebaseService->uploadFile($path, $file);
    }

    /**
     * Legacy SES email - now uses Firebase or alternative email service
     */
    public function sendEmail($to, $subject, $body)
    {
        // Firebase doesn't have email service, so we'll use Laravel's mail
        return \Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    /**
     * Check if AWS is enabled (always false now)
     */
    public function isEnabled()
    {
        return false; // AWS is disabled in favor of Firebase
    }

    /**
     * Delete file from Firebase Storage (replaces S3)
     */
    public function deleteFromS3($path)
    {
        try {
            $bucket = $this->firebaseService->storage()->getBucket();
            $object = $bucket->object($path);
            $object->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete from Firebase Storage: ' . $e->getMessage());
        }
    }

    /**
     * Get Firebase Storage URL for media (replaces CloudFront)
     */
    public function getCloudFrontUrl($path)
    {
        try {
            $bucket = $this->firebaseService->storage()->getBucket();
            $object = $bucket->object($path);
            return $object->signedUrl(new \DateTime('+1 hour'));
        } catch (\Exception $e) {
            return null;
        }
    }
}