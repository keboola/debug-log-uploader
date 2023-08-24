resource "google_storage_bucket" "bucket" {
  name          = local.bucketName
  location      = local.gcsFileStorageLocation
  storage_class = "STANDARD"
  # public_access_prevention = "enforced" - not available yet
  versioning {
    enabled = false
  }
  uniform_bucket_level_access = true
}

resource "google_service_account" "service_account" {
  account_id   = "${var.name_prefix}-debug-log-uploader"
  display_name = "${var.name_prefix} File Storage Service Account"
}

resource "google_storage_bucket_iam_member" "member_bucket_admin" {
  bucket = google_storage_bucket.bucket.name
  role   = "roles/storage.objectAdmin"
  member = "serviceAccount:${google_service_account.service_account.email}"
}
