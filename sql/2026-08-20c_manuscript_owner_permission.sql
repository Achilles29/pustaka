ALTER TABLE `ancient_manuscripts`
  ADD COLUMN IF NOT EXISTS `owner_name` varchar(220) DEFAULT NULL AFTER `rights_note`,
  ADD COLUMN IF NOT EXISTS `permission_reference` varchar(160) DEFAULT NULL AFTER `owner_name`,
  ADD COLUMN IF NOT EXISTS `permission_date` date DEFAULT NULL AFTER `permission_reference`,
  ADD COLUMN IF NOT EXISTS `permission_notes` text DEFAULT NULL AFTER `permission_date`,
  ADD COLUMN IF NOT EXISTS `permission_file_path` varchar(500) DEFAULT NULL AFTER `permission_notes`,
  ADD COLUMN IF NOT EXISTS `permission_original_name` varchar(255) DEFAULT NULL AFTER `permission_file_path`,
  ADD COLUMN IF NOT EXISTS `permission_mime_type` varchar(120) DEFAULT NULL AFTER `permission_original_name`,
  ADD COLUMN IF NOT EXISTS `permission_file_size` bigint unsigned DEFAULT NULL AFTER `permission_mime_type`,
  ADD COLUMN IF NOT EXISTS `permission_status` enum('missing','verified','revoked') NOT NULL DEFAULT 'missing' AFTER `permission_file_size`,
  ADD COLUMN IF NOT EXISTS `permission_verified_by` bigint unsigned DEFAULT NULL AFTER `permission_status`,
  ADD COLUMN IF NOT EXISTS `permission_verified_at` datetime DEFAULT NULL AFTER `permission_verified_by`,
  ADD INDEX IF NOT EXISTS `idx_manuscript_permission` (`permission_status`);
