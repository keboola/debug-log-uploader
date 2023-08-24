<?php

namespace Keboola\DebugLogUploader;

use Symfony\Component\Filesystem\Filesystem;

class UploaderFile implements UploaderInterface
{
    public function __construct(private readonly string $path)
    {
    }

    /**
     * Uploads attachment (makes a copy of file)
     */
    public function upload(string $filePath, string $contentType = 'text/plain'): string
    {
        $fileName = $this->path . '/' . $this->getFilePathAndUniquePrefix() . basename($filePath);

        (new Filesystem)->copy($filePath, $fileName);

        return $fileName;
    }

    /**
     * Writes log message to file
     */
    public function uploadString(string $name, string $content, string $contentType = 'text/plain'): string
    {
        $fileName = $this->path . '/' . $this->getFilePathAndUniquePrefix() . $name;

        (new Filesystem)->dumpFile($fileName, $content);

        return $fileName;
    }

    /**
     * Gets file path and its prefix
     */
    public function getFilePathAndUniquePrefix(): string
    {
        return date('Y/m/d/H/') . date('Y-m-d-H-i-s') . '-' . uniqid() . '-';
    }
}
