<?php

namespace App\Service;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoStorage
{
    private S3Client $s3Client;
    private string $bucketName;

    public function __construct(string $awsKey, string $awsSecret, string $bucketName, string $region = 'eu-west-3')
    {
        $this->bucketName = $bucketName;
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => $region,
            'credentials' => [
                'key'    => $awsKey,
                'secret' => $awsSecret,
            ],
        ]);
    }

    public function uploadVideo(UploadedFile $file): string
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        
        $this->s3Client->putObject([
            'Bucket' => $this->bucketName,
            'Key'    => 'videos/' . $filename,
            'Body'   => fopen($file->getPathname(), 'rb'),
            'ACL'    => 'public-read',
            'ContentType' => $file->getMimeType(),
        ]);

        return $this->s3Client->getObjectUrl($this->bucketName, 'videos/' . $filename);
    }

    public function deleteVideo(string $url): void
    {
        $path = parse_url($url, PHP_URL_PATH);
        $key = ltrim($path, '/');

        $this->s3Client->deleteObject([
            'Bucket' => $this->bucketName,
            'Key'    => $key,
        ]);
    }
} 