# Debug Log Uploader

[![Build Status](https://travis-ci.org/keboola/debug-log-uploader.svg?branch=master)](https://travis-ci.org/keboola/debug-log-uploader)
[![Latest Stable Version](https://poser.pugx.org/keboola/debug-log-uploader/v/stable)](https://github.com/keboola/debug-log-uploader/releases)
[![Total Downloads](https://poser.pugx.org/keboola/debug-log-uploader/downloads)](https://packagist.org/packages/keboola/debug-log-uploader)
[![License](https://poser.pugx.org/keboola/debug-log-uploader/license)](https://github.com/keboola/debug-log-uploader/blob/master/LICENSE.md)

Simple package for uploading logs to S3 or local filesystem.

## Development

Create `.env` file with your environment variables:

```
cp .env.dist .env
```

Start container, install dependencies:

```console
dc run --rm dev composer install
```

### Tests

```console
dc run --rm dev composer ci
```

## License

MIT. See license file.
