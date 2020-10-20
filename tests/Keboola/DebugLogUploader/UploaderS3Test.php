<?php

declare(strict_types=1);

namespace Keboola\DebugLogUploader;

use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class UploaderS3Test extends TestCase
{
    /** @var Filesystem */
    private $fs;

    /** @var UploaderS3 */
    private $uploader;

    /** @var string */
    private $sourcePath = '/tmp/uploader-s3-test-source';

    protected function setUp(): void
    {
        $this->fs = new Filesystem;
        $this->fs->remove($this->sourcePath);
        $this->fs->mkdir($this->sourcePath);

        $this->uploader = new UploaderS3(
            $this->getS3client(),
            '',
            getenv('S3_LOGS_BUCKET') . '/tests/x/'
        );

        parent::setUp();
    }

    private function getS3client(): S3Client
    {
        return new S3Client([
            'version' => '2006-03-01',
            'retries' => 20,
            'region' => getenv('AWS_DEFAULT_REGION'),
        ]);
    }

    public function testUploadFile(): void
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
            'Bucket' => getenv('S3_LOGS_BUCKET'),
            'Key' => 'tests/x/' . $destinationFile,
        ]);

        $metadata = $result->get('@metadata');

        $this->assertTrue(isset($metadata['statusCode']) && $metadata['statusCode'] == 200);
        $this->assertEquals('AES256', $result->get('ServerSideEncryption'));
    }

    public function testUploadString(): void
    {
        $fileContent = <<<TXT
S3 uploader upload string
TXT;
        $name = 's3-uploader-string.txt';

        $destinationFile = $this->uploader->uploadString($name, $fileContent);

        $client = $this->getS3client();

        $result = $client->getObject([
            'Bucket' => getenv('S3_LOGS_BUCKET'),
            'Key' => 'tests/x/' . $destinationFile,
        ]);

        $metadata = $result->get('@metadata');

        $this->assertTrue(isset($metadata['statusCode']) && $metadata['statusCode'] == 200);
        $this->assertEquals('AES256', $result->get('ServerSideEncryption'));
    }
}
