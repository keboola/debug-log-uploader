# Debug Log Uploader

[![Build Status](https://travis-ci.org/keboola/debug-log-uploader.svg?branch=master)](https://travis-ci.org/keboola/debug-log-uploader)
[![Latest Stable Version](https://poser.pugx.org/keboola/debug-log-uploader/v/stable)](https://github.com/keboola/debug-log-uploader/releases)
[![Total Downloads](https://poser.pugx.org/keboola/debug-log-uploader/downloads)](https://packagist.org/packages/keboola/debug-log-uploader)
[![License](https://poser.pugx.org/keboola/debug-log-uploader/license)](https://github.com/keboola/debug-log-uploader/blob/master/LICENSE.md)

Simple package for uploading logs to S3 or local filesystem.

## Development

Create `.env` file with your environment variables:

```
UPLOADER_AWS_KEY=your_key
UPLOADER_AWS_SECRET=your_secret
UPLOADER_AWS_REGION=your_region
UPLOADER_S3_BUCKET=your_s3_bucket
```

Start container, install dependencies:

```console
docker-compose run --rm php56 sh -c 'composer install && bash'
```

### Tests

Execute `tests.sh` script which contains `phpunit` and related commands.

In running container:

```
./tests.sh
```

From outside:

```console
docker-compose run --rm php56 sh -c 'composer install && ./tests.sh'
```

## License

MIT. See license file.
