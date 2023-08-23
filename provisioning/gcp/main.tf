terraform {
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "~> 4.0"
    }
  }
}

variable "name_prefix" {
  type = string
}

provider "google" {
  project = "gcp-dev-353411"
  region  = "europe-west1"
}

locals {
  gcsFileStorageLocation = "europe-west1"
  bucketName = "${var.name_prefix}-debug-log-uploader"
}

output "UPLOADER_GCS_BUCKET" {
  value = local.bucketName
}

output "SERVICE_ACCOUNT" {
  value = google_service_account.service_account.email
}
