<?php

require __DIR__ . '/../vendor/autoload.php';

$requiredEnvVars = [
    'UPLOADER_AWS_KEY',
    'UPLOADER_AWS_SECRET',
    'UPLOADER_AWS_REGION',
    'UPLOADER_S3_BUCKET'
];

foreach ($requiredEnvVars as $var) {
    if (getenv($var) === false) {
        trigger_error('Set all required environment variables', E_USER_ERROR);
    }
}

define('UPLOADER_AWS_KEY', getenv('UPLOADER_AWS_KEY'));
define('UPLOADER_AWS_SECRET', getenv('UPLOADER_AWS_SECRET'));
define('UPLOADER_AWS_REGION', getenv('UPLOADER_AWS_REGION'));
define('UPLOADER_S3_BUCKET', getenv('UPLOADER_S3_BUCKET'));
