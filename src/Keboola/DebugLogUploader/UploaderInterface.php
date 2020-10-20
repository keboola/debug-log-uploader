<?php

declare(strict_types=1);

namespace Keboola\DebugLogUploader;

interface UploaderInterface
{
    public function upload(
        string $filePath,
        string $contentType = 'text/plain'
    ): string;

    public function uploadString(
        string $name,
        string $content,
        string $contentType = 'text/plain'
    ): string;
}
