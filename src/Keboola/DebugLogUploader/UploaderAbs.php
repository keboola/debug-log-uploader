<?php

namespace Keboola\DebugLogUploader;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CommitBlobBlocksOptions;
use MicrosoftAzure\Storage\Blob\Models\Block;

class UploaderAbs implements UploaderInterface
{
    const PAD_LENGTH = 5;
    const CHUNK_SIZE = 4 * 1024 * 1024;

    /** @var string */
    private $urlPrefix;

    /** @var string */
    private $container;

    /** @var string */
    private $uploadPath;

    /** @var BlobRestProxy */
    private $absClient;

    public function __construct(BlobRestProxy $absClient, array $config)
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

        $this->absClient = $absClient;
    }

    /**
     * Uploads file to ABS
     * @param $filePath
     * @param string $contentType
     * @return string
     */
    public function upload($filePath, $contentType = 'text/plain')
    {
        $fileName = trim($this->uploadPath, '/') . '/' .  $this->getFilePathAndUniquePrefix() . basename($filePath);

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
            $this->absClient->createBlobBlock($this->container, $fileName, $blockId, $data);
            $counter++;
        }
        fclose($handle);

        $options = new CommitBlobBlocksOptions();
        $options->setContentType($contentType);

        $this->absClient->commitBlobBlocks($this->container, $fileName, $blockIds, $options);

        return $this->withUrlPrefix($fileName);
    }

    /**
     * Uploads string as file to ABS
     * @param $name
     * @param $content
     * @param string $contentType
     * @return string
     */
    public function uploadString($name, $content, $contentType = 'text/plain')
    {
        $fileName = trim($this->uploadPath, '/') . '/' .  $this->getFilePathAndUniquePrefix() . basename($name);

        $options = new CreateBlockBlobOptions();
        $options->setContentDisposition(sprintf('attachment; filename=%s', $fileName));
        $options->setContentType($contentType);

        $this->absClient->createBlockBlob(
            $this->container,
            $fileName,
            $content,
            $options
        );

        return $this->withUrlPrefix($fileName);
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
