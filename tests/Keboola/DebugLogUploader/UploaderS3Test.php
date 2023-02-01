<?php

namespace Keboola\DebugLogUploader;

use Aws\S3\S3Client;
use Symfony\Component\Filesystem\Filesystem;

class UploaderS3Test extends \PHPUnit\Framework\TestCase
{
    private ?\Symfony\Component\Filesystem\Filesystem $fs = null;

    private ?\Keboola\DebugLogUploader\UploaderS3 $uploader = null;

    private string $sourcePath = '/tmp/uploader-s3-test-source';

    protected function setUp(): void
    {
        $this->fs = new Filesystem;
        $this->fs->remove($this->sourcePath);
        $this->fs->mkdir($this->sourcePath);

        $this->uploader = new UploaderS3(
            $this->getS3client(),
            [
                's3-upload-path' => UPLOADER_S3_BUCKET . '/tests/x/',
                'url-prefix' => '',
            ]
        );

        parent::setUp();
    }

    public function testMissingS3clientParams()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Please set all required config parameters.'
            . ' Missing: s3-upload-path, url-prefix'
        );

        new UploaderS3($this->getS3client(), []);
    }

    private function getS3client()
    {
        return new S3Client([
            'region' => UPLOADER_AWS_REGION,
            'credentials' => [
                'key' => UPLOADER_AWS_KEY,
                'secret' => UPLOADER_AWS_SECRET,
            ],
            'version' => '2006-03-01',
        ]);
    }

    public function testUploadFile()
    {
        $fileContent = <<<TXT
S3 uploader upload file
TXT;
        $fileName = 's3-uploader-file.txt';
        $sourceFile = $this->sourcePath . '/' . $fileName;
        $this->fs->dumpFile($sourceFile, $fileContent);

        $destinationFile = $this->uploader->upload($sourceFile);

        $client = $this->getS3client();

        $result = $client->getObject([
            'Bucket' => UPLOADER_S3_BUCKET,
            'Key' => 'tests/x/' . $destinationFile,
        ]);

        $metadata = $result->get('@metadata');

        $this->assertTrue(isset($metadata['statusCode']) && $metadata['statusCode'] == 200);
        $this->assertEquals('AES256', $result->get('ServerSideEncryption'));
    }

    public function testUploadString()
    {
        $fileContent = <<<TXT
S3 uploader upload string
TXT;
        $name = 's3-uploader-string.txt';

        $destinationFile = $this->uploader->uploadString($name, $fileContent);

        $client = $this->getS3client();

        $result = $client->getObject([
            'Bucket' => UPLOADER_S3_BUCKET,
            'Key' => 'tests/x/' . $destinationFile,
        ]);

        $metadata = $result->get('@metadata');

        $this->assertTrue(isset($metadata['statusCode']) && $metadata['statusCode'] == 200);
        $this->assertEquals('AES256', $result->get('ServerSideEncryption'));
    }
}
