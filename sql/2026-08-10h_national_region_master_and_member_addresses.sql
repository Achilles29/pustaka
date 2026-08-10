-- Master wilayah nasional berdasarkan kode administrasi Kemendagri.
-- Dataset besar diimpor terpisah melalui tools/import_wilayah_kemendagri_2025.php.

CREATE TABLE IF NOT EXISTS `ref_provinces` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ref_provinces_code` (`code`),
  UNIQUE KEY `uq_ref_provinces_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ref_regencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `province_id` int(10) unsigned NOT NULL,
  `code` varchar(4) NOT NULL,
  `name` varchar(160) NOT NULL,
  `area_type` enum('kabupaten','kota','administratif') NOT NULL DEFAULT 'kabupaten',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ref_regencies_code` (`code`),
  KEY `idx_ref_regencies_province_name` (`province_id`, `name`),
  CONSTRAINT `fk_ref_regencies_province` FOREIGN KEY (`province_id`) REFERENCES `ref_provinces` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ref_provinces` (`code`, `name`, `is_active`) VALUES
('33', 'Jawa Tengah', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `is_active` = 1;

INSERT INTO `ref_regencies` (`province_id`, `code`, `name`, `area_type`, `is_active`)
SELECT p.`id`, '3317', 'Kabupaten Rembang', 'kabupaten', 1
FROM `ref_provinces` p WHERE p.`code` = '33'
ON DUPLICATE KEY UPDATE `province_id` = VALUES(`province_id`), `name` = VALUES(`name`), `area_type` = VALUES(`area_type`), `is_active` = 1;

ALTER TABLE `ref_districts`
  ADD COLUMN IF NOT EXISTS `province_id` int(10) unsigned DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `regency_id` int(10) unsigned DEFAULT NULL AFTER `province_id`,
  MODIFY COLUMN `province_code` varchar(2) NOT NULL DEFAULT '33',
  MODIFY COLUMN `regency_code` varchar(4) NOT NULL DEFAULT '3317',
  MODIFY COLUMN `code` varchar(10) NOT NULL;

DROP INDEX IF EXISTS `uq_ref_districts_name` ON `ref_districts`;
DROP INDEX IF EXISTS `uq_ref_districts_code` ON `ref_districts`;
CREATE UNIQUE INDEX IF NOT EXISTS `uq_ref_districts_code` ON `ref_districts` (`code`);
CREATE INDEX IF NOT EXISTS `idx_ref_districts_regency_name` ON `ref_districts` (`regency_id`, `name`);

UPDATE `ref_districts`
SET `regency_code` = CONCAT(`province_code`, LPAD(`regency_code`, 2, '0'))
WHERE CHAR_LENGTH(`regency_code`) = 2;

UPDATE `ref_districts`
SET `code` = REPLACE(`full_code`, '.', '')
WHERE `full_code` IS NOT NULL AND `full_code` <> '';

UPDATE `ref_districts` d
JOIN `ref_provinces` p ON p.`code` = d.`province_code`
JOIN `ref_regencies` r ON r.`code` = d.`regency_code`
SET d.`province_id` = p.`id`, d.`regency_id` = r.`id`;

ALTER TABLE `ref_villages`
  ADD COLUMN IF NOT EXISTS `province_id` int(10) unsigned DEFAULT NULL AFTER `district_id`,
  ADD COLUMN IF NOT EXISTS `regency_id` int(10) unsigned DEFAULT NULL AFTER `province_id`,
  MODIFY COLUMN `regency_code` varchar(4) NOT NULL DEFAULT '3317',
  MODIFY COLUMN `district_code` varchar(10) DEFAULT NULL;

CREATE INDEX IF NOT EXISTS `idx_ref_villages_regency_name` ON `ref_villages` (`regency_id`, `name`);

UPDATE `ref_villages` v
JOIN `ref_districts` d ON d.`id` = v.`district_id`
SET v.`province_id` = d.`province_id`,
    v.`regency_id` = d.`regency_id`,
    v.`province_code` = d.`province_code`,
    v.`regency_code` = d.`regency_code`,
    v.`district_code` = d.`code`;

ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `province_id` int(10) unsigned DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `regency_id` int(10) unsigned DEFAULT NULL AFTER `province_id`,
  ADD COLUMN IF NOT EXISTS `district_id` int(10) unsigned DEFAULT NULL AFTER `regency_id`,
  ADD COLUMN IF NOT EXISTS `village_id` int(10) unsigned DEFAULT NULL AFTER `district_id`,
  ADD COLUMN IF NOT EXISTS `province` varchar(120) DEFAULT NULL AFTER `village`,
  ADD COLUMN IF NOT EXISTS `regency` varchar(160) DEFAULT NULL AFTER `province`,
  ADD COLUMN IF NOT EXISTS `identity_address` text DEFAULT NULL AFTER `regency`,
  ADD COLUMN IF NOT EXISTS `identity_province_id` int(10) unsigned DEFAULT NULL AFTER `identity_address`,
  ADD COLUMN IF NOT EXISTS `identity_regency_id` int(10) unsigned DEFAULT NULL AFTER `identity_province_id`,
  ADD COLUMN IF NOT EXISTS `identity_district_id` int(10) unsigned DEFAULT NULL AFTER `identity_regency_id`,
  ADD COLUMN IF NOT EXISTS `identity_village_id` int(10) unsigned DEFAULT NULL AFTER `identity_district_id`,
  ADD COLUMN IF NOT EXISTS `identity_province` varchar(120) DEFAULT NULL AFTER `identity_village_id`,
  ADD COLUMN IF NOT EXISTS `identity_regency` varchar(160) DEFAULT NULL AFTER `identity_province`,
  ADD COLUMN IF NOT EXISTS `identity_district` varchar(120) DEFAULT NULL AFTER `identity_regency`,
  ADD COLUMN IF NOT EXISTS `identity_village` varchar(120) DEFAULT NULL AFTER `identity_district`;

ALTER TABLE `member_registration_requests`
  ADD COLUMN IF NOT EXISTS `province_id` int(10) unsigned DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `regency_id` int(10) unsigned DEFAULT NULL AFTER `province_id`,
  ADD COLUMN IF NOT EXISTS `district_id` int(10) unsigned DEFAULT NULL AFTER `regency_id`,
  ADD COLUMN IF NOT EXISTS `village_id` int(10) unsigned DEFAULT NULL AFTER `district_id`,
  ADD COLUMN IF NOT EXISTS `province` varchar(120) DEFAULT NULL AFTER `village`,
  ADD COLUMN IF NOT EXISTS `regency` varchar(160) DEFAULT NULL AFTER `province`,
  ADD COLUMN IF NOT EXISTS `identity_address` text DEFAULT NULL AFTER `regency`,
  ADD COLUMN IF NOT EXISTS `identity_province_id` int(10) unsigned DEFAULT NULL AFTER `identity_address`,
  ADD COLUMN IF NOT EXISTS `identity_regency_id` int(10) unsigned DEFAULT NULL AFTER `identity_province_id`,
  ADD COLUMN IF NOT EXISTS `identity_district_id` int(10) unsigned DEFAULT NULL AFTER `identity_regency_id`,
  ADD COLUMN IF NOT EXISTS `identity_village_id` int(10) unsigned DEFAULT NULL AFTER `identity_district_id`,
  ADD COLUMN IF NOT EXISTS `identity_province` varchar(120) DEFAULT NULL AFTER `identity_village_id`,
  ADD COLUMN IF NOT EXISTS `identity_regency` varchar(160) DEFAULT NULL AFTER `identity_province`,
  ADD COLUMN IF NOT EXISTS `identity_district` varchar(120) DEFAULT NULL AFTER `identity_regency`,
  ADD COLUMN IF NOT EXISTS `identity_village` varchar(120) DEFAULT NULL AFTER `identity_district`;

CREATE INDEX IF NOT EXISTS `idx_members_region` ON `members` (`province_id`, `regency_id`, `district_id`, `village_id`);
CREATE INDEX IF NOT EXISTS `idx_member_registration_region` ON `member_registration_requests` (`province_id`, `regency_id`, `district_id`, `village_id`);

-- Data lama Rembang dipertahankan sebagai alamat domisili dan, bila cocok,
-- dilengkapi referensi master tanpa mengubah teks alamat yang sudah tersimpan.
UPDATE `members` m
JOIN `ref_districts` d ON d.`regency_code` = '3317' AND LOWER(d.`name`) = LOWER(m.`district`)
LEFT JOIN `ref_villages` v ON v.`district_id` = d.`id` AND LOWER(v.`name`) = LOWER(m.`village`)
JOIN `ref_provinces` p ON p.`id` = d.`province_id`
JOIN `ref_regencies` r ON r.`id` = d.`regency_id`
SET m.`province_id` = d.`province_id`, m.`regency_id` = d.`regency_id`, m.`district_id` = d.`id`, m.`village_id` = v.`id`,
    m.`province` = p.`name`, m.`regency` = r.`name`;
