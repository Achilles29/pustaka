-- Antrean kontribusi/donasi digital berlisensi terbuka. Berkas tidak pernah
-- langsung masuk katalog atau folder publik sebelum ditinjau petugas.
CREATE TABLE IF NOT EXISTS `digital_donation_submissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `public_token` VARCHAR(64) NOT NULL,
  `submitted_by_auth_user_id` BIGINT UNSIGNED DEFAULT NULL,
  `donor_name` VARCHAR(180) NOT NULL,
  `donor_email` VARCHAR(180) DEFAULT NULL,
  `donor_phone` VARCHAR(80) DEFAULT NULL,
  `donor_organization` VARCHAR(180) DEFAULT NULL,
  `contribution_type` ENUM('book','manuscript','research','scientific_paper','thesis','dissertation','teaching_material','other') NOT NULL DEFAULT 'other',
  `title` VARCHAR(255) NOT NULL,
  `creator_names` VARCHAR(500) DEFAULT NULL,
  `publication_year` SMALLINT UNSIGNED DEFAULT NULL,
  `language` VARCHAR(120) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `license_code` ENUM('cc0','cc_by','cc_by_sa','public_domain') NOT NULL,
  `rights_statement` TEXT NOT NULL,
  `file_path` VARCHAR(500) DEFAULT NULL,
  `file_original_name` VARCHAR(255) DEFAULT NULL,
  `file_mime_type` VARCHAR(120) DEFAULT NULL,
  `file_size` BIGINT UNSIGNED DEFAULT NULL,
  `external_url` VARCHAR(1000) DEFAULT NULL,
  `status` ENUM('pending','reviewing','accepted','revision_requested','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` TEXT DEFAULT NULL,
  `reviewed_by` BIGINT UNSIGNED DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_digital_donation_public_token` (`public_token`),
  KEY `idx_digital_donations_status_created` (`status`,`created_at`),
  KEY `idx_digital_donations_type` (`contribution_type`),
  CONSTRAINT `fk_digital_donation_submitter` FOREIGN KEY (`submitted_by_auth_user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_digital_donation_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`,`module`,`title`,`route`,`description`,`is_active`) VALUES
('digital_donations.index','digital_donations','Donasi Digital','digital-donations','Verifikasi kontribusi digital berlisensi terbuka dari masyarakat.',1)
ON DUPLICATE KEY UPDATE `module`=VALUES(`module`),`title`=VALUES(`title`),`route`=VALUES(`route`),`description`=VALUES(`description`),`is_active`=1;

INSERT INTO `auth_role_permission` (`role_id`,`page_id`,`can_view`,`can_create`,`can_edit`,`can_delete`,`can_export`,`can_approve`)
SELECT r.`id`,p.`id`,1,1,1,1,1,1
FROM `auth_role` r JOIN `sys_page` p ON p.`code`='digital_donations.index'
WHERE r.`code` IN ('SUPERADMIN','ADMIN')
ON DUPLICATE KEY UPDATE `can_view`=1,`can_create`=1,`can_edit`=1,`can_delete`=1,`can_export`=1,`can_approve`=1;

INSERT INTO `sys_menu` (`parent_id`,`page_id`,`menu_area`,`menu_key`,`title`,`icon`,`url`,`sort_order`,`is_visible`,`is_active`,`is_locked`)
SELECT parent.`id`,p.`id`,'MAIN','digital_donations.index','Donasi Digital','ti ti-gift','digital-donations',45,1,1,0
FROM `sys_menu` parent JOIN `sys_page` p ON p.`code`='digital_donations.index'
WHERE parent.`menu_key`='collection_services'
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`),`page_id`=VALUES(`page_id`),`title`=VALUES(`title`),`icon`=VALUES(`icon`),`url`=VALUES(`url`),`sort_order`=VALUES(`sort_order`),`is_visible`=1,`is_active`=1;
