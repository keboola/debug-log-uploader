<?php

namespace Keboola\DebugLogUploader;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\WriteStream;

class UploaderGcs implements UploaderInterface
{
    public const CHUNK_SIZE = 4 * 1024 * 1024;

    private mixed $urlPrefix;

    private mixed $bucket;

    private string $uploadPath;

    public function __construct(
        private readonly StorageClient $client,
        array $config
    ) {
        $errors = [];
        foreach (['bucket', 'upload-path', 'url-prefix'] as $parameter) {
            if (!isset($config[$parameter])) {
                $errors[] = $parameter;
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Please set all required config parameters. Missing: ' . implode(', ', $errors));
        }

        $this->bucket = $config['bucket'];
        $this->urlPrefix = $config['url-prefix'];
        $this->uploadPath = $config['upload-path'];
    }

    /**
     * Uploads file to GCS
     */
    public function upload(string $filePath, string $contentType = 'text/plain'): string
    {
        $bucket = $this->client->bucket($this->bucket);
        $writeStream = new WriteStream(null, [
            'chunkSize' => self::CHUNK_SIZE,
        ]);
        $fileName = $this->getUploadPath() . $this->getFilePathAndUniquePrefix() . basename($filePath);
        $uploader = $bucket->getStreamableUploader($writeStream, [
            'name' => $fileName,
            'metadata' => [
                'contentType' => $contentType,
            ],
        ]);
        $writeStream->setUploader($uploader);
        $stream = fopen($filePath, 'rb');
        if (!$stream) {
            throw new \Exception(sprintf('File "%s" not found', $filePath));
        }
        while (($line = stream_get_line($stream, self::CHUNK_SIZE)) !== false) {
            $writeStream->write($line);
        }
        $writeStream->close();

        return $this->withUrlPrefix($fileName);
    }

    /**
     * Uploads string as file to GCS
     */
    public function uploadString(string $name, string $content, string $contentType = 'text/plain'): string
    {
        $bucket = $this->client->bucket($this->bucket);
        $fileName = $this->getUploadPath() . $this->getFilePathAndUniquePrefix() . $name;
        $bucket->upload($content, [
            'name' => $fileName,
            'contentType' => $contentType,
            'metadata' => [
                'contentType' => $contentType,
            ],
        ]);

        return $this->withUrlPrefix($fileName);
    }

    /**
     * Gets file path and its prefix
     */
    public function getFilePathAndUniquePrefix(): string
    {
        return date('Y/m/d/H/') . date('Y-m-d-H-i-s') . '-' . uniqid() . '-';
    }

    /**
     * Gets upload path (with check for empty path)
     */
    private function getUploadPath(): string
    {
        $uploadPath = trim($this->uploadPath, '/');

        if (empty($uploadPath)) {
            return '';
        }

        return $uploadPath . '/';
    }

    /**
     * Prepends URL prefix to file name
     */
    private function withUrlPrefix(string $logFileName): string
    {
        return $this->urlPrefix . $logFileName;
    }
}
