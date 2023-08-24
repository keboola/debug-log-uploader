<?php

namespace Keboola\DebugLogUploader;

use Google\Cloud\Storage\StorageClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class UploaderGCSTest extends TestCase
{
    private ?Filesystem $fs = null;

    private string $sourcePath = '/tmp/uploader-gcs-test-source';

    protected function setUp(): void
    {
        $this->fs = new Filesystem;
        $this->fs->remove($this->sourcePath);
        $this->fs->mkdir($this->sourcePath);

        parent::setUp();
    }

    public function testMissingRequiredParams(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Please set all required config parameters.'
            . ' Missing: bucket, upload-path, url-prefix'
        );

        new UploaderGcs($this->getClient(), []);
    }

    private function getClient(): StorageClient
    {
        $credentials = json_decode(UPLOADER_GCS_BUCKET_KEY, true, 512, JSON_THROW_ON_ERROR);
        return new StorageClient(['keyFile' => $credentials]);
    }

    public function testUploadString(): void
    {
        $fileContent = <<<TXT
GCS uploader upload string
TXT;
        $name = 'gcs-uploader-string.txt';

        $uploader = new UploaderGcs(
            $this->getClient(),
            [
                'bucket' => UPLOADER_GCS_BUCKET,
                'upload-path' => '/tests/x/',
                'url-prefix' => '',
            ]
        );

        $returnUrl = $uploader->uploadString($name, $fileContent);

        $client = $this->getClient();
        $obj = $client->bucket(UPLOADER_GCS_BUCKET)
            ->object($returnUrl);
        $content = $obj->downloadAsString();

        $this->assertEquals('GCS uploader upload string', $content);
        $this->assertEquals('text/plain', $obj->info()['contentType']);
    }

    public function testUploadStringEmptyUploadPath(): void
    {
        $fileContent = <<<TXT
GCS uploader upload string empty upload path
TXT;
        $name = 'gcs-uploader-string-empty-upload-path.txt';

        $uploader = new UploaderGcs(
            $this->getClient(),
            [
                'bucket' => UPLOADER_GCS_BUCKET,
                'upload-path' => '',
                'url-prefix' => '',
            ]
        );

        $returnUrl = $uploader->uploadString($name, $fileContent);

        $client = $this->getClient();
        $obj = $client->bucket(UPLOADER_GCS_BUCKET)
            ->object($returnUrl);
        $content = $obj->downloadAsString();

        $this->assertEquals('GCS uploader upload string empty upload path', $content);
        $this->assertEquals('text/plain', $obj->info()['contentType']);
    }

    public function testUploadFile(): void
    {
        $fileContent = <<<TXT
GCS uploader upload file
TXT;
        $fileName = 'gcs-uploader-file.txt';
        $sourceFile = $this->sourcePath . '/' . $fileName;
        $this->fs->dumpFile($sourceFile, $fileContent);

        $uploader = new UploaderGcs(
            $this->getClient(),
            [
                'bucket' => UPLOADER_GCS_BUCKET,
                'upload-path' => '/tests/y/',
                'url-prefix' => '',
            ]
        );

        $returnUrl = $uploader->upload($sourceFile);

        $client = $this->getClient();
        $obj = $client->bucket(UPLOADER_GCS_BUCKET)
            ->object($returnUrl);
        $content = $obj->downloadAsString();

        $this->assertEquals('GCS uploader upload file', $content);
        $this->assertEquals('text/plain', $obj->info()['contentType']);
    }
}
