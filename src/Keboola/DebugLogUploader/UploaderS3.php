<?php

namespace Keboola\DebugLogUploader;

use Aws\S3\S3Client;

class UploaderS3 implements UploaderInterface
{
    private string $urlPrefix;

    private string $s3path;

    public function __construct(private readonly S3Client $client, array $config)
    {
        $errors = [];
        foreach (['s3-upload-path', 'url-prefix'] as $parameter) {
            if (!isset($config[$parameter])) {
                $errors[] = $parameter;
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Please set all required config parameters. Missing: ' . implode(', ', $errors));
        }

        $this->s3path = $config['s3-upload-path'];
        $this->urlPrefix = $config['url-prefix'];
    }

    /**
     * Uploads file to s3
     */
    public function upload(string $filePath, string $contentType = 'text/plain'): string
    {
        $s3FileName = $this->getFilePathAndUniquePrefix() . basename($filePath);
        [$bucket, $prefix] = explode('/', $this->s3path, 2);

        $this->client->putObject([
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
     * Uploads string as file to s3
     */
    public function uploadString(string $name, string $content, string $contentType = 'text/plain'): string
    {
        $s3FileName = $this->getFilePathAndUniquePrefix() . $name;
        [$bucket, $prefix] = explode('/', $this->s3path, 2);

        $this->client->putObject([
            'Bucket' => $bucket,
            'Key' => (empty($prefix) ? '' : (trim($prefix, '/') . '/')) . $s3FileName,
            'ContentType' => $contentType,
            'ACL' => 'private',
            'ServerSideEncryption' => 'AES256',
            'Body' => $content,
        ]);

        return $this->withUrlPrefix($s3FileName);
    }

    /**
     * Gets file path and its prefix
     */
    public function getFilePathAndUniquePrefix(): string
    {
        return date('Y/m/d/H/') . date('Y-m-d-H-i-s') . '-' . uniqid() . '-';
    }

    /**
     * Prepends URL prefix to file name
     */
    private function withUrlPrefix(string $logFileName): string
    {
        return $this->urlPrefix . $logFileName;
    }
}
