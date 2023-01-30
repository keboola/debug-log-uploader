<?php

namespace Keboola\DebugLogUploader;

use Aws\S3\S3Client;

class UploaderS3 implements UploaderInterface
{
    /** @var string */
    private $urlPrefix;

    /** @var string */
    private $s3path;

    private \Aws\S3\S3Client $s3client;

    public function __construct(S3Client $s3Client, array $config)
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

        $this->s3client = $s3Client;
    }

    /**
     * Uploads file to s3
     * @param $filePath
     * @param string $contentType
     * @return string
     */
    public function upload($filePath, $contentType = 'text/plain')
    {
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
     * Uploads string as file to s3
     * @param $name
     * @param $content
     * @param string $contentType
     * @return string
     */
    public function uploadString($name, $content, $contentType = 'text/plain')
    {
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

    /**
     * Gets file path and its prefix
     * @return string
     */
    public function getFilePathAndUniquePrefix()
    {
        return date('Y/m/d/H/') . date('Y-m-d-H-i-s') . '-' . uniqid() . '-';
    }

    /**
     * Prepends URL prefix to file name
     * @param $logFileName string
     * @return string
     */
    private function withUrlPrefix($logFileName)
    {
        return $this->urlPrefix . $logFileName;
    }
}
