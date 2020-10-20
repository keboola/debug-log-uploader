<?php

declare(strict_types=1);

namespace Keboola\DebugLogUploader;

use Aws\S3\S3Client;

class UploaderS3 extends AbstractUploader
{
    /** @var string */
    private $urlPrefix;

    /** @var string */
    private $s3path;

    /** @var S3Client */
    private $s3client;

    public function __construct(
        S3Client $s3Client,
        string $urlPrefix,
        string $s3path
    ) {
        $this->s3path = $s3path;
        $this->urlPrefix = $urlPrefix;
        $this->s3client = $s3Client;
    }

    /**
     * Uploads file to s3
     */
    public function upload(
        string $filePath,
        string $contentType = 'text/plain'
    ): string {
        $s3FileName = $this->getFilePathAndUniquePrefix() . basename($filePath);
        [$bucket, $prefix] = explode('/', $this->s3path, 2);

        $this->s3client->putObject([
            'Bucket' => $bucket,
            'Key' => (empty($prefix) ? '' : (trim($prefix, '/') . '/')) . $s3FileName,
            'ContentType' => $contentType,
            'ACL' => 'private',
            'ServerSideEncryption' => 'AES256',
            'SourceFile' => $filePath,
        ]);

        return $this->withUrlPrefix($s3FileName);
    }

    /**
     * Prepends URL prefix to file name
     */
    private function withUrlPrefix(string $logFileName): string
    {
        return $this->urlPrefix . $logFileName;
    }

    /**
     * Uploads string as file to s3
     */
    public function uploadString(
        string $name,
        string $content,
        string $contentType = 'text/plain'
    ): string {
        $s3FileName = $this->getFilePathAndUniquePrefix() . $name;
        [$bucket, $prefix] = explode('/', $this->s3path, 2);

        $this->s3client->putObject([
            'Bucket' => $bucket,
            'Key' => (empty($prefix) ? '' : (trim($prefix, '/') . '/')) . $s3FileName,
            'ContentType' => $contentType,
            'ACL' => 'private',
            'ServerSideEncryption' => 'AES256',
            'Body' => $content,
        ]);

        return $this->withUrlPrefix($s3FileName);
    }
}
