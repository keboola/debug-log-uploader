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

### Create GCP resources

Using terraform:

```
cd provisioning/gcp
terraform init
terraform apply
# set name_prefix (ci for github action)
# set UPLOADER_GCS_BUCKET env
# Go to the `https://console.cloud.google.com/iam-admin/serviceaccounts?project=gcp-dev-353411` (or your default project) and find your service account
(`<name_prefix>-debug-log-uploader@...`)
# In the table click on action and choose `Manage keys` (or click on service name and go to the detail and then choose `keys`)
# Click on `ADD KEY` => `Create new key` and select key type `JSON` then click `CREATE`
# convert key to string and save to `.env` file: `awk -v RS= '{$1=$1}1' <key_file>.json >> .env`
# set content on the last line of `.env` as variable `UPLOADER_GCS_BUCKET_KEY`
```

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

MIT licensed, see [LICENSE](./LICENSE) file.
