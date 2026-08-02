ALTER TABLE `books`
  ADD COLUMN IF NOT EXISTS `cover_source_path` varchar(500) DEFAULT NULL AFTER `cover_path`,
  ADD COLUMN IF NOT EXISTS `cover_local_path` varchar(500) DEFAULT NULL AFTER `cover_source_path`,
  ADD COLUMN IF NOT EXISTS `cover_migration_status` enum('pending','copied','missing','failed','skipped') NOT NULL DEFAULT 'pending' AFTER `cover_local_path`,
  ADD COLUMN IF NOT EXISTS `cover_migrated_at` datetime DEFAULT NULL AFTER `cover_migration_status`;

ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `photo_source_path` varchar(500) DEFAULT NULL AFTER `photo_path`,
  ADD COLUMN IF NOT EXISTS `photo_local_path` varchar(500) DEFAULT NULL AFTER `photo_source_path`,
  ADD COLUMN IF NOT EXISTS `photo_migration_status` enum('pending','copied','missing','failed','skipped') NOT NULL DEFAULT 'pending' AFTER `photo_local_path`,
  ADD COLUMN IF NOT EXISTS `photo_migrated_at` datetime DEFAULT NULL AFTER `photo_migration_status`;

ALTER TABLE `digital_assets`
  ADD COLUMN IF NOT EXISTS `source_system` varchar(50) DEFAULT NULL AFTER `book_id`,
  ADD COLUMN IF NOT EXISTS `source_id` varchar(80) DEFAULT NULL AFTER `source_system`,
  ADD COLUMN IF NOT EXISTS `source_path` varchar(500) DEFAULT NULL AFTER `source_id`,
  ADD COLUMN IF NOT EXISTS `migration_status` enum('pending','copied','missing','failed','skipped') NOT NULL DEFAULT 'pending' AFTER `source_path`,
  ADD COLUMN IF NOT EXISTS `migrated_at` datetime DEFAULT NULL AFTER `migration_status`;

CREATE TABLE IF NOT EXISTS `asset_migration_runs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) NOT NULL DEFAULT 'inlislite_v3',
  `asset_type` enum('all','cover','member_photo','digital_file') NOT NULL DEFAULT 'all',
  `mode` enum('copy_missing','refresh_existing','dry_run') NOT NULL DEFAULT 'copy_missing',
  `status` enum('queued','running','success','failed') NOT NULL DEFAULT 'queued',
  `source_root` varchar(500) DEFAULT NULL,
  `target_root` varchar(500) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `total_source` int(10) unsigned NOT NULL DEFAULT 0,
  `total_copied` int(10) unsigned NOT NULL DEFAULT 0,
  `total_skipped` int(10) unsigned NOT NULL DEFAULT 0,
  `total_missing` int(10) unsigned NOT NULL DEFAULT 0,
  `total_failed` int(10) unsigned NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_asset_migration_runs_type` (`asset_type`),
  KEY `idx_asset_migration_runs_status` (`status`),
  KEY `idx_asset_migration_runs_created_at` (`created_at`),
  CONSTRAINT `fk_asset_migration_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_migration_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `run_id` bigint(20) unsigned DEFAULT NULL,
  `asset_type` enum('cover','member_photo','digital_file') NOT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `source_system` varchar(50) NOT NULL DEFAULT 'inlislite_v3',
  `source_id` varchar(80) DEFAULT NULL,
  `source_path` varchar(500) DEFAULT NULL,
  `local_path` varchar(500) DEFAULT NULL,
  `status` enum('copied','skipped','missing','failed') NOT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_asset_migration_items_run` (`run_id`),
  KEY `idx_asset_migration_items_asset` (`asset_type`, `status`),
  KEY `idx_asset_migration_items_entity` (`entity_type`, `entity_id`),
  KEY `idx_asset_migration_items_source` (`source_system`, `source_id`),
  CONSTRAINT `fk_asset_migration_items_run` FOREIGN KEY (`run_id`) REFERENCES `asset_migration_runs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE `books`
SET `cover_source_path` = `cover_path`,
    `cover_migration_status` = IF(`cover_path` IS NULL OR `cover_path` = '', 'skipped', `cover_migration_status`)
WHERE `cover_source_path` IS NULL;

UPDATE `members`
SET `photo_source_path` = `photo_path`,
    `photo_migration_status` = IF(`photo_path` IS NULL OR `photo_path` = '', 'skipped', `photo_migration_status`)
WHERE `photo_source_path` IS NULL;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('assets.migration', 'migration', 'Migrasi Aset INLISLite', 'assets-migration', 'Salin cover, foto member, dan file digital INLISLite ke storage aplikasi Pustaka.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'assets.migration'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_export` = 1,
  `can_approve` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'assets.migration', 'Migrasi Aset', 'ti ti-file-import', 'assets-migration', 35, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'assets.migration'
ON DUPLICATE KEY UPDATE
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;
