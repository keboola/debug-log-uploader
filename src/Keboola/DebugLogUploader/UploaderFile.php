<?php

declare(strict_types=1);

namespace Keboola\DebugLogUploader;

use Zend\Json\Json;

use Symfony\Component\Filesystem\Filesystem;

class UploaderFile extends AbstractUploader
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Uploads attachment (makes a copy of file)
     */
    public function upload(
        string $filePath,
        string $contentType = 'text/plain'
    ): string {
        $fileName = $this->path . '/' . $this->getFilePathAndUniquePrefix() . basename($filePath);

        (new Filesystem)->copy($filePath, $fileName);

        return $fileName;
    }

    /**
     * Writes log message to file
     */
    public function uploadString(
        string $name,
        string $content,
        string $contentType = 'text/plain'
    ): string {
        $fileName = $this->path . '/' . $this->getFilePathAndUniquePrefix() . $name;

        if ($contentType === 'application/json') {
            $content = Json::prettyPrint($content);
        }

        (new Filesystem)->dumpFile($fileName, $content);

        return $fileName;
    }
}
