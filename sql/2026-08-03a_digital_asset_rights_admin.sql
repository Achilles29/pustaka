-- Digital asset publication rights and admin controls.

ALTER TABLE `digital_assets`
	ADD COLUMN IF NOT EXISTS `rights_basis` enum('public_domain','licensed','owned','permission_letter','internal_use','unknown') NOT NULL DEFAULT 'unknown' AFTER `is_downloadable`,
	ADD COLUMN IF NOT EXISTS `rights_holder` varchar(180) DEFAULT NULL AFTER `rights_basis`,
	ADD COLUMN IF NOT EXISTS `license_url` varchar(500) DEFAULT NULL AFTER `rights_holder`,
	ADD COLUMN IF NOT EXISTS `permission_reference` varchar(180) DEFAULT NULL AFTER `license_url`,
	ADD COLUMN IF NOT EXISTS `permission_starts_at` date DEFAULT NULL AFTER `permission_reference`,
	ADD COLUMN IF NOT EXISTS `permission_ends_at` date DEFAULT NULL AFTER `permission_starts_at`,
	ADD COLUMN IF NOT EXISTS `access_notes` text DEFAULT NULL AFTER `permission_ends_at`;

ALTER TABLE `digital_assets`
	ADD INDEX IF NOT EXISTS `idx_digital_assets_rights_basis` (`rights_basis`),
	ADD INDEX IF NOT EXISTS `idx_digital_assets_permission_ends` (`permission_ends_at`);
