<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\Ses\SesClient;
use Illuminate\Support\Facades\Storage;

class AwsService
{
    protected $s3Client;
    protected $sesClient;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => config('aws.region'),
            'credentials' => [
                'key' => config('aws.credentials.key'),
                'secret' => config('aws.credentials.secret'),
            ],
        ]);

        $this->sesClient = new SesClient([
            'version' => 'latest',
            'region' => config('aws.ses.region'),
            'credentials' => [
                'key' => config('aws.ses.key'),
                'secret' => config('aws.ses.secret'),
            ],
        ]);
    }

    /**
     * Upload file to S3
     */
    public function uploadToS3($file, $path)
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => config('aws.s3.bucket'),
                'Key' => $path,
                'Body' => $file,
                'ACL' => 'public-read',
            ]);

            return $result['ObjectURL'];
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload to S3: ' . $e->getMessage());
        }
    }

    /**
     * Delete file from S3
     */
    public function deleteFromS3($path)
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => config('aws.s3.bucket'),
                'Key' => $path,
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete from S3: ' . $e->getMessage());
        }
    }

    /**
     * Send email via SES
     */
    public function sendEmail($to, $subject, $body, $from = null)
    {
        try {
            $result = $this->sesClient->sendEmail([
                'Destination' => [
                    'ToAddresses' => [$to],
                ],
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => 'UTF-8',
                            'Data' => $body,
                        ],
                    ],
                    'Subject' => [
                        'Charset' => 'UTF-8',
                        'Data' => $subject,
                    ],
                ],
                'Source' => $from ?: config('mail.from.address'),
            ]);

            return $result['MessageId'];
        } catch (\Exception $e) {
            throw new \Exception('Failed to send email via SES: ' . $e->getMessage());
        }
    }

    /**
     * Get CloudFront URL for media
     */
    public function getCloudFrontUrl($path)
    {
        if (config('aws.cloudfront.enabled')) {
            return 'https://' . config('aws.cloudfront.domain') . '/' . $path;
        }

        return config('aws.s3.url') . '/' . $path;
    }
}