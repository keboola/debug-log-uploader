<?php

declare(strict_types=1);

namespace Keboola\DebugLogUploader;

abstract class AbstractUploader implements UploaderInterface
{
    /**
     * Gets file path and its prefix
     */
    protected function getFilePathAndUniquePrefix(): string
    {
        return date('Y/m/d/H/') . date('Y-m-d-H-i-s') . '-' . uniqid() . '-';
    }
}
