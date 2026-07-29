CREATE TABLE IF NOT EXISTS `members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auth_user_id` bigint(20) unsigned DEFAULT NULL,
  `source_system` varchar(50) DEFAULT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `member_no` varchar(120) DEFAULT NULL,
  `full_name` varchar(180) NOT NULL,
  `identity_type` varchar(80) DEFAULT NULL,
  `identity_number` varchar(120) DEFAULT NULL,
  `gender` varchar(40) DEFAULT NULL,
  `birth_place` varchar(120) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `district` varchar(120) DEFAULT NULL,
  `village` varchar(120) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `member_type` varchar(120) DEFAULT NULL,
  `education` varchar(120) DEFAULT NULL,
  `occupation` varchar(120) DEFAULT NULL,
  `status` enum('active','inactive','blocked','expired','unknown') NOT NULL DEFAULT 'unknown',
  `registered_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_members_source` (`source_system`, `source_id`),
  UNIQUE KEY `uq_members_member_no` (`member_no`),
  KEY `idx_members_auth_user` (`auth_user_id`),
  KEY `idx_members_name` (`full_name`),
  KEY `idx_members_status` (`status`),
  CONSTRAINT `fk_members_auth_user` FOREIGN KEY (`auth_user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `member_sync_runs` (
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
  `total_users_created` int(10) unsigned NOT NULL DEFAULT 0,
  `total_failed` int(10) unsigned NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member_sync_runs_status` (`status`),
  KEY `idx_member_sync_runs_created_at` (`created_at`),
  CONSTRAINT `fk_member_sync_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `photo_path` varchar(255) DEFAULT NULL AFTER `email`;

ALTER TABLE `member_sync_runs`
  ADD COLUMN IF NOT EXISTS `source_table` varchar(80) DEFAULT NULL AFTER `source_database`;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('members.sync', 'members', 'Sinkronisasi Member', 'members/sync', 'Pantau dan jalankan sinkronisasi anggota dari INLISLite.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'members.sync'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_export` = 1,
  `can_approve` = 1;
