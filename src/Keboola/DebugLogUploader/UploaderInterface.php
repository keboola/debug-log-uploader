<?php

namespace Keboola\DebugLogUploader;

interface UploaderInterface
{
    /**
     * @return string path to the uploaded resource
     */
    public function upload($filePath, $contentType = 'text/plain');

    /**
     * @return string path to the uploaded resource
     */
    public function uploadString($name, $content, $contentType = 'text/plain');

    /**
     * @return string
     */
    public function getFilePathAndUniquePrefix();
}
