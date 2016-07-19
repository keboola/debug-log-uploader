<?php

namespace Keboola\DebugLogUploader;

interface UploaderInterface
{
    public function upload($filePath, $contentType = 'text/plain');

    public function uploadString($name, $content, $contentType = 'text/plain');

    public function getFilePathAndUniquePrefix();
}
