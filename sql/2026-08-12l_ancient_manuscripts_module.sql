-- Modul pelestarian naskah kuno: metadata, digitalisasi, dan jejak akses.
CREATE TABLE IF NOT EXISTS `ancient_manuscripts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `inventory_number` VARCHAR(80) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `alternate_title` VARCHAR(255) DEFAULT NULL,
  `language` VARCHAR(120) DEFAULT NULL,
  `script` VARCHAR(120) DEFAULT NULL,
  `material` VARCHAR(120) DEFAULT NULL,
  `page_count` SMALLINT UNSIGNED DEFAULT NULL,
  `dimensions` VARCHAR(100) DEFAULT NULL,
  `condition_state` VARCHAR(80) DEFAULT NULL,
  `estimated_period` VARCHAR(160) DEFAULT NULL,
  `author_scribe` VARCHAR(180) DEFAULT NULL,
  `origin` VARCHAR(220) DEFAULT NULL,
  `current_location` VARCHAR(220) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `collection_history` TEXT DEFAULT NULL,
  `conservation_notes` TEXT DEFAULT NULL,
  `digitized_at` DATE DEFAULT NULL,
  `digitized_by` VARCHAR(180) DEFAULT NULL,
  `digitization_notes` TEXT DEFAULT NULL,
  `rights_note` TEXT DEFAULT NULL,
  `cover_path` VARCHAR(500) DEFAULT NULL,
  `preview_file_path` VARCHAR(500) DEFAULT NULL,
  `preview_original_name` VARCHAR(255) DEFAULT NULL,
  `preview_mime_type` VARCHAR(120) DEFAULT NULL,
  `access_level` ENUM('public','member','internal') NOT NULL DEFAULT 'public',
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ancient_manuscripts_inventory` (`inventory_number`),
  KEY `idx_ancient_manuscripts_public` (`status`, `access_level`, `is_featured`),
  KEY `idx_ancient_manuscripts_origin` (`origin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ancient_manuscript_access_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `manuscript_id` BIGINT UNSIGNED NOT NULL,
  `auth_user_id` BIGINT UNSIGNED DEFAULT NULL,
  `event_type` VARCHAR(40) NOT NULL DEFAULT 'preview',
  `ip_address` VARCHAR(80) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_manuscript_access_manuscript` (`manuscript_id`, `created_at`),
  KEY `idx_manuscript_access_user` (`auth_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`) VALUES
('manuscripts.index', 'manuscripts', 'Naskah Kuno', 'manuscripts', 'Pelestarian, metadata, dan digitalisasi naskah kuno.', 1)
ON DUPLICATE KEY UPDATE `module`=VALUES(`module`), `title`=VALUES(`title`), `route`=VALUES(`route`), `description`=VALUES(`description`), `is_active`=1;

INSERT INTO `auth_role_permission` (`role_id`,`page_id`,`can_view`,`can_create`,`can_edit`,`can_delete`,`can_export`,`can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r JOIN `sys_page` p ON p.`code`='manuscripts.index'
WHERE r.`code` IN ('SUPERADMIN','ADMIN')
ON DUPLICATE KEY UPDATE `can_view`=1,`can_create`=1,`can_edit`=1,`can_delete`=1,`can_export`=1,`can_approve`=1;

INSERT INTO `sys_menu` (`parent_id`,`page_id`,`menu_area`,`menu_key`,`title`,`icon`,`url`,`sort_order`,`is_visible`,`is_active`,`is_locked`)
SELECT NULL,p.`id`,'MAIN','manuscripts.index','Naskah Kuno','ti ti-feather','manuscripts',47,1,1,0
FROM `sys_page` p WHERE p.`code`='manuscripts.index'
ON DUPLICATE KEY UPDATE `page_id`=VALUES(`page_id`),`title`=VALUES(`title`),`icon`=VALUES(`icon`),`url`=VALUES(`url`),`sort_order`=VALUES(`sort_order`),`is_visible`=1,`is_active`=1;
