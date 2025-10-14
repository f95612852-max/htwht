<?php

namespace App\Services;

use App\Services\FirebaseService;
use Exception;

class FilesystemService
{
    const VERIFY_FILE_NAME = 'cfstest.txt';

    /**
     * Verify Firebase Storage credentials
     */
    public static function getVerifyCredentials($projectId, $credentialsPath, $bucket)
    {
        try {
            $firebaseService = new FirebaseService();
            $storage = $firebaseService->storage();
            $bucket = $storage->getBucket($bucket);
            
            // Test write operation
            $bucket->upload('ok', [
                'name' => self::VERIFY_FILE_NAME
            ]);
            
            // Test read operation
            $object = $bucket->object(self::VERIFY_FILE_NAME);
            $content = $object->downloadAsString();
            
            if ($content !== 'ok') {
                return false;
            }
            
            // Test delete operation
            $object->delete();
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Legacy method for S3 compatibility - redirects to Firebase
     */
    public static function getVerifyS3Credentials($key, $secret, $region, $bucket, $endpoint)
    {
        // S3 is no longer supported, always return false
        return false;
    }
}
