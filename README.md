# Debug Log Uploader

[![Build Status](https://travis-ci.com/keboola/debug-log-uploader.svg?branch=master)](https://travis-ci.com/keboola/debug-log-uploader)
[![Latest Stable Version](https://poser.pugx.org/keboola/debug-log-uploader/v/stable)](https://github.com/keboola/debug-log-uploader/releases)
[![Total Downloads](https://poser.pugx.org/keboola/debug-log-uploader/downloads)](https://packagist.org/packages/keboola/debug-log-uploader)
[![License](https://poser.pugx.org/keboola/debug-log-uploader/license)](https://github.com/keboola/debug-log-uploader/blob/master/LICENSE.md)

Simple package for uploading logs to AWS S3, Azure Blob Storage or local filesystem.

## Development

### Create Azure resources

Use the provided `azure-services.json` to create ARM stack:

```
export RESOURCE_GROUP=testing-debug-log-uploader
az group create --name $RESOURCE_GROUP --location "East US"
az deployment group create \
    --resource-group $RESOURCE_GROUP \
    --name debug-log-uploader \
    --template-file ./azure-services.json \
    --query "properties.outputs"

```
Go to the Azure Portal - Storage Account - Access Keys and copy connection string.

### Create .env file

Create `.env` file (from `.env.dist`) with your environment variables:

```
UPLOADER_AWS_KEY=your_key
UPLOADER_AWS_SECRET=your_secret
UPLOADER_AWS_REGION=your_region
UPLOADER_S3_BUCKET=your_s3_bucket
UPLOADER_ABS_CONNECTION_STRING=
UPLOADER_ABS_CONTAINER=
```

Start container, install dependencies:

```console
docker-compose run --rm php sh -c 'composer install && bash'
```

### Tests

Execute `tests.sh` script which contains `phpunit` and related commands.

In running container:

```
./tests.sh
```

From outside:

```console
docker-compose run --rm php sh -c 'composer install && ./tests.sh'
```

## License

MIT. See license file.
