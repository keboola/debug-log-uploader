<?php

namespace Keboola\DebugLogUploader;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CommitBlobBlocksOptions;
use MicrosoftAzure\Storage\Blob\Models\Block;

class UploaderAbs implements UploaderInterface
{
    public const PAD_LENGTH = 5;
    public const CHUNK_SIZE = 4 * 1024 * 1024;

    private string $urlPrefix;

    private string $container;

    private string $uploadPath;

    public function __construct(private readonly BlobRestProxy $client, array $config)
    {
        $errors = [];
        foreach (['container', 'upload-path', 'url-prefix'] as $parameter) {
            if (!isset($config[$parameter])) {
                $errors[] = $parameter;
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Please set all required config parameters. Missing: ' . implode(', ', $errors));
        }

        $this->container = $config['container'];
        $this->urlPrefix = $config['url-prefix'];
        $this->uploadPath = $config['upload-path'];
    }

    /**
     * Uploads file to ABS
     */
    public function upload(string $filePath, string $contentType = 'text/plain'): string
    {
        $fileName = $this->getUploadPath() . $this->getFilePathAndUniquePrefix() . basename($filePath);

        $handle = fopen($filePath, 'rb');
        $counter = 1;
        $blockIds = [];

        while (!feof($handle)) {
            $blockId = base64_encode(str_pad($counter, self::PAD_LENGTH, '0', STR_PAD_LEFT));
            $block = new Block();
            $block->setBlockId($blockId);
            $block->setType('Uncommitted');
            $blockIds[] = $block;
            $data = fread($handle, self::CHUNK_SIZE);
            // Upload the block.
            $this->client->createBlobBlock($this->container, $fileName, $blockId, $data);
            $counter++;
        }
        fclose($handle);

        $options = new CommitBlobBlocksOptions();
        $options->setContentType($contentType);

        $this->client->commitBlobBlocks($this->container, $fileName, $blockIds, $options);

        return $this->withUrlPrefix($fileName);
    }

    /**
     * Uploads string as file to ABS
     */
    public function uploadString(string $name, string $content, string $contentType = 'text/plain'): string
    {
        $fileName = $this->getUploadPath() . $this->getFilePathAndUniquePrefix() . basename($name);

        $options = new CreateBlockBlobOptions();
        $options->setContentDisposition(sprintf('attachment; filename=%s', $fileName));
        $options->setContentType($contentType);

        $this->client->createBlockBlob(
            $this->container,
            $fileName,
            $content,
            $options
        );

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
