<?php

namespace Keboola\DebugLogUploader;

interface UploaderInterface
{
    /**
     * @return string path to the uploaded resource
     */
    public function upload(string $filePath, string $contentType = 'text/plain'): string;

    /**
     * @return string path to the uploaded resource
     */
    public function uploadString(string $name, string $content, string $contentType = 'text/plain'): string;

    public function getFilePathAndUniquePrefix(): string;
}
