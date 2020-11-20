<?php

namespace Keboola\DebugLogUploader;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Symfony\Component\Filesystem\Filesystem;

class UploaderAbsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filesystem */
    private $fs;

    /** @var UploaderAbs */
    private $uploader;

    /** @var string */
    private $sourcePath = '/tmp/uploader-abs-test-source';

    protected function setUp()
    {
        $this->fs = new Filesystem;
        $this->fs->remove($this->sourcePath);
        $this->fs->mkdir($this->sourcePath);

        $this->uploader = new UploaderAbs(
            $this->getAbsClient(),
            [
                'container' => UPLOADER_ABS_CONTAINER,
                'upload-path' => '/tests/x/',
                'url-prefix' => '',
            ]
        );

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

        $returnUrl = $this->uploader->uploadString($name, $fileContent);

        $client = $this->getAbsClient();
        $blob = $client->getBlob(UPLOADER_ABS_CONTAINER, $returnUrl);

        $this->assertEquals('ABS uploader upload string', fread($blob->getContentStream(), 1000));
        $this->assertEquals('text/plain', $blob->getProperties()->getContentType());
    }
}
