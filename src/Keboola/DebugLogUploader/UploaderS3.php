<?php

namespace Keboola\DebugLogUploader;

use Aws\S3\S3Client;

class UploaderS3 implements UploaderInterface
{
    /** @var string */
    private $urlPrefix;

    /** @var string */
    private $s3path;

    /** @var S3Client */
    private $s3client;

    public function __construct(array $config)
    {
        if (!isset(
            $config['aws-access-key'],
            $config['aws-secret-key'],
            $config['aws-region'],
            $config['s3-upload-path']
        )) {
            throw new \Exception('Please set all required config parameters.');
        }

        $this->s3path = $config['s3-upload-path'];

        $this->urlPrefix = isset($config['url-prefix'])
            ? $config['url-prefix']
            : 'https://connection.keboola.com/admin/utils/logs?file=';

        $this->s3client = new S3Client([
            'version' => '2006-03-01',
            'region' => $config['aws-region'],
            'retries' => 40,
            'credentials' => [
                'key' => $config['aws-access-key'],
                'secret' => $config['aws-secret-key']
            ]
        ]);
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
        list($bucket, $prefix) = explode('/', $this->s3path, 2);

        $this->s3client->putObject([
            'Bucket' => $bucket,
            'Key' => (empty($prefix) ? '' : (trim($prefix, '/') . '/')) . $s3FileName,
            'ContentType' => $contentType,
            'ACL' => 'private',
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
        list($bucket, $prefix) = explode('/', $this->s3path, 2);

        $this->s3client->putObject([
            'Bucket' => $bucket,
            'Key' => (empty($prefix) ? '' : (trim($prefix, '/') . '/')) . $s3FileName,
            'ContentType' => $contentType,
            'ACL' => 'private',
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
