<?php

namespace Keboola\DebugLogUploader;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;

class UploaderAbs implements UploaderInterface
{
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
        // TODO
        return '';
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
