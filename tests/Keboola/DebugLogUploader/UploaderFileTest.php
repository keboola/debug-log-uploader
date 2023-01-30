<?php

namespace Keboola\DebugLogUploader;

use Symfony\Component\Filesystem\Filesystem;

class UploaderFileTest extends \PHPUnit\Framework\TestCase
{
    private ?\Symfony\Component\Filesystem\Filesystem $fs = null;

    private ?\Keboola\DebugLogUploader\UploaderFile $uploader = null;

    private string $sourcePath = '/tmp/uploader-file-test-source';

    private string $destinationPath = '/tmp/uploader-file-test-dest';

    protected function setUp(): void
    {
        $this->fs = new Filesystem;
        $this->fs->remove([$this->sourcePath, $this->destinationPath]);
        $this->fs->mkdir([$this->sourcePath, $this->destinationPath]);

        $this->uploader = new UploaderFile($this->destinationPath);

        parent::setUp();
    }

    public function testUploadFile()
    {
        $fileContent = <<<TXT
File uploader upload file
TXT;
        $fileName = 'file-uploader-file.txt';
        $sourceFile = $this->sourcePath . '/' . $fileName;
        $this->fs->dumpFile($sourceFile, $fileContent);

        $destinationFile = $this->uploader->upload($sourceFile);

        $this->assertEquals($fileContent, file_get_contents($destinationFile));
    }

    public function testUploadStringPlain()
    {
        $contentTxt = <<<TXT
File uploader upload string - plain
TXT;
        $name = 'file-uploader-string-plain.txt';

        $destinationFile = $this->uploader->uploadString($name, $contentTxt);

        $this->assertEquals($contentTxt, file_get_contents($destinationFile));
    }

    public function testUploadStringJson()
    {
        $contentJsonOriginal = <<<JSON
\n{"require":{"php":"~5.6","aws/aws-sdk-php":"~3.18",\n"symfony/filesystem":"~3.0"
},"autoload":{"psr-4":{"Keboola\\\\":"src/Keboola/"}},"require-dev":{
\n"symfony/process":"~3.0","phpunit/phpunit":"~5.3","squizlabs/php_codesniffer":"~2.6",
"codeclimate/php-test-reporter":"~0.3"}\n\n}\n\n
JSON;
        $contentJsonFormatted = <<<JSON
{
    "require": {
        "php": "~5.6",
        "aws/aws-sdk-php": "~3.18",
        "symfony/filesystem": "~3.0"
    },
    "autoload": {
        "psr-4": {
            "Keboola\\\\": "src/Keboola/"
        }
    },
    "require-dev": {
        "symfony/process": "~3.0",
        "phpunit/phpunit": "~5.3",
        "squizlabs/php_codesniffer": "~2.6",
        "codeclimate/php-test-reporter": "~0.3"
    }
}
JSON;

        $name = 'file-uploader-string-json.txt';

        $destinationFile = $this->uploader->uploadString($name, $contentJsonOriginal, 'application/json');

        $this->assertEquals($contentJsonFormatted, file_get_contents($destinationFile));
    }
}
