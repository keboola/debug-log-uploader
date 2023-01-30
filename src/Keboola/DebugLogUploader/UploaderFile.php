<?php

namespace Keboola\DebugLogUploader;

use Symfony\Component\Filesystem\Filesystem;
use Zend\Json\Json;

class UploaderFile implements UploaderInterface
{
    /** @var string */
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Uploads attachment (makes a copy of file)
     * @param $filePath
     * @param string $contentType
     * @return string
     */
    public function upload($filePath, $contentType = 'text/plain')
    {
        $fileName = $this->path . '/' . $this->getFilePathAndUniquePrefix() . basename($filePath);

        (new Filesystem)->copy($filePath, $fileName);

        return $fileName;
    }

    /**
     * Writes log message to file
     * @param $name
     * @param $content
     * @param string $contentType
     * @return string
     */
    public function uploadString($name, $content, $contentType = 'text/plain')
    {
        $fileName = $this->path . '/' . $this->getFilePathAndUniquePrefix() . $name;

        if ($contentType === 'application/json') {
            $content = \Zend_Json::prettyPrint($content);
        }

        (new Filesystem)->dumpFile($fileName, $content);

        return $fileName;
    }

    /**
     * Gets file path and its prefix
     * @return string
     */
    public function getFilePathAndUniquePrefix()
    {
        return date('Y/m/d/H/') . date('Y-m-d-H-i-s') . '-' . uniqid() . '-';
    }
}
