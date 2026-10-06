CREATE TABLE IF NOT EXISTS `database_backup_settings` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `frequency` ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
  `run_time` TIME NOT NULL DEFAULT '02:00:00',
  `day_of_week` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `day_of_month` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `retention_count` SMALLINT UNSIGNED NOT NULL DEFAULT 14,
  `compress_backup` TINYINT(1) NOT NULL DEFAULT 1,
  `last_scheduled_key` VARCHAR(20) DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_database_backup_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `database_backup_settings` (`id`) VALUES (1);

CREATE TABLE IF NOT EXISTS `database_backup_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `trigger_type` ENUM('scheduled','manual') NOT NULL,
  `status` ENUM('pending','running','success','failed') NOT NULL DEFAULT 'pending',
  `file_name` VARCHAR(255) DEFAULT NULL,
  `file_path` VARCHAR(500) DEFAULT NULL,
  `file_size` BIGINT UNSIGNED DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `requested_by` BIGINT UNSIGNED DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `finished_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_database_backup_runs_status` (`status`,`created_at`),
  CONSTRAINT `fk_database_backup_runs_user` FOREIGN KEY (`requested_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`,`module`,`title`,`route`,`description`,`is_active`) VALUES
('system.database_backups','system','Backup Database','system/database-backups','Pengaturan jadwal, retensi, dan riwayat backup database.',1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`route`=VALUES(`route`),`description`=VALUES(`description`),`is_active`=1;

INSERT INTO `auth_role_permission` (`role_id`,`page_id`,`can_view`,`can_create`,`can_edit`,`can_delete`,`can_export`,`can_approve`)
SELECT r.id,p.id,1,1,1,0,1,1 FROM auth_role r JOIN sys_page p ON p.code='system.database_backups'
WHERE r.code IN ('SUPERADMIN','ADMIN')
ON DUPLICATE KEY UPDATE can_view=1,can_create=1,can_edit=1,can_export=1,can_approve=1;

INSERT INTO `sys_menu` (`parent_id`,`page_id`,`menu_area`,`menu_key`,`title`,`icon`,`url`,`sort_order`,`is_visible`,`is_active`,`is_locked`)
SELECT parent.id,p.id,'MAIN','system.database_backups','Backup Database','ti ti-database-export','system/database-backups',50,1,1,0
FROM sys_menu parent JOIN sys_page p ON p.code='system.database_backups' WHERE parent.menu_key='system'
ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),page_id=VALUES(page_id),title=VALUES(title),icon=VALUES(icon),url=VALUES(url),sort_order=VALUES(sort_order),is_visible=1,is_active=1;
