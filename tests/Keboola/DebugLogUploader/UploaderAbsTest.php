<?php

namespace Keboola\DebugLogUploader;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Symfony\Component\Filesystem\Filesystem;

class UploaderAbsTest extends \PHPUnit\Framework\TestCase
{
    private ?\Symfony\Component\Filesystem\Filesystem $fs = null;

    private string $sourcePath = '/tmp/uploader-abs-test-source';

    protected function setUp(): void
    {
        $this->fs = new Filesystem;
        $this->fs->remove($this->sourcePath);
        $this->fs->mkdir($this->sourcePath);

        parent::setUp();
    }

    public function testMissingRequiredParams()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Please set all required config parameters.'
            . ' Missing: container, upload-path, url-prefix'
        );

        new UploaderAbs($this->getAbsClient(), []);
    }

    private function getAbsClient()
    {
        return BlobRestProxy::createBlobService(UPLOADER_ABS_CONNECTION_STRING);
    }

    public function testUploadString()
    {
        $fileContent = <<<TXT
ABS uploader upload string
TXT;
        $name = 'abs-uploader-string.txt';

        $uploader = new UploaderAbs(
            $this->getAbsClient(),
            [
                'container' => UPLOADER_ABS_CONTAINER,
                'upload-path' => '/tests/x/',
                'url-prefix' => '',
            ]
        );

        $returnUrl = $uploader->uploadString($name, $fileContent);

        $client = $this->getAbsClient();
        $blob = $client->getBlob(UPLOADER_ABS_CONTAINER, $returnUrl);

        $this->assertEquals('ABS uploader upload string', fread($blob->getContentStream(), 1000));
        $this->assertEquals('text/plain', $blob->getProperties()->getContentType());
    }

    public function testUploadStringEmptyUploadPath()
    {
        $fileContent = <<<TXT
ABS uploader upload string empty upload path
TXT;
        $name = 'abs-uploader-string-empty-upload-path.txt';

        $uploader = new UploaderAbs(
            $this->getAbsClient(),
            [
                'container' => UPLOADER_ABS_CONTAINER,
                'upload-path' => '',
                'url-prefix' => '',
            ]
        );

        $returnUrl = $uploader->uploadString($name, $fileContent);

        $client = $this->getAbsClient();
        $blob = $client->getBlob(UPLOADER_ABS_CONTAINER, $returnUrl);

        $this->assertEquals('ABS uploader upload string empty upload path', fread($blob->getContentStream(), 1000));
        $this->assertEquals('text/plain', $blob->getProperties()->getContentType());
    }

    public function testUploadFile()
    {
        $fileContent = <<<TXT
ABS uploader upload file
TXT;
        $fileName = 'abs-uploader-file.txt';
        $sourceFile = $this->sourcePath . '/' . $fileName;
        $this->fs->dumpFile($sourceFile, $fileContent);

        $uploader = new UploaderAbs(
            $this->getAbsClient(),
            [
                'container' => UPLOADER_ABS_CONTAINER,
                'upload-path' => '/tests/y/',
                'url-prefix' => '',
            ]
        );

        $returnUrl = $uploader->upload($sourceFile);

        $client = $this->getAbsClient();
        $blob = $client->getBlob(UPLOADER_ABS_CONTAINER, $returnUrl);

        $this->assertEquals('ABS uploader upload file', fread($blob->getContentStream(), 1000));
        $this->assertEquals('text/plain', $blob->getProperties()->getContentType());
    }
}
