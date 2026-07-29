INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('regions.index', 'regions', 'Master Wilayah', 'regions', 'Kelola kecamatan dan Desa / Kelurahan Rembang.'),
('catalog.sync', 'catalog', 'Sinkronisasi Katalog', 'catalog/sync', 'Pantau dan jalankan sinkronisasi katalog dari INLISLite.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` IN ('regions.index', 'catalog.sync')
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 1,
  `can_export` = 1,
  `can_approve` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 0, 0, 0, 1, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'regions.index'
WHERE r.`code` = 'ADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 0,
  `can_edit` = 0,
  `can_delete` = 0,
  `can_export` = 1,
  `can_approve` = 0;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`) VALUES
(NULL, NULL, 'MAIN', 'master-data', 'Data Master', 'ti ti-database', NULL, 15, 1, 1, 1)
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, p.`id`, 'MAIN', 'master.regions', 'Master Wilayah', 'ti ti-map-pin-cog', 'regions', 16, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` p ON p.`code` = 'regions.index'
WHERE parent.`menu_key` = 'master-data'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

CREATE TABLE IF NOT EXISTS `books` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) DEFAULT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `statement_responsibility` varchar(255) DEFAULT NULL,
  `edition` varchar(120) DEFAULT NULL,
  `publish_place` varchar(160) DEFAULT NULL,
  `publisher` varchar(180) DEFAULT NULL,
  `publish_year` varchar(20) DEFAULT NULL,
  `isbn` varchar(80) DEFAULT NULL,
  `classification` varchar(80) DEFAULT NULL,
  `call_number` varchar(120) DEFAULT NULL,
  `language` varchar(60) DEFAULT NULL,
  `physical_description` varchar(255) DEFAULT NULL,
  `abstract` text DEFAULT NULL,
  `cover_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','hidden') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_books_source` (`source_system`, `source_id`),
  KEY `idx_books_title` (`title`),
  KEY `idx_books_isbn` (`isbn`),
  KEY `idx_books_classification` (`classification`),
  KEY `idx_books_status` (`status`),
  CONSTRAINT `fk_books_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_books_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_authors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) unsigned NOT NULL,
  `name` varchar(180) NOT NULL,
  `role` varchar(80) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 100,
  PRIMARY KEY (`id`),
  KEY `idx_book_authors_book` (`book_id`),
  KEY `idx_book_authors_name` (`name`),
  CONSTRAINT `fk_book_authors_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_subjects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) unsigned NOT NULL,
  `subject` varchar(180) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_book_subjects_book` (`book_id`),
  KEY `idx_book_subjects_subject` (`subject`),
  CONSTRAINT `fk_book_subjects_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) unsigned NOT NULL,
  `library_id` bigint(20) unsigned DEFAULT NULL,
  `source_system` varchar(50) DEFAULT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `item_code` varchar(120) DEFAULT NULL,
  `barcode` varchar(120) DEFAULT NULL,
  `call_number` varchar(120) DEFAULT NULL,
  `location_name` varchar(180) DEFAULT NULL,
  `collection_type` varchar(120) DEFAULT NULL,
  `inventory_number` varchar(120) DEFAULT NULL,
  `status` enum('available','loaned','missing','damaged','unknown') NOT NULL DEFAULT 'unknown',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_book_items_source` (`source_system`, `source_id`),
  KEY `idx_book_items_book` (`book_id`),
  KEY `idx_book_items_library` (`library_id`),
  KEY `idx_book_items_status` (`status`),
  CONSTRAINT `fk_book_items_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_items_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `digital_assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` bigint(20) unsigned NOT NULL,
  `file_original_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `access_policy` enum('online_only','download_allowed','location_only','member_only','internal') NOT NULL DEFAULT 'internal',
  `is_downloadable` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_digital_assets_book` (`book_id`),
  KEY `idx_digital_assets_policy` (`access_policy`),
  KEY `idx_digital_assets_status` (`status`),
  CONSTRAINT `fk_digital_assets_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_digital_assets_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `catalog_sync_runs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_database` varchar(80) NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(80) DEFAULT NULL,
  `sync_type` enum('manual','scheduled','dry_run') NOT NULL DEFAULT 'manual',
  `status` enum('queued','running','success','failed') NOT NULL DEFAULT 'queued',
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `total_source` int(10) unsigned NOT NULL DEFAULT 0,
  `total_inserted` int(10) unsigned NOT NULL DEFAULT 0,
  `total_updated` int(10) unsigned NOT NULL DEFAULT 0,
  `total_failed` int(10) unsigned NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_catalog_sync_runs_status` (`status`),
  KEY `idx_catalog_sync_runs_created_at` (`created_at`),
  CONSTRAINT `fk_catalog_sync_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `catalog_sync_maps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` enum('book','book_item','digital_asset','author','subject') NOT NULL,
  `source_system` varchar(50) NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `target_id` bigint(20) unsigned NOT NULL,
  `last_sync_run_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_catalog_sync_maps_source` (`entity_type`, `source_system`, `source_table`, `source_id`),
  KEY `idx_catalog_sync_maps_target` (`entity_type`, `target_id`),
  KEY `idx_catalog_sync_maps_run` (`last_sync_run_id`),
  CONSTRAINT `fk_catalog_sync_maps_run` FOREIGN KEY (`last_sync_run_id`) REFERENCES `catalog_sync_runs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
